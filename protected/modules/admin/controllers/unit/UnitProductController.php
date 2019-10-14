<?php

class UnitProductController extends AdminController
{

	public $layout = 'webroot.themes.myhome.views.layouts.backend';

	public function init()
	{
		Yii::import('application.modules.catalog.models.Product');
		Yii::import('application.modules.catalog.models.Category');

		parent::init();
	}

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
				'roles' => array(
					User::ROLE_ADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_JOURNALIST,
				),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new UnitProduct;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['UnitProduct']))
		{
			$model->attributes=$_POST['UnitProduct'];

			$this->cropImage($model);

			if($model->save())
				$this->redirect(array('index'));
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

		if(isset($_POST['UnitProduct']))
		{
			$model->attributes=$_POST['UnitProduct'];

			$this->cropImage($model);

			if($model->save())
				$this->redirect(array('index'));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	private function cropImage($model)
	{
		$targ_w = 416;
		$targ_h = 344;
		$jpeg_quality = 90;
		// Коэффициент множителя согласно пропорции,
		// с которой мы уменьшили выводимую оригинальную фотографию
		if ( empty($_POST['percent']) || empty($_POST['x']) || empty($_POST['y']) || empty($_POST['w']) || empty($_POST['h']) )
			return false;

		$f = 100 / $_POST['percent'];

		// Параметры, применяемые к картинке-источнику
		$src_offset_x = round($_POST['x'] * $f);
		$src_offset_y = round($_POST['y'] * $f);
		$src_width = round($_POST['w'] * $f);
		$src_height = round($_POST['h'] * $f);


		$product = Product::model()->findByPk((int)$model->product_id);;
		$filePath = $product->cover->getPreviewName(Product::$preview['resize_960']);

		// Полный путь до картники-оригинала
		$fullPath = Yii::getPathOfAlias('webroot').'/'.$filePath;

		$imageHandler = new imageHandler($fullPath, imageHandler::FORMAT_JPEG);

		$imageHandler->jCrop($src_width, $src_height, $src_offset_x, $src_offset_y, $targ_w, $targ_h, $jpeg_quality);


		$newFileName = time().rand(0, 99);

		if ( ! file_exists(UnitProduct::UPLOAD_IMAGE_DIR))
			mkdir(UnitProduct::UPLOAD_IMAGE_DIR, 0755, true);

		$imageHandler->saveImage(UnitProduct::UPLOAD_IMAGE_DIR.$newFileName.'.jpg');


		$model->image_path = UnitProduct::UPLOAD_IMAGE_DIR.$newFileName.'.jpg';
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
		$model=new UnitProduct('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['UnitProduct']))
			$model->attributes=$_GET['UnitProduct'];

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
		$model=UnitProduct::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='unit-product-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	/**
	 * Возвращает данные по товару в виде JSON
	 * @param $id Идентификатор товара
	 */
	public function actionAjaxGetInfo($id)
	{
		$success = true;
		$errorMsg = '';
		$productArray = array();

		$product = Product::model()->findByPk((int)$id);

		if ( ! $product)
		{
			$success = false;
			$errorMsg = 'Товар не найден';
			goto the_end;
		}
		else
		{
			$filePath = $product->cover->getPreviewName(Product::$preview['resize_960']);
			$size = getimagesize(Yii::getPathOfAlias('webroot').'/'.$filePath);
			$productArray['image'] = '/'.$filePath;
			$productArray['dimensions'] = $size;
			$productArray['name'] = $product->name;
			$productArray['category'] = $product->category->name;
		}



		the_end:
		die(json_encode(array(
			'success' => $success,
			'errorMsg' => $errorMsg,
			'product' => $productArray
		)));
	}
}
