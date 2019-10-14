<?php

class ChapterController extends AdminController
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
		$articleId = intval( Yii::app()->getRequest()->getParam('article_id') );
		/** @var $article HelpArticle */
		$article = HelpArticle::model()->findByPk($articleId, 'status<>:st', array(':st'=>HelpArticle::STATUS_DELETED));
		if ( is_null($article) )
			throw new CHttpException(404);
		$section = HelpSection::model()->findByPk($article->section_id);

		$dataProvider=new CActiveDataProvider('HelpChapter', array(
			'criteria' => array(
				'condition' => 'article_id=:aid AND status IN (:st1, :st2)',
				'order' => 'position ASC',
				'params' => array(':aid'=>$article->id, ':st1'=>HelpChapter::STATUS_OPEN, ':st2'=>HelpChapter::STATUS_HIDE),
			),
			'pagination' => array(
				'pageSize' => 100,
			),
		));

		$this->render('list',array(
			'dataProvider'=>$dataProvider,
			'article' => $article,
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
		$articleId = intval(Yii::app()->getRequest()->getParam('article_id'));

		/** @var $article HelpArticle */
		$article = HelpArticle::model()->findByPk($articleId, 'status<>:st', array(':st'=>HelpArticle::STATUS_DELETED));
		if ( is_null($article) )
			throw new CHttpException(404);

		if (empty($id)) {
			$chapter = new HelpChapter();
		} else {
			$chapter = HelpChapter::model()->findByPk($id, 'status <> :st', array(':st'=>HelpArticle::STATUS_DELETED));
			if (is_null($chapter))
				throw new CHttpException(404);
		}

		// update/create section
		if (isset($_POST['HelpChapter'])) {
			$chapter->attributes = $_POST['HelpChapter'];
			if ($chapter->getIsNewRecord()) {
				$chapter->author_id = Yii::app()->getUser()->getId();
				$chapter->article_id = $article->id;
				$chapter->base_path_id = $article->base_path_id;
			}

			if ($chapter->validate()) {
				if ($chapter->getIsNewRecord()) {
					$chapter->save(false);
					$chapter->position = $chapter->id;
					$chapter->save(false);
				} else {
					$chapter->save(false);
				}
				$this->redirect( $this->createUrl('list', array('article_id'=>$article->id)) );
			}
		}

		$section = HelpSection::model()->findByPk($article->section_id);

		return $this->render('update',array(
			'chapter'=>$chapter,
			'article'=>$article,
			'section' => $section,
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

		$chapter = HelpChapter::model()->findByPk($id);
		if (is_null($chapter))
			throw new CHttpException(404);

		$chapter->status = HelpChapter::STATUS_DELETED;
		$chapter->save(false);
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

		/** @var $current HelpChapter */
		$current = HelpChapter::model()->findByPk($id);

		if (is_null($current))
			die ( CJSON::encode( array('error'=>true) ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'article_id=:aid AND status IN (:st1, :st2) AND position<:position';
		$criteria->order = 'position DESC';
		$criteria->params = array(
			':position' => $current->position,
			':aid' => $current->article_id,
			':st1'=>HelpChapter::STATUS_OPEN,
			':st2'=>HelpChapter::STATUS_HIDE
		);

		$next = HelpChapter::model()->find($criteria);
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

		/** @var $current HelpChapter */
		$current = HelpChapter::model()->findByPk($id);

		if (is_null($current))
			die ( CJSON::encode( array('error'=>true) ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'article_id=:aid AND status IN (:st1, :st2) AND position>:position';
		$criteria->order = 'position ASC';
		$criteria->params = array(
			':position' => $current->position,
			':aid' => $current->article_id,
			':st1'=>HelpArticle::STATUS_OPEN,
			':st2'=>HelpArticle::STATUS_HIDE
		);

		$next = HelpChapter::model()->find($criteria);
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
