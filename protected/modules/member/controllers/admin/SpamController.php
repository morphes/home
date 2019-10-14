<?php

class SpamController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';

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
				),
			),
			array('deny',
				'users' => array('*'),
			),
		);
	}


	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$messageFilterFlag = false;

		$model = $this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['Spam'])) {
			$model->attributes = $_POST['Spam'];
			if ($model->save())
				$this->redirect(array('index', 'id' => $model->id));
		}

		if (!empty($model->allMessageFilter)) {
			$messageFilterFlag = 'allMessage';
		}

		if (!empty($model->countMessageFilter)) {
			$messageFilterFlag = 'countMessage';
		}

		if (!empty($model->searchString)) {
			$messageFilterFlag = 'searchString';
		}


		$this->render('update', array(
			'model' => $model,
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
		$model = new Spam('search');
		$messageFilterFlag = false;

		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Spam']))
			$model->attributes=$_GET['Spam'];

		if(!empty($model->allMessageFilter) and $messageFilterFlag==false)
		{
			$messageFilterFlag ='allMessage';
		}

		if(!empty($model->countMessageFilter) and $messageFilterFlag==false)
		{
			$messageFilterFlag ='countMessage';
		}

		if(!empty($model->searchString) and $messageFilterFlag==false)
		{
			$messageFilterFlag = 'searchString';
		}

		$this->render('index',array(
			'model'=>$model,
			'messageFilterFlag' =>$messageFilterFlag,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Spam::model()->findByPk((int) $id);
		if($model===null)
			$this->redirect(array('notfound'));
			//throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='spam-form')
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


		$spam = $this->loadModel($uid);

		foreach (Spam::$statusLabels as $key => $status) {
			if ($spam->status == $key) {
				$statusList.="<li id='update-status' class='current-status' user-id='{$spam->id}' status-id='{$key}'>{$status}</li>";
			} else {
				$statusList.="<li id='update-status' user-id='{$spam->id}' status-id='{$key}'>{$status}</li>";
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

		$spam = $this->loadModel($uid);

		$spam->status = $status;
		$spam->save();

		die(json_encode(array('success'=>true), JSON_NUMERIC_CHECK));
	}


	/**
	 * Если не найдена заявка кидаем на кастомизированую notfound
	 */
	public function actionNotFound()
	{
		$this->render('notfound');
	}

	public function actionView($id)
	{

		$model = MsgBody::model()->findByPk($id);
		$this->render('view',array(
			'model'=>$model,

		));
	}
}
