<?php

class BanController extends AdminController
{
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
				'actions'=>array('index','view', 'create', 'delete'),
				'roles'=>array(
					User::ROLE_POWERADMIN
				),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $ip the ID of the model to be displayed
	 */
	public function actionView($ip)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($ip),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new IpBan();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['IpBan']))
		{
			$model->attributes=$_POST['IpBan'];
			if($model->save())
				$this->redirect(array('index'));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}
	
	public function actionDelete($ip)
	{
		return IpBan::model()->deleteByPk(ip2long($ip));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('IpBan', array(
			'criteria' => array(
				'order' => 'create_time desc',
			    )
		    ));
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($ip)
	{
		$model= IpBan::model()->findByPk(ip2long($ip));
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}