<?php

class SearchController extends AdminController
{
        public $layout = 'webroot.themes.myhome.views.layouts.backend';


	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

        public function accessRules()
        {

                return array(
                        array('allow',
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

	public function actionMeansCreate()
	{
		$model=new SearchMeans;

		if(isset($_POST['SearchMeans']))
		{
			$model->attributes=$_POST['SearchMeans'];
			if($model->save())
				$this->redirect(array('meansAdmin'));
		}

		$this->render('means_create',array(
			'model'=>$model,
		));
	}

	public function actionMeansUpdate($id)
	{
		$model=$this->loadMeansModel($id);

		if(isset($_POST['SearchMeans']))
		{
			$model->attributes=$_POST['SearchMeans'];
			if($model->save())
				$this->redirect(array('meansAdmin'));
		}

		$this->render('means_update',array(
			'model'=>$model,
		));
	}

	public function actionMeansDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			$this->loadMeansModel($id)->delete();
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400);
	}

	public function actionMeansAdmin()
	{
		$dataProvider = new CActiveDataProvider('SearchMeans', array(
                        'pagination'=>array(
                                'pageSize'=>20,
                        ),
                ));

		$this->render('means_admin',array(
			'dataProvider'=>$dataProvider,
		));
	}

	public function loadMeansModel($id)
	{
		$model=SearchMeans::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404);
		return $model;
	}

}
