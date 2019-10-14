<?php

class FaqController extends AdminController
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
				'actions'=>array('list','update','delete','up','down'),
				'roles'=>array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_JOURNALIST,
				),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionList($base = Help::BASE_USER)
	{
		if ( empty(Help::$baseNames[$base]))
			throw new CHttpException(404);

		$dataProvider=new CActiveDataProvider('HelpFaq', array(
			'criteria' => array(
				'condition' => 'base_path_id=:bid AND status IN (:st1, :st2)',
				'order' => 'position ASC',
				'params' => array(':bid'=>$base, ':st1'=>HelpFaq::STATUS_OPEN, ':st2'=>HelpFaq::STATUS_HIDE),
			),
			'pagination' => array(
				'pageSize' => 100,
			),
		));

		$this->render('list',array(
			'dataProvider'=>$dataProvider,
			'baseId' => $base,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionUpdate()
	{
		$id = intval(Yii::app()->getRequest()->getParam('id'));
		$base = intval(Yii::app()->getRequest()->getParam('base'));

		if ( empty(Help::$baseNames[$base]))
			throw new CHttpException(404, 'Invalid base section');

		if (empty($id)) {
			$faq = new HelpFaq();
		} else {
			$faq = HelpFaq::model()->findByPk($id, 'status <> :st', array(':st'=>HelpFaq::STATUS_DELETED));
			if (is_null($faq))
				throw new CHttpException(404);
		}

		// update/create faq
		if (isset($_POST['HelpFaq'])) {
			$faq->attributes = $_POST['HelpFaq'];
			if ($faq->getIsNewRecord()) {
				$faq->author_id = Yii::app()->getUser()->getId();
				$faq->base_path_id = $base;
			}

			if ($faq->validate()) {
				if ($faq->getIsNewRecord()) {
					$faq->save(false);
					$faq->position = $faq->id;
					$faq->save(false);
				} else {
					$faq->save(false);
				}
				$this->redirect( $this->createUrl('list', array('base'=>$base)) );
			}
		}

		return $this->render('update',array(
			'faq'=>$faq,
			'baseId' => $base,
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

		$faq = HelpFaq::model()->findByPk($id);
		if (is_null($faq))
			throw new CHttpException(404);

		$faq->status = HelpFaq::STATUS_DELETED;
		$faq->save(false);
		die ( CJSON::encode( array('success'=>true) ) );
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

		/** @var $current HelpFaq */
		$current = HelpFaq::model()->findByPk($id);

		if (is_null($current))
			die ( CJSON::encode( array('error'=>true) ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'base_path_id=:bid AND status IN (:st1, :st2) AND position<:position';
		$criteria->order = 'position DESC';
		$criteria->params = array(
			':position' => $current->position,
			':bid' => $current->base_path_id,
			':st1'=>HelpFaq::STATUS_OPEN,
			':st2'=>HelpFaq::STATUS_HIDE
		);

		$next = HelpFaq::model()->find($criteria);
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

		/** @var $current HelpFaq */
		$current = HelpFaq::model()->findByPk($id);

		if (is_null($current))
			die ( CJSON::encode( array('error'=>true) ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'base_path_id=:bid AND status IN (:st1, :st2) AND position>:position';
		$criteria->order = 'position ASC';
		$criteria->params = array(
			':position' => $current->position,
			':bid' => $current->base_path_id,
			':st1'=>HelpFaq::STATUS_OPEN,
			':st2'=>HelpFaq::STATUS_HIDE
		);

		$next = HelpFaq::model()->find($criteria);
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
