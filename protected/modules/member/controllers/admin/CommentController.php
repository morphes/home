<?php

class CommentController extends AdminController
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
				'actions' => array('index', 'view', 'update', 'delete'),
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
	 * Страница со списком всех комментов
	 */
	public function actionIndex()
	{
		$model = new Comment('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Comment']))
			$model->attributes=$_GET['Comment'];
		
		$criteria = new CDbCriteria();
		
		Yii::import('application.modules.idea.models.*');
		Yii::import('application.models.User');
		Yii::import('application.modules.content.models.News');
		Yii::import('application.modules.media.models.*');
                $this->render('index', array(
			'model'		=> $model,
			'dataProvider'	=> $model->search()
                ));
	}
	
	/**
	 * Просмотр детальной страницы комментария
	 * @param interger $id  ID комментария
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
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
		
		if (isset($_POST['Comment']))
		{
			$model->attributes=$_POST['Comment'];
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
		$model = $this->loadModel($id);
		
		if ($model) {
			$model->delete();
		}

                // if AJAX request (triggered by deletion via list grid view), we should not redirect the browser
                if (!isset($_GET['ajax']))
                        $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model = Comment::model()->findByPk($id);
		
		if ($model===null)
			throw new CHttpException(404);
		
		return $model;
	}
	
}