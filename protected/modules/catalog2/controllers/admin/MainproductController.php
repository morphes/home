<?php

class MainproductController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
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
				),
			),
			array('deny',  // deny all users
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
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new MainUnit('product');

		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$mainRooms = MainRoom::model()->findAllByAttributes(array('status'=>MainRoom::STATUS_ENABLED), array('order'=>'position ASC'));

		$sCategory = $request->getParam('MainUnitCategory', array());
		if (isset( $sCategory[1] ))
			unset($sCategory[1]); // Remove root
		$sRooms = $request->getParam('MainUnitRoom', array());

		if (isset($_POST['MainUnit'])) {
			$model->attributes = $_POST['MainUnit'];
			$model->type_id = MainUnit::TYPE_PRODUCT;

			$dateTo = @strtotime( $request->getParam('date_to') );
			$dateFrom = @strtotime( $request->getParam('date_from') );

			$model->start_time = ($dateTo !== -1) ? $dateTo : 0;
			$model->end_time = ($dateFrom !== -1) ? $dateFrom : 0;
			$imgData = $request->getParam('img', array());

			/** @var $origin Product */
			$origin = $model->getOrigin();
			if ( $model->validate() && !is_null($origin) && $file = $this->cropImage($imgData, $model) ) {
				$model->file_id = $file->id;
				$model->save(false);
				$model->position = $model->id;
				$model->save(false);

				MainUnitCategory::updateCategories($sCategory, $model->id);
				MainUnitRoom::updateRooms($sRooms, $model->id);

				$this->redirect(array('index'));
			}
		}

		$this->render('create', array(
			'model' => $model,
			'mainRooms'=>$mainRooms,
			'sCategory'=>$sCategory,
			'sRooms'=>$sRooms,
		));
	}

	/**
	 * @param $imgData
	 * @param $model MainUnit
	 * @return UploadedFile | false
	 */
	private function cropImage($imgData, &$model)
	{
		$jpegQuality = 90;

		$origin = $model->getOrigin();

		$x = ( isset($imgData['x']) && $imgData['x']>0 && $imgData['x']<1 ) ? $imgData['x']:0;
		$y = ( isset($imgData['y']) && $imgData['y']>0 && $imgData['y']<1 ) ? $imgData['y']:0;

		$w = ( isset($imgData['w']) && $imgData['w']>0 && ($imgData['w']+$x)<=1 ) ? $imgData['w']:1-$x;
		$h = ( isset($imgData['h']) && $imgData['h']>0 && ($imgData['h']+$y)<=1 ) ? $imgData['h']:1-$y;

		if ( empty( $imgData['photo'] )) {
			$model->addError('photo', 'Фото не указано');
			return false;
		}

		$photoId = intval($imgData['photo']);

		$images = $origin->getImages() ;
		$images[] = $origin->image_id;
		if (!in_array($photoId, $images)) {
			$model->addError('photo', 'Фото отсутствует у товара');
			return false;
		}

		/** @var $photo UploadedFile */
		$photo = UploadedFile::model()->findByPk($photoId);
		if (is_null($photo)) {
			$model->addError('photo', 'Фото не найдено');
			return false;
		}

		$fileName = UploadedFile::UPLOAD_PATH .'/'.$photo->path.'/'.$photo->name.'.'.$photo->ext;

		if (!file_exists($fileName)) {
			$model->addError('photo', 'Оригинал фото не найден');
			return false;
		}

		$imageHandler = new imageHandler($fileName, imageHandler::FORMAT_JPEG);
		$imageHandler->jCrop2($x, $y, $w, $h, $jpegQuality);

		$file = new UploadedFile();
		$file->author_id = Yii::app()->getUser()->getId();
		$file->path = 'unit/mainunit/product/'.intval($origin->id % 100);
		$file->name = time().rand(0, 99);
		$file->ext = 'jpg';
		$file->type = UploadedFile::IMAGE_TYPE;
		$file->size = $imageHandler->getImageSize();

		$file->save(false);

		$folder = UploadedFile::UPLOAD_PATH . '/'.$file->path;

		if ( !file_exists($folder))
			mkdir($folder, 0700, true);

		$imageHandler->saveImage($folder. '/' .$file->name. '.' .$file->ext);
		// TODO: Добавить нарезку ресайзов
		//$file->generatePreview(  );


		return $file;
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$id = intval($id);
		/** @var $model MainUnit */
		$model = MainUnit::model()->findByPk($id, 'type_id=:tid', array(':tid'=>MainUnit::TYPE_PRODUCT));

		if (is_null($model)) // Не блокируем удаленные
			throw new CHttpException(404);

		$model->setScenario('product');

		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$mainRooms = MainRoom::model()->findAllByAttributes(array('status'=>MainRoom::STATUS_ENABLED), array('order'=>'position ASC'));

		$sCategory = $request->getParam('MainUnitCategory', array());
		if (isset( $sCategory[1] ))
			unset($sCategory[1]); // Remove root
		$sRooms = $request->getParam('MainUnitRoom', array());

		if (isset($_POST['MainUnit'])) {
			$model->attributes = $_POST['MainUnit'];
			$model->type_id = MainUnit::TYPE_PRODUCT;

			$dateTo = @strtotime( $request->getParam('date_to') );
			$dateFrom = @strtotime( $request->getParam('date_from') );

			$model->start_time = ($dateTo !== -1) ? $dateTo : 0;
			$model->end_time = ($dateFrom !== -1) ? $dateFrom : 0;

			/** @var $origin Product */
			$origin = $model->getOrigin();
			if ( $model->validate() && !is_null($origin) ) {
				// image processing
				$imgData = $request->getParam('img', array());
				$file = $this->cropImage($imgData, $model);
				if ($file)
					$model->file_id = $file->id;

				$model->save(false);

				MainUnitCategory::updateCategories($sCategory, $model->id);
				MainUnitRoom::updateRooms($sRooms, $model->id);

				$this->redirect(array('index'));
			}
		}

		$photo = $model->getImage();
		$sql = 'SELECT s.name as name, s.id as id, s.address, city.name as city_name FROM cat_store as s '
			.'INNER JOIN cat_store_price as csp ON csp.store_id=s.id '
			.'INNER JOIN city ON city.id=s.city_id '
			.'WHERE csp.product_id=:pid';
		$pid = $model->origin_id;
		$data = Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':pid', $pid)->queryAll();
		$stores = array();
		foreach ($data as $row) {
			$stores[ $row['id'] ] = $row['name'].' ('.$row['city_name'].', '.$row['address'].')';
		}

		if (!$request->getIsPostRequest()) {
			$sCategory = MainUnitCategory::getSelectedCategories($model->id);
			$sRooms = MainUnitRoom::getSelectedRooms($model->id);
		}

		$this->render('update', array(
			'model' => $model,
			'mainRooms'=>$mainRooms,
			'sCategory'=>$sCategory,
			'sRooms'=>$sRooms,
			'photo'=>$photo,
			'stores'=>$stores,
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

		$model = MainUnit::model()->findByPk($id);
		if (is_null($model))
			throw new CHttpException(404);

		$model->status = MainUnit::STATUS_DELETED;
		$model->save(false);
		die ( CJSON::encode( array('success'=>true) ) );

	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model=new MainUnit('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['MainUnit']))
			$model->attributes=$_GET['MainUnit'];

		$this->render('index',array(
			'model'=>$model,
		));
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

		/** @var $current MainUnit */
		$current = MainUnit::model()->findByPk($id);

		if (is_null($current) || $current->type_id != MainUnit::TYPE_PRODUCT)
			die ( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'status IN (:st1, :st2) AND position<:position AND type_id=:tid';
		$criteria->order = 'position DESC';
		$criteria->params = array(
			':position' => $current->position,
			':st1'=>MainUnit::STATUS_ENABLED,
			':st2'=>MainUnit::STATUS_DISABLED,
			':tid'=>MainUnit::TYPE_PRODUCT,
		);

		/** @var $next MainUnit */
		$next = MainUnit::model()->find($criteria);
		if (is_null($next) || $next->type_id != MainUnit::TYPE_PRODUCT)
			die ( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$tmp = $current->position;
		$current->position = $next->position;
		$next->position = $tmp;
		$current->save(false);
		$next->save(false);
		die ( json_encode( array('success'=>true), JSON_NUMERIC_CHECK ) );
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

		/** @var $current MainUnit */
		$current = MainUnit::model()->findByPk($id);

		if (is_null($current) || $current->type_id != MainUnit::TYPE_PRODUCT)
			die ( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'status IN (:st1, :st2) AND position>:position AND type_id=:tid';
		$criteria->order = 'position ASC';
		$criteria->params = array(
			':position' => $current->position,
			':st1'=>MainRoom::STATUS_ENABLED,
			':st2'=>MainRoom::STATUS_DISABLED,
			':tid'=>MainUnit::TYPE_PRODUCT,
		);

		/** @var $next MainUnit */
		$next = MainUnit::model()->find($criteria);
		if (is_null($next) || $next->type_id != MainUnit::TYPE_PRODUCT)
			die ( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$tmp = $current->position;
		$current->position = $next->position;
		$next->position = $tmp;
		$current->save(false);
		$next->save(false);
		die ( json_encode( array('success'=>true), JSON_NUMERIC_CHECK ) );
	}

	public function actionAxGetContent()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('product_id') );
		if ( !$request->getIsAjaxRequest() || empty($id) )
			throw new CHttpException(404);

		/** @var $product Product */
		$product = Product::model()->findByPk($id);
		if ( $product===null )
			throw new CHttpException(404);

		// Дополнительная фильтрация магазинов по городу.
		$cityId = (int)$request->getParam('city_id');

		$images = $product->getImages(true, true);

		$sql = 'SELECT s.name as name, s.id as id, s.address, city.name as city_name FROM cat_store as s '
			.'INNER JOIN cat_store_price as csp ON csp.store_id=s.id '
			.'INNER JOIN city ON city.id=s.city_id '
			.'WHERE csp.product_id=:pid';
		if ($cityId > 0) {
			$sql .= ' AND s.city_id = ' . $cityId;
		}

		$pid = $product->id;
		$data = Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':pid', $pid)->queryAll();
		$stores = array();
		foreach ($data as $row) {
			$stores[ $row['id'] ] = $row['name'].' ('.$row['city_name'].', '.$row['address'].')';
		}

		$html = $this->renderPartial('_item', array(
			'product' => $product,
			'images' => $images,
			'stores' => $stores,
		), true);

		die ( json_encode( array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK ) );
	}

	/**
	 * @brief Обрабатывает запрос на смену статуса из контекстного меню
	 * @return JSON
	 */
	public function actionAxStatusUpdate()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );
		$status = intval( $request->getParam('status') );
		if ( !$request->getIsAjaxRequest() || empty($id) || !isset(MainUnit::$statusNames[$status]))
			throw new CHttpException(404);

		/** @var $current MainUnit */
		$current = MainUnit::model()->findByPk($id);
		if ($current === null)
			throw new CHttpException(404);

		$current->status = $status;
		$current->save(false);

		die ( json_encode( array('success'=>true), JSON_NUMERIC_CHECK ) );
	}

	/**
	 * @brief Возвращает выпадающий список для смены статуса пользователя
	 * @param integer $uid user id
	 */
	public function actionAxStatusList()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );
		if ( !$request->getIsAjaxRequest() || empty($id) )
			throw new CHttpException(404);

		/** @var $current MainUnit */
		$current = MainUnit::model()->findByPk($id);
		if ($current === null)
			throw new CHttpException(404);

		$statusList = '';
		foreach (MainUnit::$statusNames as $key => $status) {
			if ($current->status == $key) {
				$statusList.= CHtml::tag('li', array(
					'data-id'=>$current->id,
					'status-id'=>$current->status,
					'class'=>'current-status',
				), $status);
			} else {
				$statusList.= CHtml::tag('li', array('data-id'=>$current->id, 'status-id'=>$key), $status);
			}
		}
		die ( json_encode( array('success'=>true, 'html'=>$statusList), JSON_NUMERIC_CHECK ) );
	}
}
