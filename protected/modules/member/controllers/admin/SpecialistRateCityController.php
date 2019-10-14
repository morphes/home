<?php

class SpecialistRateCityController extends AdminController
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout = 'webroot.themes.myhome.views.layouts.backend';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array('accessControl');
	}

	public function accessRules()
	{

		return array(
			array('allow',
				'roles' => array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_MODERATOR
				),
			),
			array('deny',
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
		$model=new SpecialistRateCity;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['SpecialistRateCity']))
		{
			$model->attributes=$_POST['SpecialistRateCity'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
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

		if(isset($_POST['SpecialistRateCity']))
		{
			$model->attributes=$_POST['SpecialistRateCity'];
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
	 * Lists all models.
	 */
	public function actionIndex($id)
	{
		if(isset($_GET['Rate'])) {
			$model = new SpecialistRateCity();
			$params = $_GET['Rate'];

			if($params['city_id']){
				$model->city_id = $params['city_id'];
			}

			if($params['service_id']){
				$model->service_id = $params['service_id'];
			}

			if($params['rate_id']) {
				$model->rate_id = $params['rate_id'];
			}

			if($model->save()){
				$this->redirect(array('index','id'=>$params['rate_id']));
			}
		}
		$rate = SpecialistRate::model()->findByPk((int)$id);
		$dataProvider=new CActiveDataProvider('SpecialistRateCity', array(
		'criteria' => array(
			'condition'=> 'rate_id='.(int)$id,
			'order'=>'rate_id DESC'
		)));

		$this->render('index',array(
			'rate' => $rate,
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new SpecialistRateCity('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['SpecialistRateCity']))
			$model->attributes=$_GET['SpecialistRateCity'];

		$this->render('admin',array(
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
		$model=SpecialistRateCity::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='specialist-rate-city-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
