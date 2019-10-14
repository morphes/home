<?php

class MediaEventController extends AdminController
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='webroot.themes.myhome.views.layouts.backend';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('index', 'create', 'delete', 'update', 'upload', 'getnumberofgallery', 'saveimagedescription',
					'getallgalleryimages', 'uploadimagegallery', 'deleteimagegallery', 'getlistgallery',
					'getplace', 'removeplace',
				),
				'roles'=>array(
					User::ROLE_ADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_JOURNALIST
				),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model = new MediaEvent();
		$model->status = MediaEvent::STATUS_IN_PROGRESS;
		$model->author_id = Yii::app()->getUser()->getId();
		$model->save(false);
		$this->redirect(array('update', 'id' => $model->id));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);

		if(isset($_POST['MediaEvent']))
		{
			$model->attributes = $_POST['MediaEvent'];
			if ( isset($_POST['MediaEventPlace']) ) {
				$places = $_POST['MediaEventPlace'];
				$errors = false;

				$cnt=0;
				foreach ($places as $key=>$data) {
					/** @var $placeObj MediaEventPlace */
					$placeObj = MediaEventPlace::model()->findByPk( intval($key) );
					if (is_null($placeObj))
						continue;
					$placeObj->attributes = $data;

					if ( $placeObj->validate() ) {
						if ($cnt == 0) {
							$model->city_id = $placeObj->city_id;
							$cnt++;
						}
						$placeObj->geocode = CHtml::encode(YandexMap::getGeocode('г.'.$placeObj->getCityName().', '.$placeObj->address));
						$placeObj->save(false);
					} else {
						$errors = true;
					}
				}

				if ($errors)
					$model->addError('places', 'Возникли ошибки при сохранении места проведения');
			} else {
				$model->addError('places', 'Не указано место проведения');
			}

			if ($model->validate(null, false)) {
				$model->save(false);
				$this->redirect(array('index'));
			}
		}

		$eventTypes = MediaEventType::model()->findAllByAttributes(array('status'=>MediaEventType::STATUS_ACTIVE));
		$themes = MediaTheme::model()->findAllByAttributes(array('status'=>MediaTheme::STATUS_ACTIVE));
		$places = MediaEventPlace::model()->findAllByAttributes( array('event_id'=>$model->id) );

		$this->render('update',array(
			'model'=>$model,
			'eventTypes'=>$eventTypes,
			'themes'=>$themes,
			'places'=>$places,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(404);

		$id = intval($id);
		$model = $this->loadModel($id);
		$model->status = MediaEvent::STATUS_DELETED;
		$model->save(false);

		die ( json_encode(array('success'=>true)) );
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model = new MediaEvent('search');
		$model->unsetAttributes(); // clear any default values
		if (isset($_GET['MediaEvent']))
			$model->attributes = $_GET['MediaEvent'];

		$themes = MediaTheme::model()->findAllByAttributes(array('status'=>MediaTheme::STATUS_ACTIVE));
		$eventTypes = MediaEventType::model()->findAllByAttributes(array('status'=>MediaEventType::STATUS_ACTIVE));

		$this->render('index', array(
			'model' => $model,
			'themes' => $themes,
			'eventTypes' => $eventTypes,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 * @return MediaEvent
	 */
	public function loadModel($id)
	{
		$model=MediaEvent::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}


	/**
	 * Загрузка изображений на fileApi
	 * @param eid - MediaEvent id
	 */
	public function actionUpload($eid = null)
	{
		/** @var $event MediaEvent */
		$event = MediaEvent::model()->findByPk(intval($eid));
		if (is_null($event))
			die( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$event->setImageType('main');

		$image = UploadedFile::loadImage($event, 'file', '', true);
		if ($image) {
			$event->image_id = $image->id;
			$event->save(false);
			$html = $this->renderPartial('_imageItem', array('file'=>$image), true);
			die( json_encode( array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK ) );
		} else {
			die( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );
		}
	}

	/** GALLERY */

	/**
	 * Получает номер галереи. Нужен для вызова из javascript кода в редакторе
	 */
	public function actionGetNumberOfGallery()
	{
		$modelId = intval( Yii::app()->getRequest()->getParam('modelId') );

		$sql = "SELECT MAX(NUM) FROM media_gallery WHERE model = 'MediaEvent' and model_id = '".intval($modelId)."'";
		$maxNum = Yii::app()->getDb()->createCommand($sql)->queryScalar();

		if (!$maxNum)
			$nextNum = 1;
		else
			$nextNum = $maxNum+1;

		die(json_encode(array(
			'num' => $nextNum
		)));
	}

	/**
	 * Сохраняет описания для фотографий карниток фотогалереи
	 */
	public function actionSaveImageDescription()
	{
		foreach($_POST['short_description'] as $id=>$desc) {
			UploadedFile::model()->updateByPk((int)$id,array('desc' => CHtml::encode($desc)));
		}

		foreach($_POST['detail_description'] as $id=>$desc) {
			$model = MediaGallery::model()->findByAttributes(array('image_id' => (int)$id));
			$model->description = CHtml::encode($desc);
			$model->save(false);
		}

		die('success');
	}

	/**
	 * Получает и возвращает html для всех фоток загруженных в галерею к Знанию.
	 * Берет только те фотографии, который хранятся под указанным номером $numGallery
	 */
	public function actionGetAllGalleryImages()
	{
		$modelId = intval( Yii::app()->getRequest()->getParam('modelId') );
		$numGallery = intval( Yii::app()->getRequest()->getParam('num') );

		$event = MediaEvent::model()->findByPk($modelId);
		if ($event) {
			$models = MediaGallery::model()->findAllByAttributes(array('model' => 'MediaEvent', 'model_id' => $modelId, 'num' => $numGallery));

			$html = '';
			foreach($models as $model) {
				$image = UploadedFile::model()->findByPk($model->image_id);
				$html .= $this->renderPartial('_imageGallery', array(
					'model' => $event,
					'image' => $image,
					'detail_description' => $model->description
				));
			}

			die($html);
		} else {
			throw new CHttpException(404, 'Неверный ID записи');
		}

	}

	/**
	 * Cохраняет фотографии во встроенную фотогалерею Знания
	 */
	public function actionUploadImageGallery($id)
	{
		$model = MediaEvent::model()->findByPk((int)$id);
		$numGallery = (int)$_POST['numGallery'];

		if ($model) {
			$gallery = new MediaGallery();
			$gallery->author_id = Yii::app()->user->id;
			$gallery->model = get_class($model);
			$gallery->model_id = $model->id;
			$gallery->num = $numGallery;
			$gallery->save(false);

			$gallery->setImageType('photo');
			$image = UploadedFile::loadImage($gallery, 'upload', '', true);

			if ($image) {
				$gallery->image_id = $image->id;
				$gallery->save(false);

				$html = $this->renderPartial('_imageGallery', array('model' => $model, 'image' => $image, 'detail_description' => ''), true);
				die($html);
			} else {
				$html = $this->renderPartial('_imageGalleryError', array('model' => $model), true);
				die($html);
			}
		} else {
			die('<div>Неверная модель</div>');
		}
	}

	/**
	 * Удалает фотографию из встроенной фотогалереи Знания
	 */
	public function actionDeleteImageGallery()
	{
		$modelId = (int)$_POST['modelId'];
		$imageId = (int)$_POST['imageId'];
		$model = MediaGallery::model()->findByAttributes(array('model_id' => $modelId, 'image_id' => $imageId));
		if ($model) {
			$model->delete();
			UploadedFile::model()->deleteByPk($imageId);

			die('success');
		}

		die('error');
	}

	public function actionGetListGallery()
	{
		$modelId =(int)$_POST['modelId'];

		$event = MediaEvent::model()->findByPk($modelId);
		if ($event) {
			$sql = "SELECT image_id, num FROM media_gallery WHERE model = 'MediaEvent' and model_id = '{$modelId}' ORDER BY num, id";
			$photos = Yii::app()->getDb()->createCommand($sql)->queryAll();
			$html = $this->renderPartial('_imageListGallery', array(
				'photos' => $photos
			), true);

			die($html);
		} else {
			throw new CHttpException(404, 'Неверный ID записи');
		}
	}

	/** END GALLERY */

	/**
	 * Get autocomplete for coauthor without checkAccess
	 * @return JSON
	 * @author Alexey Shvedov
	 */
	public function actionGetplace()
	{
		if ( !Yii::app()->getRequest()->getIsAjaxRequest() )
			throw new CHttpException(404);

		$eventId = Yii::app()->request->getParam('eventId');
		$event = MediaEvent::model()->findByPk($eventId);
		if (is_null($event))
			throw new CHttpException(404);

		$this->layout = false;

		$result = $this->renderPartial('_placeItem', array(
			'place' => MediaEventPlace::createRow($event->id),
		), true);

		echo CJSON::encode(array('success' => true, 'data' => $result));
		return;
	}

	/**
	 * Remove coauthor from Interior without checkAccess
	 * @return JSON
	 * @author Alexey Shvedov
	 */
	public function actionRemoveplace()
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(404);

		$placeId = Yii::app()->request->getParam('placeId');
		$rows = MediaEventPlace::model()->deleteByPk($placeId);
		die ( json_encode(array('success' => (bool) $rows), JSON_NUMERIC_CHECK) );
	}

}
