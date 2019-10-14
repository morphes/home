<?php

class InteriorController extends AdminController
{

        public $layout = 'webroot.themes.myhome.views.layouts.backend';
        
        
        public function filters() {
		return array('accessControl');
	}

	public function accessRules() {

		return array(
			array('allow',
				'actions' => array('index', 'list', 'view', 'bindProducts', 'AjaxSearchProduct', 'AjaxBindProduct', 'AjaxUnbindProduct', 'AjaxUpdateProduct'),
				'roles'=>array(
					User::ROLE_ADMIN,
					User::ROLE_JUNIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_FREELANCE_IDEA,
					User::ROLE_JOURNALIST,
				),
			),
			array('allow',
				'actions' => array('migrate'),
				'roles'=>array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
				),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}
        
	public function actionIndex()
	{
		$this->render('index');
	}

	/**
	 * Show all of interiors
	 * @param type $page
	 */
	public function actionList()
	{
		$model = new Interior('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Interior']))
			$model->attributes=$_GET['Interior'];
		
		if ($model->status == 0)
			$model->status = null;
		
		$date_from = Yii::app()->request->getParam('date_from');
		$date_to = Yii::app()->request->getParam('date_to');

		// Дополнительные поля фильтра для поиска идей с привязанными товарамми
                $is_bind = (int)Yii::app()->request->getParam('is_bind');
		$prod_id = Yii::app()->request->getParam('prod_id');

		// Получаем список помещений для фильрации
		$roomsModels = IdeaHeap::getRooms(Config::INTERIOR);
		$rooms = array();
		foreach ($roomsModels as $id=>$item) {
			$rooms[$id] = $item->option_value;
		}

		$room_id = Yii::app()->request->getParam('room_id');

		$criteria=new CDbCriteria;
		$criteria->compare('t.author_id', $model->author_id);
		$criteria->compare('t.name', $model->name, true);
		if ($model->id)
			$criteria->compare('t.id', explode(',', $model->id), true);

                $criteria->compare('t.status', $model->status);
		$criteria->compare('t.status', '<>'.Interior::STATUS_DELETED);
		if ($date_from)
			$criteria->compare('t.create_time', '>='.(strtotime($date_from)));
		if ($date_to)
			$criteria->compare('t.create_time', '<='.(strtotime($date_to)+86400));

		if ($room_id) {
			$criteria->join = 'INNER JOIN interior_content ic ON ic.interior_id = t.id';
			$criteria->addCondition('ic.room_id = :rid');
			$criteria->params[':rid'] = $room_id;
		}

		
		$criteria->order = 't.create_time DESC';
		
		if (empty($model->status))
			$model->status = 0;

		if ($is_bind) {
			Yii::import('application.modules.catalog.models.ProductOnPhotos');
			$criteria->join = "LEFT JOIN ".ProductOnPhotos::model()->tableName()." as ph ON t.id = ph.model_id";
			$criteria->addCondition("ph.model = 'Interior'");
			$criteria->select = 't.*';
			$criteria->distinct = true;

			if ($prod_id)
				$criteria->compare('ph.product_id', explode(',', $prod_id), true);


		}

		
		$dataProvider = new CActiveDataProvider('Interior', array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize'=>20,
			),
		));
		// new ROLE
		if (in_array(Yii::app()->user->role, array(User::ROLE_FREELANCEEDITOR) )) {
			return $this->render('freelanceInteriorList', array(
				'model'		=> $model,
				'dataProvider'	=> $dataProvider,
				'date_from'	=> $date_from,
				'date_to'	=> $date_to
			));
		}



                $this->render('interior_list', array(
			'model'        => $model,
			'dataProvider' => $dataProvider,
			'date_from'    => $date_from,
			'date_to'      => $date_to,
			'is_bind'      => $is_bind,
			'prod_id'      => $prod_id,
			'rooms'        => $rooms,
			'room_id'      => $room_id,
                ));
	}
	
	/**
	 * Interior detail page 
	 * @param integer $interior_id 
	 */
	public function actionView($interior_id = NULL, $interior_content_id = NULL)
	{
		// Check existing interior
		$interior = Interior::model()->findByPk((int) $interior_id);
		$interiorContent = InteriorContent::model()->findByPk((int) $interior_content_id, 'interior_id=:interior_id', array(':interior_id'=>(int)$interior_id));
		
		if ($interior && $interior->status == Interior::STATUS_DELETED) {
			$this->render('deleted', array('interior' => $interior));
			Yii::app()->end();
		}
		
		
		if ($interior AND ! $interiorContent AND ! $interior_content_id)
		{			
			if ( ! is_null($interior_id)) {
				$interior = Interior::model()->findByPk((int) $interior_id);
				
				if ($interior) {
					
					$interiorContents = InteriorContent::model()->findAll('interior_id = :sid', array(
					    ':sid' => $interior->id,
					));
					
					$errors = array();
					$coauthorErrors = array();

					$arrKeys = array_keys(Config::$ideaTypes, 'Interior');
					$objects = IdeaHeap::model()->findAll('idea_type_id = :idea AND option_key = :key', array(':idea' => reset($arrKeys),
					    ':key' => 'object')
					);

					/**
					 * Отрисовка формы для продолжения заполнения Interior и InteriorContent
					 */
					$interiorImage = UploadedFile::model()->findByPk($interior->image_id);
					$filesId = LayoutUploadedFile::model()->findAllByAttributes(array('item_id' => $interior->id, 'idea_type_id' => Config::INTERIOR));

					$layouts = array();
					foreach ($filesId as $item) {
						$layouts[] = UploadedFile::model()->findByPk($item->uploaded_file_id);
					}
					$coauthors = Coauthor::model()->findAllByAttributes(array('idea_id' => $interior->id, 'idea_type_id' => Config::INTERIOR));

					return $this->render('view', array(
						'interior'	=> $interior,
						'interiorContents' => $interior,
						'interiorImage' => $interiorImage,
						'errors'	=> $errors,
						'objects'	=> CHtml::listData($objects, 'id', 'option_value'),
						'layouts'	=> $layouts,
						'coauthors'	=> $coauthors,
						'journal'=> InteriorJournal::getJournal($interior),
					));
				}
			}


			throw new CHttpException(404);
			
		}
		elseif ($interior AND $interiorContent)
		{
			// Получаем список комнат
			$interiorRooms = InteriorContent::model()->findAll('interior_id=:interior_id AND room_id is NOT NULL', array('interior_id' => $interior->id));
			
			// Получаем все картинки для помещения идеи (картинки, цвета)
			$images = interiorContent::getInteriorContentImagesById($interiorContent->id);
			
			// Дополнительные цвета нашего помещения
			$additionalColors = IdeaAdditionalColor::model()->findAll('item_id=:id AND color_id IS NOT NULL AND idea_type_id=:typeId', array(':id' => $interior_content_id, ':typeId' => Config::INTERIOR));
			
			// Список доступных цветов
			$colors = IdeaHeap::getColors(Config::INTERIOR, $interior->object_id);
			// Список названий комнат
			$rooms = IdeaHeap::getRooms(Config::INTERIOR, $interior->object_id);
			// Список стилей
			$styles = IdeaHeap::getStyles(Config::INTERIOR, $interior->object_id);
			
			$this->render('view_item', array(
			    'interior'		=> $interior,
			    'interiorRooms'	=> $interiorRooms,
			    'interiorContent'	=> $interiorContent,
			    'images'		=> $images,
			    'additionalColors'	=> $additionalColors,
			    'rooms'		=> $rooms,
			    'colors'		=> $colors,
			    'styles'		=> $styles
			));
		}
		else
		{
			throw new CHttpException(404);
		}
		
	}

	public function actionMigrate($interior_id = null)
	{
		$interior = Interior::model()->findByPk((int) $interior_id);
		if (is_null($interior))
			throw new CHttpException(404);

		$result = $interior->migrateToArchitecture();
		if ($result) {
			$interior->status = Interior::STATUS_DELETED;
			$interior->save(false);
			$this->redirect( $this->createUrl('/idea/admin/architecture/update/', array('id'=>$result)) );
		}
		$this->redirect( $this->createUrl('/idea/admin/create/interior/', array('id'=>$interior->id)) );
	}

	/**
	 * Экшен для привязки товаров к фотографии помещения.
	 */
	public function actionBindProducts($file_id, $model, $model_id)
	{
		Yii::import('application.modules.catalog.models.*');

		// Простой лэйаут с подключенным bootstrap'ом
		$this->layout = '//layouts/simpleBootstrap';

		$uFile = UploadedFile::model()->findByPk((int)$file_id);
		$interior = Interior::model()->findByPk((int)$model_id);

		if ( ! $uFile || ! $interior)
			throw new CHttpException(404, 'Неверные данные');


		// Получаем список привязанных товаров к фото.
		$products = ProductOnPhotos::model()->findAllByAttributes(array('ufile_id' => $uFile->id));


		$this->render('bindProducts', array(
			'interior' => $interior,
			'uFile' => $uFile,
			'products' => $products
		));
	}

	/**
	 * Проверяет наличие товара по его $id.
	 * Если товар найден, то возвращаются данные по нему
	 *
	 * @param $id Идентификатор товара
	 */
	public function actionAjaxSearchProduct($id)
	{
		Yii::import('application.modules.catalog.models.*');

		$success = true;
		$errorMsg = '';

		// Данные по товару
		$productArr = array();

		$product = Product::model()->findByPk((int)$id);

		if ( ! $product) {
			$success = false;
			$errorMsg = 'Товар с указанным ID не найден';
			goto the_end;
		}


		$productArr['id'] = $product->id;
		$productArr['image'] = '/'.$product->cover->getPreviewName(Product::$preview['crop_200']);
		$productArr['name'] = $product->name;
		$productArr['category'] = $product->category->name;
		$productArr['vendor'] = $product->vendor->name;
		$productArr['type'] = ProductOnPhotos::TYPE_SIMILAR;

		the_end:
		die(json_encode(array(
			'success'  => $success,
			'errorMsg' => $errorMsg,
			'product' => $productArr
		)));
	}

	/**
	 * Привязывает товар к фотографии
	 */
	public function actionAjaxBindProduct()
	{
		Yii::import('application.modules.catalog.models.Product');
		Yii::import('application.modules.catalog.models.ProductOnPhotos');

		$success = true;
		$errorMsg = '';
		$htmlRow = '';

		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();


		$file_id = intval($request->getParam('file_id'));
		$product_id = intval($request->getParam('product_id'));
		$params = $request->getParam('params', array());
		$model_id = intval($request->getParam('model_id'));
		$model = $request->getParam('model');
		$type = intval($request->getParam('type', ProductOnPhotos::TYPE_SIMILAR));


		$uFile = UploadedFile::model()->findByPk($file_id);
		$product = Product::model()->findByPk($product_id);
		$interior = Interior::model()->findByPk($model_id);

		if ( ! $product || ! $uFile || $model != 'Interior' || ! $interior || !isset(ProductOnPhotos::$typeNames[$type]) ) {
			$success = false;
			$errorMsg = 'Неверные данные для привязки';
			goto the_end;
		}

		if (ProductOnPhotos::model()->exists('ufile_id = :fid AND product_id = :pid', array(':fid' => $uFile->id, ':pid' => $product->id))) {
			$success = false;
			$errorMsg = 'Такой товар уже добавлен к фото';
			goto the_end;
		}

		$model = new ProductOnPhotos();
		$model->setAttributes(array(
			'ufile_id'    => $uFile->id,
			'product_id'  => $product->id,
			'model'       => 'Interior',
			'model_id'    => $interior->id,
			'type' => $type,
			'params'      => serialize($params),
			'create_time' => time(),
			'update_time' => time()
		));
		if ( ! $model->save()) {
			$success = false;
			$errorMsg = serialize($model->getErrors());
			goto the_end;
		}

		// Получаем код строки новодобавленного товара
		$htmlRow = $this->renderPartial('bindProdItemList', array('product' => $model->product), true);


		the_end:
		die(json_encode(array(
			'success'  => $success,
			'errorMsg' => $errorMsg,
			'htmlRow'  => $htmlRow
		)));

	}

	/**
	 * Привязывает товар к фотографии
	 */
	public function actionAjaxUpdateProduct()
	{
		Yii::import('application.modules.catalog.models.Product');
		Yii::import('application.modules.catalog.models.ProductOnPhotos');

		$success = true;
		$errorMsg = '';

		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();

		$file_id = intval($request->getParam('file_id'));
		$product_id = intval($request->getParam('product_id'));
		$params = $request->getParam('params', array());
		$type = intval($request->getParam('type', ProductOnPhotos::TYPE_SIMILAR));

		$uFile = UploadedFile::model()->findByPk($file_id);
		$product = Product::model()->findByPk($product_id);

		if ( ! $product || ! $uFile || !isset(ProductOnPhotos::$typeNames[$type])) {
			$success = false;
			$errorMsg = 'Неверные данные для Обновления';
			goto the_end;
		}

		try {
			/** @var $model ProductOnPhotos */
			$model = ProductOnPhotos::model()->findByAttributes(array(
				'ufile_id'    => $uFile->id,
				'product_id'  => $product->id,
			));
			$model->params = serialize($params);
			$model->type = $type;
			$model->update_time = time();
			$model->save();

		} catch(Exception $e) {
			$success = false;
			$errorMsg = $e->getMessage();
			goto the_end;
		}



		the_end:
		die(json_encode(array(
			'success'  => $success,
			'errorMsg' => $errorMsg
		)));

	}

	/**
	 * Отвязывает товар от фото
	 */
	public function actionAjaxUnbindProduct()
	{
		Yii::import('application.modules.catalog.models.Product');
		Yii::import('application.modules.catalog.models.ProductOnPhotos');

		$success = true;
		$errorMsg = '';


		$file_id = (int)Yii::app()->request->getParam('file_id');
		$product_id = (int)Yii::app()->request->getParam('product_id');

		$uFile = UploadedFile::model()->findByPk($file_id);
		$product = Product::model()->findByPk($product_id);

		if ( ! $product || ! $uFile) {
			$success = false;
			$errorMsg = 'Неверные данные для отвязки товара';
			goto the_end;
		}


		$model = ProductOnPhotos::model()->findByAttributes(array(
			'ufile_id'    => $uFile->id,
			'product_id'  => $product->id,
		));

		if ($model) {
			$model->delete();
		} else {
			$success = false;
			$errorMsg = 'Связанный с фото товар не найден';
			goto the_end;
		}


		the_end:
		die(json_encode(array(
			'success'  => $success,
			'errorMsg' => $errorMsg
		)));
	}
}