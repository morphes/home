<?php

class SectionController extends AdminController
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

		$dataProvider=new CActiveDataProvider('HelpSection', array(
			'criteria' => array(
				'condition' => 'base_path_id=:bid AND status IN (:st1, :st2)',
				'order' => 'position ASC',
				'params' => array(':bid'=>$base, ':st1'=>HelpSection::STATUS_OPEN, ':st2'=>HelpSection::STATUS_HIDE),
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
			$section = new HelpSection();
		} else {
			$section = HelpSection::model()->findByPk($id, 'status <> :st', array(':st'=>HelpSection::STATUS_DELETED));
			if (is_null($section))
				throw new CHttpException(404);
		}

		// update/create section
		if (isset($_POST['HelpSection'])) {
			$section->attributes = $_POST['HelpSection'];
			if ($section->getIsNewRecord()) {
				$section->author_id = Yii::app()->getUser()->getId();
				$section->base_path_id = $base;
			}

			if ($section->validate()) {
				if ($section->getIsNewRecord()) {
					$section->save(false);
					$section->position = $section->id;
					$section->save(false);
				} else {
					$section->save(false);
				}
				$this->redirect( $this->createUrl('list', array('base'=>$base)) );
			}
		}

		return $this->render('update',array(
			'section'=>$section,
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

		$section = HelpSection::model()->findByPk($id);
		if (is_null($section))
			throw new CHttpException(404);

		$section->status = HelpSection::STATUS_DELETED;
		$section->save(false);
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

		/** @var $current HelpSection */
		$current = HelpSection::model()->findByPk($id);

		if (is_null($current))
			die ( CJSON::encode( array('error'=>true) ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'base_path_id=:bid AND status IN (:st1, :st2) AND position<:position';
		$criteria->order = 'position DESC';
		$criteria->params = array(
			':position' => $current->position,
			':bid' => $current->base_path_id,
			':st1'=>HelpSection::STATUS_OPEN,
			':st2'=>HelpSection::STATUS_HIDE
		);

		$next = HelpSection::model()->find($criteria);
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

		/** @var $current HelpSection */
		$current = HelpSection::model()->findByPk($id);

		if (is_null($current))
			die ( CJSON::encode( array('error'=>true) ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'base_path_id=:bid AND status IN (:st1, :st2) AND position>:position';
		$criteria->order = 'position ASC';
		$criteria->params = array(
			':position' => $current->position,
			':bid' => $current->base_path_id,
			':st1'=>HelpSection::STATUS_OPEN,
			':st2'=>HelpSection::STATUS_HIDE
		);

		$next = HelpSection::model()->find($criteria);
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
