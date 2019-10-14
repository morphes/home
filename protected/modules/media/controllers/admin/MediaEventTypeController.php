<?php

class MediaEventTypeController extends AdminController
{
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
				'actions'=>array('index', 'create', 'delete', 'update'),
				'roles'=>array(
					User::ROLE_ADMIN,
					User::ROLE_JOURNALIST,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
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
		$model = new MediaEventType();
		if(isset($_POST['MediaEventType']))
		{
			$model->attributes=$_POST['MediaEventType'];
			$model->status = MediaEventType::STATUS_ACTIVE;

			if($model->save())
				$this->redirect(array('index'));
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
		$model = $this->loadModel($id);

		if(isset($_POST['MediaEventType']))
		{
			$model->attributes=$_POST['MediaEventType'];

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
		if ( !Yii::app()->getRequest()->getIsAjaxRequest() )
			throw new CHttpException(404);

		$model = $this->loadModel($id);
		$model->status = MediaEventType::STATUS_DELETED;
		$model->save(false);

		die ( json_encode( array('success'=>true) ) );
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model = new MediaEventType('search');
		$model->unsetAttributes(); // clear any default values
		if (isset($_GET['MediaEventType']))
			$model->attributes = $_GET['MediaEventType'];

		$this->render('index', array(
			'model'=> $model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 * @return MediaEventType
	 */
	public function loadModel($id)
	{
		$model=MediaEventType::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
