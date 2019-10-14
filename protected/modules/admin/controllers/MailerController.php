<?php

class MailerController extends AdminController
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
			array('allow',
				'actions'=>array('index','view','create','update', 'upload'),
				'roles'=>array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
				),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$groupList = Usergroup::model()->findAll();
		$this->render('view',array(
			'model'=>$this->loadModel($id),
			'groupList'=>$groupList,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Mailer();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Mailer']))
		{
			$model->attributes=$_POST['Mailer'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$groupList = Usergroup::model()->findAll();
		$this->render('update',array(
			'model'=>$model,
			'groupList'=>$groupList,
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

		if(isset($_POST['Mailer']))
		{
			$model->attributes=$_POST['Mailer'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$groupList = Usergroup::model()->findAll();
		$this->render('update',array(
			'model'=>$model,
			'groupList'=>$groupList,
		));
	}
	
	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		//$this->rightbar = null;
		
		$dataProvider=new CActiveDataProvider('Mailer', array( 'criteria' => array('order'=>'id DESC'), 'pagination'=>array('pageSize'=>15) ));
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Mailer::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='mailer-delivery-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function actionUpload()
	{
		$uploaded = Yii::app()->file->set('upload'); // ?
		$fname = time() . $_FILES["upload"]['name'];

		$image = Yii::app()->image->load($_FILES["upload"]["tmp_name"]);
		$path = Mailer::UPLOAD_IMAGE_DIR;
		if (!file_exists($path))
			mkdir($path, 0755, true);
		$image->quality(85)->save($path . '/' . $fname);

		$url = $this->createAbsoluteUrl('/' . $path . '/' . $fname);
		$callback = $_GET['CKEditorFuncNum'];
		$output = '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(' . $callback . ', "' . $url . '","' . '' . '");</script>';
		echo $output;
		die();
	}
}
