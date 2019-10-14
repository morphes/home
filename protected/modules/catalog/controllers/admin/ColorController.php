<?php

class ColorController extends AdminController
{
        public $layout = 'webroot.themes.myhome.views.layouts.backend';

        public function filters() {
                return array('accessControl');
        }

        public function accessRules() {

                return array(
                        array('allow',
                                'roles'=>array(
                                        User::ROLE_ADMIN,
                                        User::ROLE_POWERADMIN,
                                        User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_FREELANCE_PRODUCT,
                                ),
                        ),
                        array('deny',
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
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new CatColor;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['CatColor']))
		{
			$model->attributes=$_POST['CatColor'];
			if($model->save())
				$this->redirect(array('create'));
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
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['CatColor']))
		{
			$model->attributes=$_POST['CatColor'];
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
		$dataProvider = new CActiveDataProvider('CatColor', array(
                        'pagination'=>array(
                                'pageSize'=>20,
                        )));

		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=CatColor::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='cat-color-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}


        /**
         * Автокомплит опции Цвет
         * @param $term
         * @throws CHttpException
         */
        public function actionAcColor($term)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);
                $this->layout = false;
                $data = Yii::app()->db->createCommand("SELECT t.id, t.name FROM `cat_color` t WHERE t.name LIKE '" . CHtml::encode($term) . "%'")->queryAll();
                $results = array();
                foreach($data as $record) {
                        $results[] = array(
                                'label'=>$record['name'],
                                'value'=>$record['name'],
                                'id'=>$record['id'],
                        );
                }
                die(CJSON::encode($results));
        }
}
