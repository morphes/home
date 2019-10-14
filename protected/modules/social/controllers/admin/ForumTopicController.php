<?php

class ForumTopicController extends AdminController
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

			array('allow',
				'actions'=>array('index'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('create', 'update', 'delete', 'deleteFile', 'view'),
				'roles'=>array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
				),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function beforeAction($action)
	{
		$this->rightbar = null;

		return parent::beforeAction($action);
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
		$model = new ForumTopic;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		$model->author_id = Yii::app()->user->id;

		if(isset($_POST['ForumTopic']))
		{
			$model->attributes=$_POST['ForumTopic'];

			if($model->save()) {
				$model->saveFiles();

				$this->redirect(array('index'));
			}
		}

		$this->render('create',array(
			'model'=>$model,
		));
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

		if(isset($_POST['ForumTopic']))
		{
			$model->attributes = $_POST['ForumTopic'];

			if($model->save()) {
				$model->saveFiles();

				$this->redirect(array('index'));
			}
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}


	/**
	 * Удаление привязанного файла к топику
	 */
	public function actionDeleteFile($id = null)
	{
		$success = false;

		if (ForumFile::model()->deleteAllByAttributes(array('file_id' => (int)$id)))
			$success = true;

		die(json_encode(array(
			'success' => $success
		)));
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
			$model = $this->loadModel($id);
			$model->status = ForumTopic::STATUS_DELETED;
			$model->save(false);

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

		$model = new ForumTopic('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['ForumTopic']))
			$model->attributes=$_GET['ForumTopic'];

		$this->render('index',array(
			'model'=>$model,
		));;
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=ForumTopic::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='forum-topic-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
