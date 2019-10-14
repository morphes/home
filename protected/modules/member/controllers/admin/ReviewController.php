<?php

class ReviewController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'roles'=>array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
				),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionList()
	{
		// configure filter
		$model = new Review('search');

		if(isset($_GET['Review']))
			$model->attributes=$_GET['Review'];

		$date_from = Yii::app()->request->getParam('date_from');
		$date_to = Yii::app()->request->getParam('date_to');

		$criteria = new CDbCriteria();
		// date
		if ($date_from)
			$criteria->compare('t.create_time', '>='.(strtotime($date_from)));
		if ($date_to)
			$criteria->compare('t.create_time', '<='.(strtotime($date_to)+86400));
		// type
		$criteria->compare('t.type', $model->type);
		// спец, на которого отклики
		$criteria->compare('t.spec_id', $model->spec_id);

		// author_id
		$criteria->compare('t.author_id', $model->author_id);
		// id
		if ($model->id)
			$criteria->compare('t.id', explode(',', $model->id), true);

		// status
		if (!empty($model->status)) { // Filter delete status
			$criteria->compare('t.status', $model->status);
		} else {
			$criteria->addCondition('(t.status IN (:stShow, :stHide))');
			$criteria->params[':stShow'] = Review::STATUS_SHOW;
			$criteria->params[':stHide'] = Review::STATUS_HIDE;
		}

		if ($model->spec_login) {

			$spec = User::model()->find('login=:login', [':login' => $model->spec_login]);
			if ($spec) {
				$criteria->compare('spec_id', $spec->id);
			} else {
				$criteria->compare('spec_id', 0);
			}
		}

		if ($model->author_login) {

			$author = User::model()->find('login=:login', [':login' => $model->author_login]);
			if ($author) {
				$criteria->compare('author_id', $author->id);
			} else {
				$criteria->compare('author_id', 0);
			}
		}

		$criteria->order = 'id DESC';

		$pageSize = 50;

		$dataProvider = new CActiveDataProvider('Review', array(
			'criteria' => $criteria,
			'pagination' => array('pageSize' => $pageSize, 'pageVar'=>'page'),
		));

		$this->render('list', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
			'date_from' => $date_from,
			'date_to' => $date_to,
		));
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$review = Review::model()->findByPk($id, 'status IN(:st1, :st2)', array(':st1'=>Review::STATUS_SHOW, ':st2'=>Review::STATUS_HIDE));
		if (is_null($review))
			throw new CHttpException(404);

		$this->render('view',array(
			'review'=>$review,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$review = Review::model()->findByPk($id, 'status IN(:st1, :st2)', array(':st1'=>Review::STATUS_SHOW, ':st2'=>Review::STATUS_HIDE));
		if (is_null($review))
			throw new CHttpException(404);

		if(isset($_POST['Review']))
		{
			$review->setScenario('admUpdate');
			$review->attributes=$_POST['Review'];
			if($review->save())
				$this->redirect(array('list'));
		}

		$this->render('update',array(
			'review'=>$review,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(404);

		$review = Review::model()->findByPk($id, 'status IN(:st1, :st2)', array(':st1'=>Review::STATUS_SHOW, ':st2'=>Review::STATUS_HIDE));
		if (is_null($review))
			throw new CHttpException(404);

		$review->status = Review::STATUS_DELETED;
		$review->save();

		die (CJSON::encode(array('success'=>true)));
	}
}
