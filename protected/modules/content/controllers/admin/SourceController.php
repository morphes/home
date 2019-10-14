<?php

class SourceController extends AdminController
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
	public function accessRules() {

		return array(
			array('allow',
				'actions' => array('index', 'view', 'admin', 'autocomplete'),
				'roles' => array(
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_FREELANCE_IDEA,
				),
			),
			array('allow',
				'actions' => array('createmultiple', 'deletemultiple', 'create', 'update'),
				'roles' => array(
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_FREELANCE_IDEA,
				),
			),
			array('deny',
				'users' => array('*'),
			),
		);
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
		$model=new Source;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Source']))
		{
			$model->attributes=$_POST['Source'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
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

		if(isset($_POST['Source']))
		{
			$model->attributes=$_POST['Source'];
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
	public function actionAdmin()
	{
		$model=new Source('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Source']))
			$model->attributes=$_GET['Source'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Source::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='source-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	/**
	 * Возвращает массив данных для автокомплит-инпута по всем источникам
	 * @param type $term 
	 */
	function actionAutocomplete($term = '')
        {
                if (Yii::app()->request->isAjaxRequest && !empty($term)) {
                        
			$command = Yii::app()->db->createCommand();
			
			$sources = $command->select("name, url, id")
				->from(Source::model()->tableName())
				->where('name LIKE :name', array(':name' => '%'.$term.'%'))
				->queryAll();

			$arr = array();
			if ( ! empty($sources)) {
				foreach ($sources as $item) {
					$arr[] = array(
						'lable' => $item['name'],
						'value' => $item['name'],
						'url'	=> $item['url'],
						'id'	=> $item['id']
					);
				}
			}

			die( CJSON::encode($arr) );
                }
        }
	
	
	/**
	 * Создает пустой источник для указанной модели.
	 */
	public function actionCreateMultiple()
	{
		Yii::import('application.modules.idea.models.Interior');


		$success = false;
		$html = '';
		
		$model_id = (int)Yii::app()->request->getParam('model_id');
		$model_name = Yii::app()->request->getParam('model_name');

		switch ($model_name) {
			case 'Architecture':
				Yii::import('application.modules.idea.models.Architecture');
				$model = Architecture::model()->findByPk($model_id);
				break;
			case 'Interiorpublic':
				Yii::import('application.modules.idea.models.Interiorpublic');
				$model = Interiorpublic::model()->findByPk($model_id);
				break;
			default:
				$model = Interior::model()->findByPk($model_id);
				break;
		}

		
		if ($model) {
			$sourceMultiple = new SourceMultiple();
			$sourceMultiple->model = get_class($model);
			$sourceMultiple->model_id = $model->id;
			
			if ($sourceMultiple->save()) {
				$success = true;
				
				$html = $this->renderPartial(
					'application.modules.idea.views.admin.create._sourceItem',
					array('sourceMultiple' => $sourceMultiple),
					true
				);
			} else {
				print_r($sourceMultiple->getErrors());
				die();
			}
		}
		
		
		die(CJSON::encode(array(
			'success' => $success,
			'html' => $html
		)));
	}
	
	
	/**
	 * Удаляет источник.
	 * 
	 * @param Integer $id 
	 */
	public function actionDeleteMultiple($id = null)
	{
		$success = false;
		
		$model = SourceMultiple::model()->findByPk((int)$id);
		
		if ($model) {
			$model->delete();
			
			$success = true;
		}
		
		die(CJSON::encode(array(
			'success' => $success,
		)));
	}
}
