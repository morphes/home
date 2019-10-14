<?php

class MediaPromoController extends AdminController
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
				'actions'=>array('index', 'create', 'delete', 'update', 'uploadPreview', 'deletePreview'),
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
		$model = new MediaPromo();

		$model->status = MediaPromo::STATUS_HIDE;

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

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['MediaPromo']))
		{
			$model->attributes=$_POST['MediaPromo'];

			if($model->save())
				$this->redirect(array('index'));
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
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model = new MediaPromo('search');
		$model->unsetAttributes(); // clear any default values
		if (isset($_GET['MediaPromo']))
			$model->attributes = $_GET['MediaPromo'];

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
		$model=MediaPromo::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='media-promo-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	/**
	 * Метод который занимается сохранением фотографии для превью Знания
	 */
	public function actionUploadPreview($id)
	{

		$layout = false;

		$model = MediaPromo::model()->findByPk((int)$id);
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
		$model = MediaPromo::model()->findByPk((int)Yii::app()->getRequest()->getParam('modelId'));
		$image = UploadedFile::model()->findByPk((int)Yii::app()->getRequest()->getParam('imageId'));

		if ($model && $image && $model->image_id == $image->id) {
			$image->delete();

			$model->image_id = 0;
			$model->save(false);

			die('success');
		}

		die('error');
	}

}
