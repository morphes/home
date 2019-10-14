<?php

class MainroomController extends AdminController
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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				//'actions'=>array('index','view'),
				'roles'=>array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_SENIORMODERATOR,
				),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function init()
	{
		// отключение твиттер-панели
		$this->rightbar = null;
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new MainRoom;

		// update/create section
		if (isset($_POST['MainRoom'])) {
			$model->attributes = $_POST['MainRoom'];
			if ($model->validate()) {
				$model->save(false);
				$model->position = $model->id;
				$model->save(false);

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
		$id = intval($id);
		$model = MainRoom::model()->findByPk($id);

		if (is_null($model)) // Не блокируем удаленные
			throw new CHttpException(404);

		if(isset($_POST['MainRoom'])) {
			$model->attributes=$_POST['MainRoom'];
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
	public function actionDelete()
	{
		$id = intval( Yii::app()->getRequest()->getParam('id') );

		if (!Yii::app()->getRequest()->getIsAjaxRequest() || empty($id))
			throw new CHttpException(404);

		$model = MainRoom::model()->findByPk($id);
		if (is_null($model))
			throw new CHttpException(404);

		$model->status = MainRoom::STATUS_DELETED;
		$model->save(false);
		die ( CJSON::encode( array('success'=>true) ) );

	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model=new MainRoom('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['MainRoom']))
			$model->attributes=$_GET['MainRoom'];

		$this->render('index',array(
			'model'=>$model,
		));
	}

	/**
	 * Move up item position
	 * @return JSON
	 * @author Alexey Shvedov
	 */
	public function actionUp()
	{
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );
		if ( !$request->getIsAjaxRequest() || empty($id) )
			throw new CHttpException(404);

		/** @var $current MainRoom */
		$current = MainRoom::model()->findByPk($id);

		if (is_null($current))
			die ( CJSON::encode( array('error'=>true) ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'status IN (:st1, :st2) AND position<:position';
		$criteria->order = 'position DESC';
		$criteria->params = array(
			':position' => $current->position,
			':st1'=>MainRoom::STATUS_ENABLED,
			':st2'=>MainRoom::STATUS_DISABLED,
		);

		$next = MainRoom::model()->find($criteria);
		if (is_null($next))
			die ( CJSON::encode( array('error'=>true) ) );

		$tmp = $current->position;
		$current->position = $next->position;
		$next->position = $tmp;
		$current->save(false);
		$next->save(false);
		die ( CJSON::encode( array('success'=>true) ) );
	}

	/**
	 * Move down item position
	 * @return JSON
	 * @author Alexey Shvedov
	 */
	public function actionDown()
	{
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );
		if ( !$request->getIsAjaxRequest() || empty($id) )
			throw new CHttpException(404);

		/** @var $current MainRoom */
		$current = MainRoom::model()->findByPk($id);

		if (is_null($current))
			die ( CJSON::encode( array('error'=>true) ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'status IN (:st1, :st2) AND position>:position';
		$criteria->order = 'position ASC';
		$criteria->params = array(
			':position' => $current->position,
			':st1'=>MainRoom::STATUS_ENABLED,
			':st2'=>MainRoom::STATUS_DISABLED,
		);

		$next = MainRoom::model()->find($criteria);
		if (is_null($next))
			die ( CJSON::encode( array('error'=>true) ) );

		$tmp = $current->position;
		$current->position = $next->position;
		$next->position = $tmp;
		$current->save(false);
		$next->save(false);
		die ( CJSON::encode( array('success'=>true) ) );
	}

}
