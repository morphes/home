<?php

class StatSpecialistController extends AdminController
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
				'roles' => array(
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN
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

	public function actionStatistic($id)
	{
		Yii::import('application.modules.idea.models.*');
		$model = User::model()->findByPk($id);
		$timeFrom = false;
		$timeTo = false;
		$city = false;

		//Переливание статистики из Reddis в mysql при открытии статистики магазина
		StatSpecialist::updateStatSpecialistMySql('STAT:SPECIALIST:' . $id . ':*');
		StatProject::updateStatProjectMySql('STAT:PROJECT:' . $id . ':*');
		StatUserService::updateStatUserServMySql('STAT:USER:' . $id . ':*');

		if (!empty($_GET['timeFrom']) || !empty($_GET['timeTo'])) {
			$timeFrom = $_GET['timeFrom'];
			$timeTo = $_GET['timeTo'];
		}

		if (isset($_GET['city']) && $_GET['city']!=='null'){
			$city = (int)$_GET['city'];
		}




			$statSpecialistModel = StatSpecialist::model()->getStat($id, strtotime($timeFrom), strtotime($timeTo));
			$statProjectModel = StatProject::model()->getStat($id, strtotime($timeFrom), strtotime($timeTo));
			$statProjectModelDp = StatProject::model()->getStatTable($id, strtotime($timeFrom), strtotime($timeTo),true);
			$statUserService = StatUserService::model()->getStatTable($id, strtotime($timeFrom), strtotime($timeTo), $city);

		$this->render('statistic', array(
			'model'          	=> $model,
			'statSpecialistModel' 	=> $statSpecialistModel,
			'statProjectModel'      => $statProjectModel,
			'statProjectModelDp'    => $statProjectModelDp,
			'timeFrom'       	=> $timeFrom,
			'timeTo'         	=> $timeTo,
			'statUserService'       => $statUserService,
			'city'			=> $city
		));

	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new StatSpecialist;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['StatSpecialist']))
		{
			$model->attributes=$_POST['StatSpecialist'];
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

		if(isset($_POST['StatSpecialist']))
		{
			$model->attributes=$_POST['StatSpecialist'];
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
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider = new CActiveDataProvider('StatSpecialist');
		$this->render('index', array(
			'dataProvider' => $dataProvider,
		));
	}


	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model = new StatSpecialist('search');
		$model->unsetAttributes(); // clear any default values
		if (isset($_GET['StatSpecialist']))
			$model->attributes = $_GET['StatSpecialist'];

		$this->render('admin', array(
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
		$model = StatSpecialist::model()->findByPk($id);
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
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'stat-specialist-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
