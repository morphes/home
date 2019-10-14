<?php

class CatFoldersController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';


	public function filters()
	{
		return array('accessControl');
	}


	public function accessRules()
	{

		return array(
			array(
				'allow',
				'roles' => array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_MODERATOR,

				),
			),
			array(
				'deny',
				'users' => array('*'),
			),
		);
	}


	/**
	 * Displays a particular model.
	 *
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view', array(
			'model' => $this->loadModel($id),
		));
	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model = new CatFolders;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['CatFolders'])) {
			$model->attributes = $_POST['CatFolders'];
			if ($model->save())
				$this->redirect(array('view', 'id' => $model->id));
		}

		$this->render('create', array(
			'model' => $model,
		));
	}


	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['CatFolders'])) {
			$model->attributes = $_POST['CatFolders'];
			if ($model->save())
				$this->redirect(array('index'));
		}

		$this->render('update', array(
			'model' => $model,
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
		$model = new CatFolders('search');
		$model->unsetAttributes(); // clear any default values
		if (isset($_GET['CatFolders']))
			$model->attributes = $_GET['CatFolders'];

		$this->render('index', array(
			'model' => $model,
		));
	}


	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model = new CatFolders('search');
		$model->unsetAttributes(); // clear any default values
		if (isset($_GET['CatFolders']))
			$model->attributes = $_GET['CatFolders'];

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
		$model = CatFolders::model()->findByPk($id);
		if ($model === null)
			throw new CHttpException(404, 'The requested page does not exist.');

		return $model;
	}


	/**
	 * Возвращает список
	 * возможных статусов
	 * папки по AJAX
	 *
	 * @param $id
	 *
	 * @throws CHttpException
	 */
	public function actionAjax_status_list($id)
	{
		$this->layout = false;
		$statusList = '';

		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(400);


		$folder = $this->loadModel($id);

		foreach (CatFolders::$statusLabels as $key => $status) {
			if ($folder->status == $key) {
				$statusList .= "<li id='update-status' class='current-status' folder-id='{$folder->id}' status-id='{$key}'>{$status}</li>";
			} else {
				$statusList .= "<li id='update-status' folder-id='{$folder->id}' status-id='{$key}'>{$status}</li>";
			}
		}

		die(CJSON::encode(array('success' => true, 'html' => $statusList)));
	}


	/**
	 * Ajax метод для
	 * смены статуса
	 * папки
	 *
	 * @param $id
	 * @param $status
	 *
	 * @throws CHttpException
	 */
	public function actionAjax_status_update($id, $status)
	{

		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(400);

		$folder = $this->loadModel($id);

		$folder->status = $status;
		$folder->save();

		die(json_encode(array('success' => true), JSON_NUMERIC_CHECK));
	}
}
