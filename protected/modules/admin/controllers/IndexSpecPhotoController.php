<?php

class IndexSpecPhotoController extends AdminController
{

	public $layout = 'webroot.themes.myhome.views.layouts.backend';


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
			array(
				'allow',
				'actions' => array(
					'index', 'view', 'create',
					'update', 'upload', 'delete',
					'AjaxGetPhoto', 'AjaxStatusList',
					'AjaxStatusUpdate'
				),
				'roles'   => array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_JOURNALIST,
				),
			),
			array(
				'deny', // deny all users
				'users' => array('*'),
			),
		);
	}


	/**
	 * Displays a particular model.
	 *
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view', array(
			'model' => $this->loadModel($id),
		));
	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model = new IndexSpecPhoto;
		$imgData = array();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		$model->status = IndexSpecPhoto::STATUS_INACTIVE;

		if (isset($_POST['IndexSpecPhoto'])) {
			$model->attributes = $_POST['IndexSpecPhoto'];

			$imgData = Yii::app()->request->getParam('img', array());
			if ($model->validate() && $file = $this->cropImage($imgData, $model)) {
				$model->image_id = $file->id;
				$model->save(false);

				$this->redirect(array('index'));
			}
		}

		$this->render('create', array(
			'model' => $model,
			'imgData' => $imgData,
		));
	}


	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);
		$imgData = array();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['IndexSpecPhoto'])) {
			$model->attributes = $_POST['IndexSpecPhoto'];

			$imgData = Yii::app()->request->getParam('img', array());
			if ( ! $imgData['photo'] && $model->save()) {

				$this->redirect(array('index'));

			} elseif($model->validate() && $file = $this->cropImage($imgData, $model)) {

				$model->image_id = $file->id;
				$model->save(false);

				$this->redirect(array('index'));
			}
		}

		$this->render('update', array(
			'model' => $model,
			'imgData' => $imgData
		));
	}


	/**
	 * @param $imgData
	 * @param $model MainUnit
	 * @return UploadedFile | false
	 */
	private function cropImage($imgData, &$model)
	{
		Yii::import('application.models.User');

		$jpegQuality = 90;

		/** @var $user User */
		$user = User::model()->findByPk((int)$model->model_id);
		if (!$user) {
			$model->addError('image_id', 'Указанный пользователь не существует');
			return false;
		}

		if (!$user->image_id) {
			$model->addError('image_id', 'У пользователя не загружен аватар');
			return false;
		}


		$x = ( isset($imgData['x']) && $imgData['x']>0 && $imgData['x']<1 ) ? $imgData['x']:0;
		$y = ( isset($imgData['y']) && $imgData['y']>0 && $imgData['y']<1 ) ? $imgData['y']:0;

		$w = ( isset($imgData['w']) && $imgData['w']>0 && ($imgData['w']+$x)<=1 ) ? $imgData['w']:1-$x;
		$h = ( isset($imgData['h']) && $imgData['h']>0 && ($imgData['h']+$y)<=1 ) ? $imgData['h']:1-$y;

		if ( empty( $imgData['photo'] )) {
			$model->addError('photo', 'Выберите фотографию');
			return false;
		}

		$photoId = intval($imgData['photo']);


		/** @var $photo UploadedFile */
		$photo = UploadedFile::model()->findByPk($photoId);
		if (is_null($photo)) {
			$model->addError('photo', 'Фото не найдено');
			return false;
		}

		$fileName = UploadedFile::UPLOAD_PATH .'/'.$photo->path.'/'.$photo->name.'.'.$photo->ext;

		if (!file_exists($fileName)) {
			$model->addError('photo', 'Оригинал фото не найден');
			return false;
		}

		/** @var $imageHandler imageHandler */
		$ih = new imageHandler($fileName, imageHandler::FORMAT_JPEG);
		$iWidth = $ih->getImage()->getImageWidth();
		$iHeight = $ih->getImage()->getImageHeight();

		$toWidth = ceil($w * $iWidth);
		$toHeight = ceil($h * $iHeight);
		$offsetX = ceil($x * $iHeight);
		$offsetY = ceil($y * $iHeight);


		$target_w = 95;
		$target_h = 95;


		$ih->jCrop($toWidth, $toHeight, $offsetX, $offsetY, $target_w, $target_h, $jpegQuality);

		$file = new UploadedFile();
		$file->author_id = Yii::app()->getUser()->getId();
		$file->path = IndexSpecPhoto::getImagePath();
		$file->name = time() . '_' . rand(0, 99);
		$file->ext = 'jpg';
		$file->type = UploadedFile::IMAGE_TYPE;
		$file->size = $ih->getImageSize();

		$file->save(false);


		if ( !file_exists(ltrim($file->path, '/'))) {
			mkdir(ltrim($file->path, '/'), 0755, true);
		}

		if ($ih->saveImage(ltrim($file->path, '/') . '/' . $file->name. '.' .$file->ext)) {
			return $file;
		} else {
			return false;
		}

	}


	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 *
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if (Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if (!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl'])
					? $_POST['returnUrl'] : array('admin'));
		} else
			throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
	}


	/**
	 * Manages all models.
	 */
	public function actionIndex()
	{
		$model = new IndexSpecPhoto('search');
		$model->unsetAttributes(); // clear any default values
		if (isset($_GET['IndexSpecPhoto']))
			$model->attributes = $_GET['IndexSpecPhoto'];

		$this->render('index', array(
			'model' => $model,
		));
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 *
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model = IndexSpecPhoto::model()->findByPk($id);
		if ($model === null)
			throw new CHttpException(404, 'The requested page does not exist.');

		return $model;
	}


	/**
	 * Performs the AJAX validation.
	 *
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'index-spec-photo-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}


	/**
	 * Возвращает фотографию пользователя
	 * 
	 * @throws CHttpException
	 */
	public function actionAjaxGetPhoto()
	{
		Yii::import('application.models.User');

		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('model_id') );
		if ( !$request->getIsAjaxRequest() || empty($id) ) {
			throw new CHttpException(404);
		}

		/** @var $user User */
		$user = User::model()->findByPk($id);
		if (!$user) {
			throw new CHttpException(404);
		}

		if ($user->status != User::STATUS_ACTIVE) {
			throw new CHttpException(406, 'Пользователь не имеет статус «Активен»');
		}

		if (!$user->image_id) {
			throw new CHttpException(404, 'У пользователя не задан аватар');
		}


		$html = $this->renderPartial('_item', array(
			'user' => $user
		), true);

		die ( json_encode( array(
			'success' => true,
			'html'    => $html,
			'name'    => $user->name
		), JSON_NUMERIC_CHECK ) );
	}

	/**
	 * Возвращает выпадающий список для смены статуса пользователя
	 *
	 * @param integer $uid user id
	 */
	public function actionAjaxStatusList()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );
		if ( !$request->getIsAjaxRequest() || empty($id) ) {
			throw new CHttpException(404);
		}

		/** @var $current IndexSpecPhoto */
		$current = IndexSpecPhoto::model()->findByPk($id);
		if ($current === null) {
			throw new CHttpException(404);
		}

		$statusList = '';
		foreach (IndexSpecPhoto::$statusName as $key => $status) {
			if ($current->status == $key) {
				$statusList.= CHtml::tag('li', array(
					'data-id'        => $current->id,
					'data-status_id' => $current->status,
					'class'          => 'current-status',
				), $status);
			} else {
				$statusList.= CHtml::tag('li', array('data-id'=>$current->id, 'data-status_id'=>$key), $status);
			}
		}
		die ( json_encode( array('success'=>true, 'html'=>$statusList), JSON_NUMERIC_CHECK ) );
	}

	/**
	 * @brief Обрабатывает запрос на смену статуса из контекстного меню
	 * @return JSON
	 */
	public function actionAjaxStatusUpdate()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );
		$status = intval( $request->getParam('status') );
		if ( !$request->getIsAjaxRequest() || empty($id) || !isset(IndexSpecPhoto::$statusName[$status])) {
			throw new CHttpException(404);
		}

		/** @var $current IndexSpecPhoto */
		$current = IndexSpecPhoto::model()->findByPk($id);
		if ($current === null) {
			throw new CHttpException(404);
		}

		$current->status = $status;
		$current->save(false);

		die ( json_encode( array('success'=>true), JSON_NUMERIC_CHECK ) );
	}
}
