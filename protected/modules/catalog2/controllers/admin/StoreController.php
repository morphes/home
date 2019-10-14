<?php

class StoreController extends AdminController
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
					User::ROLE_SALEMANAGER,
					User::ROLE_FREELANCE_STORE,
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
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'update' page.
	 */
	public function actionCreate()
	{
		$model=new Store();
		$model->user_id = Yii::app()->user->id;
		$model->status = Store::STATUS_ACTIVE;
		$model->type = Store::TYPE_OFFLINE; // тип по умолчанию
		$model->save(false);

		$this->redirect($this->createUrl('update', array('id'=>$model->id)));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 * @param integer $type Новый тип магазина (используется только для смены типа)
	 */
	public function actionUpdate($id, $type=null)
	{

		/** @var $model Store */
		$model = $this->loadModel($id);

		if (!$model->subdomain_id) {
			$model->subdomain = new Subdomain();
		}
        Yii::import('application.components.XmlParser.*');

		$storeGeo = StoreGeo::model()->findByAttributes(array(
			'store_id' => $model->id
		));
		if (!$storeGeo) {
			$storeGeo = new StoreGeo();
		}

		$changeTypeUrl = $this->createUrl('update', array('id'=>$model->id));

		// Фикс для битых магазинов, ставится тип offline
		if (empty($model->type)) {
			$model->type = Store::TYPE_OFFLINE;
		}

		// установка сценария валидации, соответствующего типу магазина
		if ( $model->type == Store::TYPE_OFFLINE )
			$model->setScenario('offline');
		if ( $model->type == Store::TYPE_ONLINE )
			$model->setScenario('online');

		// Если пришел запрос на смену типа магазина, то меняется тип магазина,
		// сохраняется в базу и открывается форма редактирования магазина нового типа
		if ( $type && in_array($type, array_keys(Store::$types)) ) {
			$model->type = (int) $type;
			$model->save(false, array('type'));
			$this->redirect($changeTypeUrl);
		}

		if (isset($_POST['Store'])) {
			$model->attributes = $_POST['Store'];
			$model->setTimeFromForm();
			$model->geocode = CHtml::encode(
				YandexMap::getGeocode(
					'г.'
					. (isset($model->city->name) ? $model->city->name : '')
					. ', '
					. $model->address
				)
			);

			if (!$model->user_id) {
				$model->user_id = Yii::app()->user->id;
			}


			if ( $model->type == Store::TYPE_ONLINE )
				$model->mall_build_id = null;

			if ( !$model->mall_build_id )
				$model->floor_id = $model->sect_name = null;

			if ( $model->type == Store::TYPE_OFFLINE )
				$model->geocode = CHtml::encode(YandexMap::getGeocode('г.'.(isset($model->city->name) ? $model->city->name : '').', '.$model->address));

			// Валидируем магазин
			$storeValid = $model->validate();
			// По-умолчанию валидация на домен прошла
			$domainValid = true;

			// Если тариф магазина «Минисат», то включаем проверку доменов.
			if ($model->tariff_id == Store::TARIF_MINI_SITE) {

				$model->subdomain->setAttributes($_POST['Subdomain']);
				$model->subdomain->model = get_class($model);
				$model->subdomain->model_id = $model->id;

				$domainValid = $model->subdomain->validate();
			}

			// Город
			if (isset($_POST['StoreGeo'])) {
				$storeGeo->attributes = $_POST['StoreGeo'];
				$storeGeo->store_id = $model->id;
				$storeGeo->type = StoreGeo::TYPE_CITY;

				$storeGeo->_resetStoreCity();
			}

			if ($model->type == Store::TYPE_OFFLINE) {
				$storeGeoValid = $storeGeo->validate();
			} else {
				$storeGeoValid = true;
			}

			if (!$storeGeoValid) {
				$model->addError('city_id', 'Необходимо заполнить поле город');
			}



			if ($storeValid && $storeGeoValid && $domainValid) {

				// Если все без ошибок, сохраняем поддомен в таблице поддоменов.
				if ($model->tariff_id == Store::TARIF_MINI_SITE) {
					$model->subdomain->save(false);

					// В таблицу Магазинов, сохраняем ID поддомена.
					$model->subdomain_id = $model->subdomain->id;
				} else {

					if (!$model->subdomain->isNewRecord) {
						$model->subdomain->delete();
					}

					// Если тариф не МиниСайт
					$model->subdomain_id = 0;
				}


				$model->save(false);
				$storeGeo->save(false);



				/**
				 * Сохранение логотипа
				 */
				$model->setImageType('logo');
				$file = UploadedFile::loadImage($model, 'logo', '', true);
				if ($file) {
					$model->image_id = $file->id;
					$model->save(false, array('image_id'));
				}



				$this->redirect(array('index'));
			}
		}

		$city = City::model()->findByPk((int)$storeGeo->geo_id);

		$this->render('update',array(
			'model'         => $model,
			'changeTypeUrl' => $changeTypeUrl,
			'city'          => $city
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
//		StorePrice::model()->deleteAllByAttributes(array('store_id'=>$model->id));
		$model->status = Store::STATUS_DISABLED;
		$model->save(false);
//		$model->delete();
//		$model->deleteSphinx();

                // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
                if(!isset($_GET['ajax']))
                        $this->redirect($this->createUrl('index'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{

        $model = new Store('search');
        $model->unsetAttributes();
        $category_id = 0;

        $date_from = Yii::app()->request->getParam('date_from');
        $date_to = Yii::app()->request->getParam('date_to');

        $criteria = new CDbCriteria();
        $criteria->order = 'create_time DESC';
        $criteria->compare('status', Store::STATUS_ACTIVE);

        if (isset($_GET['Store'])) {
            $model->attributes = $_GET['Store'];

            if ($_GET['Store']['category']) {
                $sql = "SELECT id FROM {{store2}} WHERE category_ids = :category_id LIMIT 10000";
                $category_id = intval($_GET['Store']['category']);
                $store_ids = Yii::app()->sphinx->createCommand($sql)->bindParam(':category_id', $category_id)->queryColumn();
                if(!$store_ids) {
                    $store_ids = array(0);
                }
                $criteria->addCondition('t.id IN ('.implode(",", $store_ids).")");
            }


            if ($model->id)
                $criteria->compare('t.id', explode(',', $model->id), true);
            if ($model->name)
                $criteria->compare('t.name', $model->name, true);
            if ($model->type)
                $criteria->compare('t.type', $model->type);
            if ($model->site)
                $criteria->compare('t.site', $model->site, true);

            if ($model->city_id) {
				$criteria->join = 'INNER JOIN cat_store_city as c ON c.store_id=t.id';
				$criteria->addCondition('c.city_id=:cid');
				$criteria->params[':cid'] = $model->city_id;
			}
			if ($model->address)
				$criteria->compare('t.address', $model->address, true);
                        if($model->user_id)
                                $criteria->compare('t.user_id', $model->user_id);
			if ($model->contractor_id)
				$criteria->compare('t.contractor_id', $model->contractor_id);
                        if ($date_from)
                                $criteria->compare('t.create_time', '>='.(strtotime($date_from)));
                        if ($date_to)
                                $criteria->compare('t.create_time', '<='.(strtotime($date_to)+86400));
			if ($model->mall_build_id)
                        	$criteria->compare('mall_build_id', $model->mall_build_id);
			if ($model->tariff_id)
				$criteria->compare('tariff_id', $model->tariff_id);
                }

                $dataProvider=new CActiveDataProvider('Store', array(
                        'criteria' => $criteria,
                        'pagination'=>array(
                                'pageSize'=>20,
                        ),
                ));

                $this->render('index',array(
                        'dataProvider'=>$dataProvider,
                        'model'=>$model,
                        'date_from'=>$date_from,
                        'date_to'=>$date_to,
                        'category_id' => $category_id
                ));
	}

	/**
	 * Страница товаров с ценами для магазина $id
	 * @param int $id ID магазина
	 * @throws CHttpException
	 */
	public function actionGoods($id = null)
	{
		$store = Store::model()->findByPk((int)$id);
		if ( ! $store)
			throw new CHttpException(404);


		// Получаем название действия
		$action = Yii::app()->request->getParam('action');

		switch($action) {
			case 'import':
				$this->importGoods($store);
				break;

			case 'export':
				$this->exportGoods($store);
				break;
            case 'importXML':
                $this->importXml($store);
                break;
		}

		$sort = new CSort();
		$sort->defaultOrder = array('update_time' => 'desc');

		$dataProvider = new CActiveDataProvider('StorePrice', array(
			'criteria' => array(
				'condition' => 'store_id = :sid AND by_vendor = 0',
				'params' => array(':sid' => $store->id)
			),
			'pagination' => array(
				'pageSize' => 25
			),
			'sort' => $sort
		));

		// Если есть сделанная задача на экспорт с успешным статусом.
		$taskImport = CatCsv::model()->findByAttributes(array(
			'action'  => 'export',
			'type'    => CatCsv::TYPE_STORE,
			'item_id' => $store->id,
			'status'  => CatCsv::STATUS_FINISHED,
			'file'    => 'IS NOT NULL'
		));

        $importXmlStatus = Yii::app()->redis->get("store_{$store->id}_importXml_progress");
        if ($importXmlStatus) {
            Yii::app()->user->setFlash('xml_import_progress', "Идет импорт XML-файла: $importXmlStatus%");
        }

        $this->render('goods', array(
			'store'        => $store,
			'dataProvider' => $dataProvider,
			'taskImport'   => $taskImport
		));
	}

	/**
	 * Страница привязки товаров к магазинам.
	 * @param int $id ID магазина
	 * @throws CHttpException
	 */
	public function actionAddGood($id = null)
	{
		$store = Store::model()->findByPk((int)$id);
		if ( ! $store)
			throw new CHttpException(404);

		if (Yii::app()->request->isAjaxRequest) {
			// Получаем массив идентификаторов Производителей
			$vendor_ids = Yii::app()->request->getParam('vendor_ids');
			$product_ids = explode(',', Yii::app()->request->getParam('product_ids'));

			if ( ! empty($vendor_ids) || ! empty($product_ids))
			{
				if (is_array($vendor_ids))
				{
					// Получаем список всех "активных" товаров указанных производителей.
					$products = Product::model()->findAllByAttributes(array(
						'vendor_id' => $vendor_ids,
						'status'    => Product::STATUS_ACTIVE
					));

					// Привязываем цены товаров
					StorePrice::savePrices($products, $store);

					foreach($products as $product)
					{
						$product->updateSphinx();
					}
				}

				if (is_array($product_ids)) {
					// Получаем список всех "активных" товаров
					$products = Product::model()->findAllByAttributes(array(
						'id' => $product_ids,
						'status'    => Product::STATUS_ACTIVE
					));

					// Привязываем цены товаров
					StorePrice::savePrices($products, $store);

					foreach($products as $product)
					{
						$product->updateSphinx();
					}
				}

				die(json_encode(array(
					'success' => true
				)));
			} else {
				die(json_encode(array(
					'success' => false
				)));
			}
		}

		$this->render('addGood', array(
			'store' => $store
		));
	}

    private function importXml($store)
    {
        // проверка наличия запущенных импортов для текущего магазина
        if (($taskExists = CatXml::model()->findByAttributes(array(
            'store_id' => $store->id,
            'status' => [CatXml::STATUS_NEW, CatXml::STATUS_IN_PROGRESS]
        )))) {
            Yii::app()->user->setFlash('importError', 'Импорт XML для текущего магазина уже запущен');
            return false;
        }

        // получение данных о xml-файле
        $xmlFile = (isset($_FILES['file_xml']) ? $_FILES['file_xml'] : null);
        if (!$xmlFile || $xmlFile['error'] != 0) {
            Yii::app()->user->setFlash('importError', 'Ошибка загрузки xml-файла');
            return false;
        }

        // сохранение файла
        $filePath = 'uploads/protected/catalog2/importXml';
        $fileName = date('Y-m-d_Hi').'_store_'.Amputate::rus2translit($store->name).'.xml';
        if ( ! file_exists(Yii::app()->basePath . '/../' . $filePath)) {
            mkdir(Yii::app()->basePath . '/../' . $filePath, 0755, true);
        }
        if (!move_uploaded_file($xmlFile['tmp_name'], Yii::app()->basePath . '/../' . $filePath . DIRECTORY_SEPARATOR . $fileName))
        {
            Yii::app()->user->setFlash('importError', 'Ошибка сохранения xml-файла на сервере');
            return false;
        }

        // сохранение задачи импорта
        $xmlImportTask = new CatXml();
        $xmlImportTask->user_id = Yii::app()->user->id;
        $xmlImportTask->status = CatXml::STATUS_NEW;
        $xmlImportTask->store_id = $store->id;
        $xmlImportTask->file = $filePath . DIRECTORY_SEPARATOR . $fileName;

        if ($xmlImportTask->save())
        {
            Yii::app()->gearman->appendJob('catXml:importXml', array(
                'task_id' => $xmlImportTask->id,
            ));
            Yii::app()->user->setFlash('importSuccess', 'XML файл поставлен в очередь на обработку');
        } else {
            Yii::app()->user->setFlash('importError', 'Ошибка создания задачи на импорт');
        }
    }

	/**
	 * Проверяет наличие задачи на импорт для магазина $store_id.
	 * Получает файл csv и ставит задачу на импорт.
	 * @param StorePrice $store  Магазин, для которого нужно импортировать цены товаров.
	 */
	private function importGoods($store)
	{
		// Получаем данные по задаче на импорт для производителя $vid
		$taskImport = CatCsv::model()->findByAttributes(array(
			'action'  => 'import',
			'type'    => CatCsv::TYPE_STORE,
			'item_id' => $store->id,
		));

		if ($taskImport && ($taskImport->status == CatCsv::STATUS_IN_PROGRESS || $taskImport->status == CatCsv::STATUS_NEW))
		{
			Yii::app()->user->setFlash('importError', 'Импорт для текущего магазина уже запущен');
		}
		else
		{
			// Если файл существует и нет ошибок
			if (isset($_FILES['file_csv']) && ($file_csv = $_FILES['file_csv']) && $file_csv['error'] == 0)
			{
				// Новое имя для файла
				$basePath = Yii::app()->basePath.'/..';
				$folder = '/uploads/protected/catalog2/importCsv';
				// Имя csv файла
				$fileName = date('Y-m-d_Hi').'_store_'.Amputate::rus2translit($store->name).'.csv';

				// Создаем папку, если ее вдруг нет.
				if ( ! file_exists($basePath.$folder))
					mkdir($basePath.$folder, 0755, true);

				if (move_uploaded_file($file_csv['tmp_name'], $basePath.$folder.'/'.$fileName))
				{
					if (($handle = fopen($basePath.$folder.'/'.$fileName, "r")) !== false)
					{
						// Считываем первую строку из CSV файла, и проверяем, чтобы там были нужные названия полей
						// Если все ок, то ставим запись в очередь на импорт.
						$data = fgetcsv($handle, 4096, ";");

						if (CatCsv::checkImportCsvHead($data, CatCsv::TYPE_STORE))
						{
							// Подсчитаем кол-во строк в файле, которое равно кол-ву импортируемых элементов
							$countLines = 0;
							while (($line = fgets($handle,4096)) !== false) {
								$countLines++;
							}
							fclose($handle);

							$taskImport = new CatCsv();
							$taskImport->user_id  = Yii::app()->user->id;
							$taskImport->action   = 'import';
							$taskImport->type     = CatCsv::TYPE_STORE;
							$taskImport->item_id  = $store->id;
							$taskImport->status   = CatCsv::STATUS_NEW;
							$taskImport->file     = $folder.'/'.$fileName;
							$taskImport->data     = serialize(array('store_id' => $store->id));
							$taskImport->progress = serialize(array('totalItems' => $countLines, 'doneItems' => 0));

							if ($taskImport->save())
							{
								// Добавляем очередь в воркер
								Yii::app()->gearman->appendJob('catCsv:importStore', array(
									'task_id' => $taskImport->id,
								));

								$this->redirect('/catalog2/admin/catCsv/list');
							} else {
								Yii::app()->user->setFlash('importError', 'Ошибка создания задачи на импорт');
							}

						} else {
							Yii::app()->user->setFlash('importError', 'Неверный набор полей в csv файле');
						}

					}
				}

			}
		}
	}

	/**
	 * Ставит задачу на экспорт для привязанных к магазину товаров.
	 */
	private function exportGoods($store)
	{
		$taskExport = new CatCsv();
		$taskExport->user_id  = Yii::app()->user->id;
		$taskExport->action   = 'export';
		$taskExport->type     = CatCsv::TYPE_STORE;
		$taskExport->item_id  = $store->id;
		$taskExport->status   = CatCsv::STATUS_NEW;
		$taskExport->data     = serialize(array('store_id' => $store->id));
		$taskExport->progress = serialize(array('totalItems' => 0, 'doneItems' => 0));


		if ($taskExport->save()) {
			// Добавляем очередь в воркер
			Yii::app()->gearman->appendJob('catCsv:exportStore', array(
				'task_id' => $taskExport->id,
			));

			$this->redirect('/catalog2/admin/catCsv/list');
		} else {
			Yii::app()->user->setFlash('importError', 'Ошибка создания задачи на экспорт');
		}
	}

	/**
	 * Обновляет цену для указанного магазина и товара
	 *
	 * @param int $sid Инденификатор Магазина
	 * @param int $pid Идентификатор Товара
	 */
	public function actionUpdatePrice($sid = 0, $pid = 0)
	{
		$success = true;
		$errorMsg = '';

		// Получаем цен
		$price = (float) str_replace(' ', '', Yii::app()->request->getParam('price'));


		/** @var $model StorePrice */
		$model = StorePrice::model()->findByAttributes(array(
			'store_id' => (int)$sid,
			'product_id' => (int)$pid
		));
		if ($model) {
			$model->price = $price;
			$model->save(false);

			Yii::app()->gearman->appendJob('sphinx:product2',
				array('product_id' => $pid, 'action' => 'update')
			);
		} else {
			$success = false;
			$errorMsg = 'Привязанный товар не найден';
		}

		die(json_encode(array(
			'success' => $success,
			'errorMsg' => $errorMsg
		)));
	}

	/**
	 * Обновляет скидку для указанного магазина и товара
	 *
	 * @param int $sid Инденификатор Магазина
	 * @param int $pid Идентификатор Товара
	 */
	public function actionUpdateDiscount($sid = 0, $pid = 0)
	{
		$success = true;
		$errorMsg = '';

		// Получаем цен
		$discount = (int)Yii::app()->request->getParam('discount');
		// Ограничиваем диапазоном [0;100]
		$discount = min(100, max(0, $discount));


		/** @var $model StorePrice */
		$model = StorePrice::model()->findByAttributes(array(
			'store_id'   => (int)$sid,
			'product_id' => (int)$pid
		));
		if ($model) {
			$model->discount = $discount;
			$model->save(false);

			// Ставим в герман очередь на обновление товара
			Yii::app()->gearman->appendJob('sphinx:product2',
				array('product_id' => $pid, 'action' => 'update')
			);
		} else {
			$success = false;
			$errorMsg = 'Привязанный товар не найден';
		}

		die(json_encode(array(
			'success'  => $success,
			'errorMsg' => $errorMsg
		)));
	}

	/**
	 * Удаляет привязанный товар магазина.
	 *
	 * @param int $sid Инденификатор Магазина
	 * @param int $pid Идентификатор Товара
	 */
	public function actionDeletePrice($sid = 0, $pid = 0, $ids = '')
	{
		if (Yii::app()->request->isAjaxRequest) {

			if (($ids = Yii::app()->request->getParam('ids'))) {
				StorePrice::model()->deleteAllByAttributes(array(
					'store_id'   => $sid,
					'product_id' => explode(',', $ids)
				));
				$idsProduct = explode(',', $ids);

				$products = Product::model()->findAllByPk($idsProduct);

				foreach ($products as $product) {
					$product->updateSphinx();
				}
			} else {
				/** @var $model StorePrice */
				$model = StorePrice::model()->findByAttributes(array(
					'store_id'   => (int)$sid,
					'product_id' => (int)$pid
				));
				if ($model) {
					$model->delete();
					$product = Product::model()->findByPk((int)$pid);
					$product->updateSphinx();
				}
			}
		}
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 * @return Store
	 */
	public function loadModel($id)
	{
		$model=Store::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='store-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

        /**
         * Создание связки  магазин - производитель
         * @throws CHttpException
         */
        public function actionAddVendor()
        {
                $this->layout = false;

                if (!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $store_id = (int) Yii::app()->request->getParam('store_id');
                $vendor_id = (int) Yii::app()->request->getParam('vendor_id');

                if(!Store::model()->exists('id=:id', array(':id'=>$store_id)) || !Vendor::model()->exists('id=:id', array(':id'=>$vendor_id)))
                        die(CJSON::encode(array('success'=>false, 'message'=>'Некорректное значение магазина или производителя')));

                $exists = Yii::app()->dbcatalog2->createCommand()->from('cat_store_vendor')
                        ->where('store_id=:sid AND vendor_id=:vid', array(':sid'=>$store_id, ':vid'=>$vendor_id))
                        ->limit(1)
                        ->queryAll();


                if(!$exists) {
                        Yii::app()->dbcatalog2->createCommand()->insert('cat_store_vendor', array('vendor_id'=>$vendor_id, 'store_id'=>$store_id));
                        //Yii::app()->gearman->appendJob('assign_products', array('store_id' => $store_id, 'vendor_id' => $vendor_id));
                } else {
                        die(CJSON::encode(array('success'=>false, 'message'=>'Связка уже существует')));
                }

                die(CJSON::encode(array('success'=>true, 'message'=>'')));
        }

        /**
         * Удаляет связку магазин-производитель
         * @param $chain_id
         * @param $store_id
         * @throws CHttpException
         */
        public function actionDeleteVendor($store_id, $vendor_id)
        {
                $this->layout = false;

                if (!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $storeId = (int) $store_id;
                $vendorId = (int) $vendor_id;

		/** @var $store Store */
		$store = Store::model()->findByAttributes(array('id' => $storeId));

                if (!$store || !Vendor::model()->exists('id=:id', array(':id'=>$vendorId)))
                        throw new CHttpException(404);

                Yii::app()->dbcatalog2->createCommand()->delete('cat_store_vendor', 'store_id=:sid AND vendor_id=:vid', array(':vid'=>$vendor_id, ':sid'=>$store_id));


		// После отвязки Производителя, удаляем все мнимые связки
		$store->unbindVendorFromCatStorePrice($vendorId);
        }


        /**
         * Создает копию указанного магазина и редиректит на страницу редактирования копии
         * @param $id - id исходного магазина
         */
        public function actionClone($id)
        {
                $source = $this->loadModel($id);

                $target = new Store('offline');
                $target->attributes = $source->attributes;
                $target->create_time = time();
                $target->update_time = time();

                $target->save(false);

                $vendors_ids = Yii::app()->dbcatalog2->createCommand()->select('vendor_id')->from('cat_store_vendor')
                        ->where('store_id=:id', array(':id'=>$source->id))->queryAll();

                foreach($vendors_ids as $v) {
                        Yii::app()->dbcatalog2->createCommand()->insert('cat_store_vendor', array(
                                'store_id'=>$target->id,
                                'vendor_id'=>$v['vendor_id'],
                        ));
                        //Yii::app()->gearman->appendJob('assign_products', array('store_id' => $target->id, 'vendor_id' => $v['vendor_id']));
                }


                $this->redirect($this->createUrl('update', array('id'=>$target->id)));
        }

        /**
         * Удаление изображения магазина
         * @param $store_id
         * @throws CHttpException
         */
        public function actionDeleteImage()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $store_id = Yii::app()->request->getParam('store_id');

                $store = $this->loadModel($store_id);
                $store->image_id = null;
                $store->save();

                die(json_encode(array('success'=>true)));
        }

        /**
         * Возвращает имя и логин администратора магазина по его id
         * @throws CHttpException
         */
        public function actionCheckAdmin()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $admin_id = Yii::app()->request->getParam('admin_id');

                $admin = User::model()->findByAttributes(array('id'=>(int) $admin_id, 'role'=>User::ROLE_STORES_ADMIN, 'status'=>User::STATUS_ACTIVE));

                if($admin)
                        die(CJSON::encode(array('success'=>true, 'html'=>"$admin->name, $admin->login")));
                else
                        die(CJSON::encode(array('success'=>false, 'message'=>'Пользователь не найден')));
        }

        /**
         * Добавление моератора к магазину
         */
        public function actionAddModerator()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $moderator_id = Yii::app()->request->getParam('moderator_id');
                $store_id = Yii::app()->request->getParam('store_id');

                $store = Store::model()->findByPk((int) $store_id);
                $moderator = User::model()->findByAttributes(array('id'=>(int) $moderator_id, 'role'=>User::ROLE_STORES_MODERATOR, 'status'=>User::STATUS_ACTIVE));

                if(!$moderator)
                        die(CJSON::encode(array('success'=>false, 'message'=>'Добавляемый модератор не найден')));

                if(!$store)
                        die(CJSON::encode(array('success'=>false, 'message'=>'Некорректно указан магазин')));

                $connection = Yii::app()->dbcatalog2;

                $exists = $connection->createCommand()->from('cat_store_moderator')
                        ->where('store_id=:sid and moderator_id=:mid', array(':sid'=>$store->id, ':mid'=>$moderator->id))->queryRow();

                if($exists)
                        die(CJSON::encode(array('success'=>false, 'message'=>'Такой модератор уже привязан к данному магазину')));

                $connection->createCommand()->insert('cat_store_moderator', array('store_id'=>$store->id, 'moderator_id'=>$moderator->id));

                die(CJSON::encode(array('success'=>true, 'message'=>'Модератор успешно добавлен')));
        }

        /**
         * Удаляет связку магазин-производитель
         * @param $store_id
         * @param $moderator_id
         * @throws CHttpException
         */
        public function actionDeleteModerator($store_id, $moderator_id)
        {
                $this->layout = false;

                if (!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $store_id = (int) $store_id;
                $moderator_id = (int) $moderator_id;

                if(!Store::model()->exists('id=:id', array(':id'=>$store_id)) || !User::model()->exists('id=:id', array(':id'=>$moderator_id)))
                        throw new CHttpException(404);

                Yii::app()->dbcatalog2->createCommand()->delete('cat_store_moderator', 'store_id=:sid AND moderator_id=:vid', array(':vid'=>$moderator_id, ':sid'=>$store_id));

                Yii::app()->end();
        }

        /**
	 * Статистика магазина
         * @param $id store
         */
	public function actionStatistic($id)
	{
		$model = $this->loadModel($id);
		$categories = Category::model()->findAll();

		$flagFromSearch = false;
		$timeFrom = false;
		$timeTo = false;


		if (!empty($_GET['timeFrom']) || !empty($_GET['timeTo'])) {
			$timeFrom = $_GET['timeFrom'];
			$timeTo = $_GET['timeTo'];

			$statStoreModel = StatStore::model()->getStatGroupByPeriod($id, strtotime($timeFrom), strtotime($timeTo));
			$flagFromSearch = true;
		} else {
			$statStoreModel = StatStore::model()->getStatGroupByDay($id);
		}


		//Переливание статистики из Reddis в mysql при открытии статистики магазина
		StatStore::updateStatStoreMySql('STAT:STORE:' . $id . ':*');


		$this->render('statistic', array(
			'model'          => $model,
			'categories'     => $categories,
			'statStoreModel' => $statStoreModel,
			'flagFromSearch' => $flagFromSearch,
			'timeFrom'       => $timeFrom,
			'timeTo'         => $timeTo,
		));
	}

        /**
         * Поиск дубликатов магазина
         */
        public function actionFindDublicates()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                /**
                 * Получение параметров, необходимых для поиска дубликата
                 */
                $name = Yii::app()->request->getParam('name');
                $address = Yii::app()->request->getParam('address');
                $city_id = (int) Yii::app()->request->getParam('city_id');
                $model_id = (int) Yii::app()->request->getParam('model_id');

		/*
		 * Принимает значение привязанности к ТЦ. Если ТЦ привязан, то проверки на дубликат не делаем
		 */
		$mall_select = Yii::app()->request->getParam('mall_select') === 'true';


                /**
                 * Получение геокода по адресу
                 */
                $city = City::model()->findByPk($city_id);
                $geocode = CHtml::encode(YandexMap::getGeocode('г.'.(($city) ? $city->name : '').', '.$address));
                $geocode_array = unserialize($geocode);

                /**
                 * Поиск дубликата по названию магазина и его геокоду
                 */
                if($name && !empty($geocode_array))
                        $dublicate_store = Store::model()->findByAttributes(array('name'=>CHtml::encode($name), 'geocode'=>$geocode));
                else
                        $dublicate_store = null;

                /**
                 * Результат
                 */
                if($dublicate_store && $dublicate_store->id != $model_id && $mall_select == false)
                        die(CJSON::encode(array('success'=>true, 'dublicate_id'=>$dublicate_store->id)));
                else
                        die(CJSON::encode(array('success'=>true, 'dublicate_id'=>null)));

        }

	/**
	 * Загружает фотографию для шапки Минисайта
	 *
	 * @throws CHttpException
	 */
	public function actionHeadImageUpload()
	{
		if (!Yii::app()->request->isPostRequest) {
			throw new CHttpException(400);
		}

		/** @var $model Store */
		$model = Store::model()->findByAttributes(array(
			'id' => Yii::app()->request->getParam('sid'),
		));

		if (!$model) {
			throw new CHttpException(404);
		}


		$model->setImageType('headImage');
		$file = UploadedFile::loadImage($model, 'headImage', '', false, null, true, array('width' => 1000, 'height' => 230));
		$file_errors = $file->getErrors();

		if ($file_errors && isset($file_errors['file'])) {
			$error_message = $file_errors['file'][0];
			die(CJSON::encode(array(
				'success' => false,
				'message' => $error_message
			)));
		}

		$model->head_image_id = $file->id;
		$model->save(false);

		$this->layout = false;

		die(CJSON::encode(array(
			'success' => true,
			'html'    => $this->renderPartial('_head_image', array('file' => $file), true)
		)));
	}

	/**
	 * Создание и сохранение объекта гео-охвата для интернет-магазина
	 * @throws CHttpException
	 */
	public function actionAjaxCreateStoreGeo()
	{
		if ( !Yii::app()->request->isAjaxRequest )
			throw new CHttpException(400);

		$geo_type = Yii::app()->request->getParam('geo_type');
		$geo_id = Yii::app()->request->getParam('geo_id');
		$store_id = Yii::app()->request->getParam('store_id');

		$osg = new StoreGeo();
		$osg->type = $geo_type;
		$osg->geo_id = $geo_id;
		$osg->store_id = $store_id;

		if ( $osg->save() ) {
			die(CJSON::encode(array(
				'success'=>true,
				'html'=>$this->renderPartial('_geoForm', array('geo'=>$osg), true),
			)));
		} else {
			die(CJSON::encode(array('success'=>false, 'error'=>'Некорректные данные')));
		}
	}

	/**
	 * Добавляет страны СНГ для магазина
	 * @throws CHttpException
	 */
	public function actionAjaxSetCountries()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();

		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$storeId = intval($request->getParam('store_id'));
		$store = Store::model()->findByPk($storeId);

		if ( $store === null )
			throw new CHttpException(404);

		$countryList = array(
			81, //'Азербайджан',
			245, // 'Армения',
			248, // 'Беларусь',
			1894, // 'Казахстан',
			2788, // 'Молдова',
			3159, // 'Россия',
			9575, // 'Таджикистан',
			9787, // 'Узбекистан',
			9908, // 'Украина',
			277569, // 'Туркмения',
			2303, // киргызстан
			7716096, // абхазия
		);

		$critearia = new CDbCriteria();
		$critearia->condition = 't.type=:type AND t.store_id=:sid AND t.geo_id IN ('.implode(',', $countryList).')';
		$critearia->index = 'geo_id';
		$critearia->params = array(':type'=>StoreGeo::TYPE_COUNTRY, ':sid'=>$storeId);

		$storeGeos = StoreGeo::model()->findAll($critearia);

		$html = '';

		$sg = null;


		foreach ($countryList as $item) {
			if (isset($storeGeos[$item]))
				continue;

			$sg = new StoreGeo();
			$sg->afterSaveCommit = true;

			$sg->store_id = $storeId;
			$sg->type = StoreGeo::TYPE_COUNTRY;
			$sg->geo_id = $item;

			$sg->save(false);

			$html .= $this->renderPartial('_geoForm', array('geo'=>$sg), true);
		}

		if ($sg instanceof StoreGeo) {
			$sg->_resetStoreCity();
		}

		die ( json_encode(array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK) );
	}


	/**
	 * Удаляет фотографию шапки Минисайта
	 *
	 * @throws CHttpException
	 */
	public function actionHeadImageDelete()
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(400);
		}

		$success = false;
		$message = '';

		$fid = Yii::app()->request->getParam('fid');
		$sid = Yii::app()->request->getParam('sid');

		$model = Store::model()->findByPk((int) $sid);
		$file = UploadedFile::model()->findByPk((int) $fid);

		if(!$model || !$file) {
			throw new CHttpException(403);
		}

		// если файл принадлежит указанному магазину
		if ($model->head_image_id == $file->id) {

			// Очищаем инфу о загруженной фотографии
			$model->head_image_id = null;

			// сохранение магазина
			$model->save(false);

			$success = true;
			$message = 'Фото успешно удалено';
		} else {
			$success = false;
			$message = 'Удаляемая фофтография не принадлежит магазину';
		}

		die(CJSON::encode(array(
			'success' => $success,
			'message' => $message
		)));
	}

	/**
	 * Удаление объекта гео-охвата интернет-магазина
	 * @throws CHttpException
	 */
	public function actionAjaxDeleteStoreGeo()
	{
		if ( !Yii::app()->request->isAjaxRequest )
			throw new CHttpException(400);

		$geo_type = (int) Yii::app()->request->getParam('geo_type');
		$geo_id = (int) Yii::app()->request->getParam('geo_id');
		$store_id = (int) Yii::app()->request->getParam('store_id');

		$osg = StoreGeo::model()->findByAttributes(array(
			'store_id'=>$store_id,
			'geo_id'=>$geo_id,
			'type'=>$geo_type,
		));

		if ( !$osg )
			throw new CHttpException(404);

		$deleteResult = Yii::app()->dbcatalog2->createCommand()
			->delete(StoreGeo::model()->tableName(),
			'store_id=:sid and geo_id=:gid and type=:t',
			array(
				':sid'=>$store_id,
				':gid'=>$geo_id,
				':t'=>$geo_type,
			)
		);

		if ( $deleteResult )
			die(CJSON::encode(array('success'=>true)));
		else
			die(CJSON::encode(array('success'=>false, 'error'=>'Некорректные данные')));
	}
}
