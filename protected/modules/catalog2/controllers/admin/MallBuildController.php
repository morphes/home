<?php

class MallBuildController extends AdminController
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
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
			array('allow',
				'roles'=>array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_MODERATOR,
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
		$model=new MallBuild;

		$model->user_id = Yii::app()->user->id;

		/** @var $workTime WorkTime Инициализация объекта рабочего времени */
		$workTime = Yii::app()->workTime->setModel($model);

		if(isset($_POST['MallBuild']))
		{
			$model->attributes=$_POST['MallBuild'];
			$model->work_time = $workTime->setAttributes($_POST['MallBuild'])->getSerialize();

			if ($model->save()) {
				/**
				 * Сохранение логотипа
				 */
				$model->setImageType('logo');
				$file = UploadedFile::loadImage($model, 'logo', '', true);
				if($file) {
					$model->image_id = $file->id;
					$model->save(false, array('image_id'));
				}


				$this->redirect(array('view','id'=>$model->id));
			}

		}



		$this->render('create',array(
			'model'    => $model,
			'workTime' => $workTime
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		/** @var $model MallBuild */
		$model=$this->loadModel($id);

		/** @var $workTime WorkTime Инициализация объекта рабочего времени */
		$workTime = Yii::app()->workTime->setModel($model);

		if(isset($_POST['MallBuild']))
		{
			$model->attributes = $_POST['MallBuild'];
			$model->work_time = $workTime->setAttributes($_POST['MallBuild'])->getSerialize();

			if ($model->save()) {

				$model->saveServices($_POST['MallBuild']['servicesIds']);

				/**
				 * Сохранение логотипа
				 */
				$model->setImageType('logo');
				$file = UploadedFile::loadImage($model, 'logo', '', true);
				if($file) {
					$model->image_id = $file->id;
					$model->save(false, array('image_id'));
				}

				$this->redirect(array('view','id'=>$model->id));
			}

		}

		$this->render('update',array(
			'model'    => $model,
			'workTime' => $workTime
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
		$model=new MallBuild('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['MallBuild']))
			$model->attributes=$_GET['MallBuild'];

		$this->render('index',array(
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
		$model=MallBuild::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='mall-build-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	/**
	 * Удаление изображения магазина
	 * @param $store_id
	 * @throws CHttpException
	 */
	public function actionAjaxDeleteLogo()
	{
		if(!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(400);

		$build_id = Yii::app()->request->getParam('build_id');

		$mall = $this->loadModel($build_id);
		$mall->image_id = null;
		$mall->save();

		die(json_encode(array('success'=>true)));
	}

	/**
	 * Добавление этажа к ТЦ
	 * @param $bid Идентификатор ТЦ
	 * @throws CHttpException
	 */
	public function actionAjaxAppendFloor($bid)
	{
		$build = MallBuild::model()->findByPk((int)$bid);
		if ( ! $build)
			throw new CHttpException(400, 'Торговый центр с иднетификатором #'.(int)$bid.' не найден');

		$floor = new MallFloor();

		$floor->mall_build_id = $build->id;
		$floor->save(false);

		die(json_encode(array(
			'success' => true,
			'html'    => $this->renderPartial('_floorItem', array('floor' => $floor), true)
		)));
	}

	/**
	 * Удаление этажа из ТЦ
	 * @param $fid Идентификатор этажа
	 * @throws CHttpException
	 */
	public function actionAjaxDeleteFloor($fid)
	{
		$floor = MallFloor::model()->findByPk((int)$fid);

		if ( ! $floor)
			throw new CHttpException(404);

		$floor->delete();

		die(json_encode(array(
			'success' => true
		)));
	}


	/**
	 * Обновление названия этажа
	 * @param $fid Идентификатор этажа
	 * @throws CHttpException
	 */
	public function actionAjaxUpadeNameFloor($fid)
	{
		$success = true;
		$errorMsg = '';

		$floor = MallFloor::model()->findByPk((int)$fid);
		if ( ! $floor)
			throw new CHttpException(404);

		$name = Yii::app()->request->getParam('name');

		$floor->name = $name;
		if ( ! $floor->save()) {
			$success = false;
			$errorMsg = print_r($floor->getErrors(), true);
		}


		die(json_encode(array(
			'success' => $success,
			'errorMsg' => $errorMsg
		)));
	}

	public function actionAjaxAppendFloorImage($fid)
	{
		$success = true;
		$html = '';

		/**
		 * Загрузка файлов на fileApi
		 * @param tid - Tender id
		 */
		$floor = MallFloor::model()->findByPk((int)$fid);
		if ( ! $floor)
			throw new CHttpException(404);

		$floor->setImageType('image');
		$image = UploadedFile::loadImage($floor, 'file', '', true);
		if ($image) {
			$floor->image_id = $image->id;
			$floor->save(false);

			$html = $this->renderPartial('_floorImage', array('image' => $image), true);

		} else {
			$success = false;
		}



		die(json_encode(array(
			'success' => $success,
			'html'    => $html
		)));
	}

	public function actionAjaxGetFloorsById($id)
	{
		$success = true;
		$html = '';

		$build = MallBuild::model()->findByPk((int)$id);
		if (!$build)
			throw new CHttpException(404);

		$floors = MallFloor::model()->findAllByAttributes(array('mall_build_id' => $build->id));

		if ($floors){
			$html = CHtml::dropDownList('floor_id', '', CHtml::listData($floors, 'id', 'name'));
		} else {
			$success = false;
		}



		die(json_encode(array(
			'success' => $success,
			'html'    => $html
		)));
	}
}
