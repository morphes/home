<?php

class StoreNewsController extends AdminController
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
					'imageUpload', 'imageDelete'
				),
				'roles'   => array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_JUNIORMODERATOR,
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
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model = new StoreNews;

		$model->status = StoreNews::STATUS_NEW;
		$model->user_id = Yii::app()->user->id;
		$model->save(false);

		$this->redirect($this->createUrl('update', array('id' => $model->id)));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['StoreNews']))
		{
			$model->attributes=$_POST['StoreNews'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
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
	 * Manages all models.
	 */
	public function actionIndex()
	{
		$model=new StoreNews('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['StoreNews']))
			$model->attributes=$_GET['StoreNews'];

		$this->render('index',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=StoreNews::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='store-news-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}


	/**
	 * Загружает фотографию для шапки Минисайта
	 *
	 * @throws CHttpException
	 */
	public function actionImageUpload()
	{
		if (!Yii::app()->request->isPostRequest) {
			throw new CHttpException(400);
		}

		/** @var $model StoreNews */
		$model = StoreNews::model()->findByAttributes(array(
			'id' => Yii::app()->request->getParam('nid'),
		));


		if (!$model) {
			throw new CHttpException(404);
		}


		$model->setImageType('image');
		$file = UploadedFile::loadImage($model, 'file', '', true, null, true, array('width' => 140, 'height' => 140));
		if ($file) {
			$file_errors = $file->getErrors();
		} else {
			die(CJSON::encode(array(
				'success' => false,
				'message' => 'Не удалось загрузить файл'
			)));
		}

		if (isset($file_errors) && isset($file_errors['image'])) {
			$error_message = $file_errors['image'][0];
			die(CJSON::encode(array(
				'success' => false,
				'message' => $error_message
			)));
		}

		$model->image_id = $file->id;
		$model->save(false);

		$this->layout = false;

		die(CJSON::encode(array(
			'success' => true,
			'html'    => $this->renderPartial('_image', array('file' => $file), true)
		)));
	}


	/**
	 * Удаляет фотографию шапки Минисайта
	 *
	 * @throws CHttpException
	 */
	public function actionImageDelete()
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(400);
		}

		$success = false;
		$message = '';

		$fid = Yii::app()->request->getParam('fid');
		$nid = Yii::app()->request->getParam('nid');

		$model = StoreNews::model()->findByPk((int) $nid);
		$file = UploadedFile::model()->findByPk((int) $fid);

		if(!$model || !$file) {
			throw new CHttpException(403);
		}

		// если файл принадлежит указанному магазину
		if ($model->image_id == $file->id) {

			// Очищаем инфу о загруженной фотографии
			$model->image_id = null;

			// сохранение магазина
			$model->save(false);

			$success = true;
			$message = 'Фото успешно удалено';
		} else {
			$success = false;
			$message = 'Удаляемая фофтография не принадлежит магазину';
		}

		die(CJSON::encode(array(
			'success' => $success,
			'message' => $message
		)));
	}
}
