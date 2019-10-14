<?php

class ArticleController extends AdminController
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

	public function actionList()
	{
		$sectionId = intval( Yii::app()->getRequest()->getParam('section_id') );
		/** @var $section HelpSection */
		$section = HelpSection::model()->findByPk($sectionId, 'status<>:st', array(':st'=>HelpSection::STATUS_DELETED));
		if ( is_null($section) )
			throw new CHttpException(404);

		$dataProvider=new CActiveDataProvider('HelpArticle', array(
			'criteria' => array(
				'condition' => 'section_id=:sid AND status IN (:st1, :st2)',
				'order' => 'position ASC',
				'params' => array(':sid'=>$section->id, ':st1'=>HelpArticle::STATUS_OPEN, ':st2'=>HelpArticle::STATUS_HIDE),
			),
			'pagination' => array(
				'pageSize' => 100,
			),
		));

		$this->render('list',array(
			'dataProvider'=>$dataProvider,
			'section' => $section,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionUpdate()
	{
		$id = intval(Yii::app()->getRequest()->getParam('id'));
		$sectionId = intval(Yii::app()->getRequest()->getParam('section_id'));

		/** @var $section HelpSection */
		$section = HelpSection::model()->findByPk($sectionId, 'status<>:st', array(':st'=>HelpSection::STATUS_DELETED));
		if ( is_null($section) )
			throw new CHttpException(404);

		if (empty($id)) {
			$article = new HelpArticle();
		} else {
			$article = HelpArticle::model()->findByPk($id, 'status <> :st', array(':st'=>HelpArticle::STATUS_DELETED));
			if (is_null($article))
				throw new CHttpException(404);
		}

		// update/create section
		if (isset($_POST['HelpArticle'])) {
			$article->attributes = $_POST['HelpArticle'];
			if ($article->getIsNewRecord()) {
				$article->author_id = Yii::app()->getUser()->getId();
				$article->section_id = $section->id;
				$article->base_path_id = $section->base_path_id;
			}

			if ($article->validate()) {
				if ($article->getIsNewRecord()) {
					$article->save(false);
					$article->position = $article->id;
					$article->save(false);
				} else {
					$article->save(false);
				}
				$this->redirect( $this->createUrl('list', array('section_id'=>$section->id)) );
			}
		}

		return $this->render('update',array(
			'article'=>$article,
			'section'=>$section,
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

		$article = HelpArticle::model()->findByPk($id);
		if (is_null($article))
			throw new CHttpException(404);

		$article->status = HelpArticle::STATUS_DELETED;
		$article->save(false);
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

		/** @var $current HelpArticle */
		$current = HelpArticle::model()->findByPk($id);

		if (is_null($current))
			die ( CJSON::encode( array('error'=>true) ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'section_id=:sid AND status IN (:st1, :st2) AND position<:position';
		$criteria->order = 'position DESC';
		$criteria->params = array(
			':position' => $current->position,
			':sid' => $current->section_id,
			':st1'=>HelpArticle::STATUS_OPEN,
			':st2'=>HelpArticle::STATUS_HIDE
		);

		$next = HelpArticle::model()->find($criteria);
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

		/** @var $current HelpArticle */
		$current = HelpArticle::model()->findByPk($id);

		if (is_null($current))
			die ( CJSON::encode( array('error'=>true) ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'section_id=:sid AND status IN (:st1, :st2) AND position>:position';
		$criteria->order = 'position ASC';
		$criteria->params = array(
			':position' => $current->position,
			':sid' => $current->section_id,
			':st1'=>HelpArticle::STATUS_OPEN,
			':st2'=>HelpArticle::STATUS_HIDE
		);

		$next = HelpArticle::model()->find($criteria);
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
