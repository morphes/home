<?php

class MediaNewController extends AdminController
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
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('index','delete','uploadimage','uploadPreview', 'deletePreview', 'UploadImageGallery', 'DeleteImageGallery', 'SaveImageDescription', 'GetAllGalleryImages', 'GetNumberOfGallery', 'GetListGallery', 'UploadAuthorPhoto'),
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
		$model = new MediaNew();

		$model->status = MediaNew::STATUS_HIDE;
		$model->author_id = Yii::app()->user->id;
		$model->public_time = time();

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

		$request = Yii::app()->getRequest();

		$sCategory = $request->getParam('MediaNewCategory', array());


		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['MediaNew']))
		{

			$arrayCat = array();

			foreach ($sCategory as $key => $sC) {

				if ($sC == 'on') {
					$arrayCat[$key] = 1;
				}
			}

			if ($arrayCat) {
				$json = json_encode($arrayCat);
			} else {
				$json = '';
			}

			//Если Категории получены
			//То записываем в базу
			if (isset($json)) {
				$model->selected_category = $json;
			}

			$model->attributes = $_POST['MediaNew'];
			$model->public_time = !empty($_POST['MediaNew']['public_time'])
				? strtotime(@$_POST['MediaNew']['public_time'])
				: time();

			if ($model->image_id && ($desc = Yii::app()->getRequest()->getParam('preview_description')))
				UploadedFile::model()->updateByPk($model->image_id, array('desc' => $desc));


			if ($model->save())
				$this->redirect(array('index'));
		}

		if(empty($model->model_first))
		{
			$model->model_first = MediaNew::ARTICLE_MODEL_NEW;
		}

		if(empty($model->model_second))
		{
			$model->model_second = MediaNew::ARTICLE_MODEL_NEW;
		}

		if(empty($model->model_third))
		{
			$model->model_third = MediaNew::ARTICLE_MODEL_NEW;
		}

		$model->public_time = date('d.m.Y H:i', $model->public_time);

		$authorPhoto = UploadedFile::model()->findByPk($model->author_image_id);

		$jsonDecodeCat = array();

		if($model->selected_category)
		{
			$jsonDecodeCat = json_decode($model->selected_category, true);

		}


		$this->render('update',array(
			'model'=>$model,
			'authorPhoto' => $authorPhoto,
			'sCategory' => $jsonDecodeCat,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model = new MediaNew('search');
		$model->unsetAttributes(); // clear any default values
		if (isset($_GET['MediaNew']))
			$model->attributes = $_GET['MediaNew'];

		$this->render('index', array(
			'model'=> $model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=MediaNew::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='media-new-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	/**
	 * Метод для загрузки одиночных фотографий в редакторе.
	 */
	public function actionUploadimage()
	{

		$fname = time() . $_FILES["upload"]['name'];

		$image = Yii::app()->image->load($_FILES["upload"]["tmp_name"]);
		$image->quality(85)->resize('700', '700', Image::WIDTH)->save(MediaNew::UPLOAD_IMAGE_DIR . $fname);

		$url = $this->createAbsoluteUrl('/' . MediaNew::UPLOAD_IMAGE_DIR . $fname);
		echo $url;
		die();
	}

	/**
	 * Метод который занимается сохранением фотографии для превью Знания
	 */
	public function actionUploadPreview($id)
	{

		$layout = false;

		$model = MediaNew::model()->findByPk((int)$id);
		if ($model) {
			$model->setImageType('preview');
			$image = UploadedFile::loadImage($model, 'upload', '', true);
			if ($image) {
				$model->image_id = $image->id;
				$model->save(false);

				$html = $this->renderPartial('_imagePreview', array('model' => $model, 'image' => $image), true);

				die($html);
			} else {
				die($this->renderPartial('_imagePreviewError', array('model' => $model)));
			}
		} else {
			throw new CHttpException(404);
		}
	}

	/**
	 * Удаляет превьюшку для Знания
	 */
	public function actionDeletePreview()
	{
		$model = MediaNew::model()->findByPk((int)Yii::app()->getRequest()->getParam('modelId'));
		$image = UploadedFile::model()->findByPk((int)Yii::app()->getRequest()->getParam('imageId'));

		if ($model && $image && $model->image_id == $image->id) {
			$image->delete();

			$model->image_id = 0;
			$model->save(false);

			die('success');
		}

		die('error');
	}

	/**
	 * Cохраняет фотографии во встроенную фотогалерею Знания
	 */
	public function actionUploadImageGallery($id)
	{
		$layout = false;

		$model = MediaNew::model()->findByPk((int)$id);
		$numGallery = (int)$_POST['numGallery'];

		if ($model) {
			$gallery = new MediaGallery();
			$gallery->author_id = Yii::app()->user->id;
			$gallery->model = get_class($model);
			$gallery->model_id = $model->id;
			$gallery->num = $numGallery;
			$gallery->save('false');

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
		$modelId = (int)$_POST['modelId'];
		$numGallery = (int)$_POST['num'];

		$knowledge = MediaNew::model()->findByPk($modelId);
		if ($knowledge) {
			$models = MediaGallery::model()->findAllByAttributes(array('model' => 'MediaNew', 'model_id' => $modelId, 'num' => $numGallery));

			$html = '';
			foreach($models as $model) {
				$image = UploadedFile::model()->findByPk($model->image_id);
				$html .= $this->renderPartial('_imageGallery', array(
					'model' => $knowledge,
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
	 * Получает номер галереи. Нужен для вызова из javascript кода в редакторе
	 */
	public function actionGetNumberOfGallery()
	{
		$modelId = (int)$_POST['modelId'];

		$sql = "SELECT MAX(NUM) FROM media_gallery WHERE model = 'MediaNew' and model_id = '".intval($modelId)."'";
		$maxNum = Yii::app()->getDb()->createCommand($sql)->queryScalar();

		if (!$maxNum)
			$nextNum = 1;
		else
			$nextNum = $maxNum+1;

		die(json_encode(array(
			'num' => $nextNum
		)));
	}

	public function actionGetListGallery()
	{
		$modelId =(int)$_POST['modelId'];

		$knowledge = MediaNew::model()->findByPk($modelId);
		if ($knowledge) {
			$sql = "SELECT image_id, num FROM media_gallery WHERE model = 'MediaNew' and model_id = '{$modelId}' ORDER BY num, id";
			$photos = Yii::app()->getDb()->createCommand($sql)->queryAll();
			$html = $this->renderPartial('_imageListGallery', array(
				'photos' => $photos
			), true);

			die($html);
		} else {
			throw new CHttpException(404, 'Неверный ID записи');
		}
	}

	/**
	 * Метод который занимается сохранением фотографии для превью Знания
	 */
	public function actionUploadAuthorPhoto($id)
	{
		$layout = false;


		/** @var $model MediaNew */
		$model = MediaNew::model()->findByPk((int)$id);
		if ($model) {
			$model->setImageType('authorPhoto');
			$image = UploadedFile::loadImage($model, 'file', '', true);
			if ($image) {
				$model->author_image_id = $image->id;
				$model->save(false);

				$html = $this->renderPartial('_imageAuthorPhoto', array('model' => $model, 'image' => $image), true);

				die(json_encode(array(
					'html' => $html
				)));
			} else {
				die(json_encode(array(
					'html' => $this->renderPartial('_imagePreviewError', array('model' => $model))
				)));
			}
		} else {
			throw new CHttpException(404);
		}
	}
}
