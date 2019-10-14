<?php

class PortfolioController extends AdminController
{
	/**
	 * @var string the default layout for the views.
	 */
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
				'actions'=>array('index', 'view', 'create', 'update', 'admin', 'delete'),
				'roles'=>array(
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER
				),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
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
		$model=new Portfolio('making');

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Portfolio']))
		{
			$model->attributes=$_POST['Portfolio'];
			//$model->author_id = Yii::app()->user->id;
			if ($model->service_id == 0)
				$model->service_id = null;
			if($model->save()) {

				$model->setScenario('create');
				/**
				 * Сохранение новых изображений с их описанием
				 */
				if(isset($_POST['Portfolio']['new'])) {
					foreach($_POST['Portfolio']['new'] as $file_key=>$desc){
						$model->setImageType('portfolio');
						$image = UploadedFile::loadImage($model, 'file_'.$file_key, $desc['filedesc'], true);
						if ($image) {
							$rel = new PortfolioUploadedfile();
							$rel->item_id = $model->id;
							$rel->file_id = $image->id;
							$rel->save();
						}
					}
				}


				if ($model->validate())
					$model->status = Portfolio::STATUS_MODERATING;

				if ($model->save())
					$this->redirect(array('view','id'=>$model->id));
			}
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
		$model->setScenario('create');

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Portfolio']))
		{
			$model->attributes=$_POST['Portfolio'];

			if($model->save())
			{
				/**
				 * Сохранение новых изображений с их описанием
				 */
				if(isset($_POST['Portfolio']['new'])) {
					foreach($_POST['Portfolio']['new'] as $file_key=>$desc){
						$model->setImageType('portfolio');
						$image = UploadedFile::loadImage($model, 'file_'.$file_key, $desc['filedesc'], true);
						if ($image) {
							$rel = new PortfolioUploadedfile();
							$rel->item_id = $model->id;
							$rel->file_id = $image->id;
							$rel->save();
						}
					}
				}

				/**
				 * Удаление изображений (проход по массиву id изображений для удаления)
				 */
				if(isset($_POST['Portfolio']['delete'])) {
					foreach($_POST['Portfolio']['delete'] as $file_id){
						$img = PortfolioUploadedfile::model()->findByAttributes(array('item_id'=>$model->id, 'file_id'=>(int)$file_id));
						if($img)
							$img->delete();
					}
				}

				/**
				 * Обновление описаний для уже созданых ранее изображений
				 */
				if(isset($_POST['Portfolio']['filedesc'])) {
					foreach($_POST['Portfolio']['filedesc'] as $file_id => $file_desc) {
						$img = PortfolioUploadedfile::model()->findByAttributes(array('item_id'=>$model->id, 'file_id'=>(int)$file_id));
						if(!$img)
							continue;

						$uf = UploadedFile::model()->findByPk((int)$file_id);
						$uf->desc = CHtml::encode($file_desc);
						$uf->save();
					}
				}

				$this->redirect(array('view','id'=>$model->id));
			}

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
		// we only allow deletion via POST request
		$model = $this->loadModel($id);

		$model->status = Portfolio::STATUS_DELETED;
		$model->save(false);

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model=new Portfolio('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Portfolio']))
			$model->attributes=$_GET['Portfolio'];

		if ($model->status == 0)
			$model->status = null;
		if ($model->service_id == 0)
			$model->service_id = null;

		$date_from = Yii::app()->request->getParam('date_from');
		$date_to = Yii::app()->request->getParam('date_to');

		$criteria=new CDbCriteria;

		$criteria->compare('t.author_id', $model->author_id);
		$criteria->compare('t.name', $model->name, true);
		if ($model->id)
			$criteria->compare('t.id', explode(',', $model->id), true);

		$criteria->compare('t.status', $model->status);
		$criteria->compare('t.status', '<>'.Portfolio::STATUS_DELETED);
		if ($date_from)
			$criteria->compare('t.create_time', '>='.(strtotime($date_from)));
		if ($date_to)
			$criteria->compare('t.create_time', '<='.(strtotime($date_to)+86400));

		$criteria->compare('t.service_id', $model->service_id);
		$criteria->order = 't.create_time DESC';

		if (empty($model->status))
			$model->status = 0;

		$dataProvider = new CActiveDataProvider('Portfolio', array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize'=>20,
				'pageVar' => 'page'
			),
		));

		$this->render('index',array(
			'model'		=> $model,
			'dataProvider'	=> $dataProvider,
			'date_from' 	=> $date_from,
			'date_to' 	=> $date_to
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Portfolio::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='portfolio-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

}
