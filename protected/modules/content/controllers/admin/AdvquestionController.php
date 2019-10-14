<?php

class AdvquestionController extends AdminController
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
				'roles'=>array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_MODERATOR,
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
		$model=new AdvQuestion;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['AdvQuestion']))
		{
			$model->attributes=$_POST['AdvQuestion'];
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

		if(isset($_POST['AdvQuestion']))
		{
			$model->attributes=$_POST['AdvQuestion'];
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
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('AdvQuestion');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new AdvQuestion('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['AdvQuestion']))
			$model->attributes=$_GET['AdvQuestion'];

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
		$model=AdvQuestion::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='adv-question-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	/**
	 * @brief Возвращает выпадающий список для смены статуса обработки спам сообщения.
	 * @param integer $uid user id
	 */
	public function actionAjax_status_list($uid)
	{
		$this->layout = false;
		$statusList = '';

		if ( !Yii::app()->request->isAjaxRequest )
			throw new CHttpException(400);


		$model = $this->loadModel($uid);

		foreach (AdvQuestion::$statusLabels as $key => $status) {
			if ($model->status == $key) {
				$statusList.="<li id='update-status' class='current-status' user-id='{$model->id}' status-id='{$key}'>{$status}</li>";
			} else {
				$statusList.="<li id='update-status' user-id='{$model->id}' status-id='{$key}'>{$status}</li>";
			}
		}

		die(CJSON::encode(array('success'=>true, 'html'=>$statusList)));
	}

	/**
	 * @brief Обрабатывает запрос на смену статуса из контекстного меню
	 * @param integer $uid
	 * @param integer $status
	 *
	 * @throws CHttpException
	 * @return JSON
	 */
	public function actionAjax_status_update($uid, $status)
	{
		//$this->layout = false;

		if ( !Yii::app()->request->isAjaxRequest )
			throw new CHttpException(400);

		$model = $this->loadModel($uid);

		$model->status = $status;
		$model->save();

		die(json_encode(array('success'=>true), JSON_NUMERIC_CHECK));
	}
}
