<?php

class ProductController extends AdminController
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
                $category = Category::model()->findByPk((int)Yii::app()->request->getParam('category_id'));

                if(!$category)
                        return $this->render('categorySelect');

                $errors = array();
                $products = array();

                if(isset($_POST['Product'])) {

                        foreach($_POST['Product'] as $pid => $data)
                        {
                                $product = Product::model()->findByAttributes(array('id'=>(int)$pid, 'category_id'=>$category->id));

                                if(!$product)
                                        continue;

                                $products[] = $product;
                                $product->attributes = $data;

                                if(in_array($product->status, array(Product::STATUS_ACTIVE, Product::STATUS_APPROVAL)))
                                        $product->setScenario('update');
                                else
                                        $product->setScenario('init');


                                if(!$product->save())
                                        $errors['Product'][$product->id] = $product->getErrors();

                                foreach($product->values as $value)
                                {
                                        $value->setScenario($product->getScenario());
                                        $value->value = @$_POST['Value'][$value->id]['value'];
                                        //$value->desc = isset($_POST['Value'][$value->id]['desc']) ? CHtml::encode($_POST['Value'][$value->id]['value']) : '';
                                        if(!$value->save())
                                                $errors['Value'][$value->id] = $value->getErrors();
                                }

                                $product->updateSphinx();
                        }

                        /**
                         * Создание успешно завершено
                         */
                        if(!$errors)
                                $this->redirect(array('index', 'cid'=>$category->id));
                }

		$this->render('create',array(
			'category'=>$category,
                        'product'=>new Product,
                        'errors'=>$errors,
                        'products'=>$products,
		));
	}

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param null $ids
     * @param null $category_id
     * @throws CHttpException
     * @internal param int $id the ID of the model to be updated
     */
	public function actionUpdate($ids =  null, $category_id = null)
	{
                $category = Category::model()->findByPk((int)$category_id);
                $errors = array();
                $availableOptions = $category->availableOptions;

                if(!$ids || !$category)
                        throw new CHttpException(404);

                /**
                 * Проверка $ids на int
                 */
                $ids = explode(',',$ids);
                foreach($ids as $key=>$value) {
                        $ids[$key] = (int)$value;
                        $ajaxResult[(int)$value] = true; // инициализация массива (pid=>true, по-умолчанию)
                }
		$ids = array_slice($ids, 0, 20);
                $ids = implode(',', $ids);


                if(isset($_POST['Product'])) {

                        $products = array();
                        $ajaxResult = array(); // массив pid=>saving-status, сообщающий ajax authoSave'ру, какие продукты были сохранены успешно
			$errorFlag = false;

                        foreach($_POST['Product'] as $pid => $data)
                        {
                                $product = Product::model()->findByAttributes(array('id'=>(int)$pid, 'category_id'=>$category->id));

                                if(!$product)
                                        continue;

                                $products[] = $product;
                                $product->attributes = $data;

                                if(in_array($product->status, array(Product::STATUS_ACTIVE, Product::STATUS_APPROVAL)))
                                        $product->setScenario('update');
                                else
                                        $product->setScenario('init');

                                if(!$product->save()) {
                                        $errors['Product'][$product->id] = $product->getErrors();
                                        $ajaxResult[$product->id] = false; // ошибка сохранения товара
					$errorFlag = true;
                                }

                                foreach ($product->values as $value)
                                {
                                        if(!isset($availableOptions[$value->option_id])) continue;

                                        $value->setScenario($product->getScenario());
                                        $value->value = @$_POST['Value'][$value->id]['value'];
                                        //$value->desc = isset($_POST['Value'][$value->id]['desc']) ? CHtml::encode($_POST['Value'][$value->id]['value']) : '';
                                        if(!$value->save()) {
                                                $ajaxResult[$product->id] = false; // ошибка сохранения товара (если не сохранена хотя-бы одна его опция)
						$errorFlag = true;
                                        }
                                }

                                if(!$errorFlag)
				{
					$product->updateSphinx();
				}
                        }

                        if(!$errors && !Yii::app()->request->isAjaxRequest)
                                $this->redirect(array('admin/category/index', 'cid'=>$category->id));

                        // возвращение результата ajax authoSave функции
                        if(Yii::app()->request->isAjaxRequest)
                                die(CJSON::encode(array('result'=>$ajaxResult)));
                } else {
                        // Выбор товаров, отмеченных для редактирования
                        $products = Product::model()->findAll("id in ({$ids}) AND category_id=:cid", array(':cid'=>$category->id));
                }

                $this->render('update',array(
                        'category'=>$category,
                        'product'=>new Product,
                        'errors'=>$errors,
                        'products'=>$products,
                ));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
                if(Yii::app()->request->isPostRequest) {

                        $ids = explode(',',$id);
                        foreach($ids as $key=>$value)
                                $ids[$key] = (int)$value;

                        foreach($ids as $id) {
                                $product = $this->loadModel($id);
                                $product->status = Product::STATUS_DELETED;
                                $product->save(false);
                                $product->updateSphinx();
                        }

                        die(CJSON::encode(array('result'=>true)));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Manages all models.
	 */
	public function actionIndex($cid = null)
	{
                $category = Category::model()->findByPk((int)$cid);

                if(!$category)
                        throw new CHttpException(404);

                $model = new Product('search');
                $model->unsetAttributes();
                if(isset($_GET['Product']))
                        $model->attributes=$_GET['Product'];

                if ($model->status == 0)
                        $model->status = null;

                $date_from = Yii::app()->request->getParam('date_from');
                $date_to = Yii::app()->request->getParam('date_to');


                $criteria=new CDbCriteria;
                $criteria->compare('t.category_id', $category->id);

                $criteria->compare('t.vendor_id', $model->vendor_id);
                $criteria->compare('t.name', $model->name, true);
                $criteria->compare('t.barcode', $model->barcode);
                if ($model->id)
                        $criteria->compare('t.id', explode(',', $model->id), true);

                $criteria->compare('t.status', $model->status);
                $criteria->compare('t.status', '<>'.Product::STATUS_DELETED);
                if ($date_from)
                        $criteria->compare('t.create_time', '>='.(strtotime($date_from.' 00:00:00')));
                if ($date_to)
                        $criteria->compare('t.create_time', '<='.(strtotime($date_to.' 23:59:59')));
                $criteria->order = 't.create_time DESC';
		$criteria->compare('t.user_id', $model->user_id);

                if (empty($model->status))
                        $model->status = 0;

		if (isset($_GET['Product']['contractor']))
			$model->contractor = $_GET['Product']['contractor'];

		if (!empty($model->contractor))	{
			$criteria->join = 'INNER JOIN cat_vendor_contractor as cvc ON cvc.vendor_id=t.vendor_id ';
			$criteria->compare('cvc.contractor_id', $model->contractor);
		}


                $dataProvider=new CActiveDataProvider('Product', array(
                        'criteria' => $criteria,
                        'pagination'=>array(
                                'pageSize'=>20,
                        ),
                ));

		$this->render('list',array(
			'dataProvider'=>$dataProvider,
                        'category'=>$category,
                        'date_from'=>$date_from,
                        'date_to'=>$date_to,
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
		$model=Product::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

        public function actionProductCreate($category_id = null)
        {
                if(!Yii::app()->request->isAjaxRequest || !$category_id)
                        throw new CException(403);

                $category = Category::model()->findByPk((int)$category_id);

                if(!$category)
                        throw new CDbException(404);

                $product = new Product('init');
                $product->user_id = Yii::app()->user->id;
                $product->category_id = $category->id;
                $product->status = Product::STATUS_IN_PROGRESS;
                $product->save();

                $response = array(
                        'html'=>$this->renderPartial('_productRow', array('model'=>$product, 'options'=>$category->options, 'class'=>'unsaved'), true),
                        'result'=>true,
                );

                die(CJSON::encode($response));
        }

        /**
         * Создание нового товара и присвоение атрибутам значений исходного товара
         * @param $id исходного товара
         * @param $category_id
         * @throws CHttpException
         */
        public function actionProductClone($id, $category_id)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(403);

                // категория исходного продукта
                $category = Category::model()->findByPk((int)$category_id);

                if($category && isset($_POST['Product'][$id])) {

                        // копирование основных атрибутов продукта
                        $product = new Product('init');
                        $product->attributes = $_POST['Product'][$id];
                        $product->user_id = Yii::app()->user->id;
                        $product->category_id = $category->id;
                        $product->status = Product::STATUS_IN_PROGRESS;

                        // если в основных атрибутах копируемого продукта допущена ошибка - выдается сообщение об ошибке
                        if(!$product->save()) {
                                $response = array(
                                        'text'=>'При заполнении полей исходного товара допущена ошибка',
                                        'result'=>false,
                                );
                                die(CJSON::encode($response));
                        }

                        // копирование значений исходного товара в новый по соответствующему option_id значения
                        if(isset($_POST['Value'])) {
                                foreach($_POST['Value'] as $key => $data)
                                {
                                        $src_value = Value::model()->findByPk((int) $key);
                                        if(!$src_value)
                                                continue;
                                        $dst_value = Value::model()->findByAttributes(array('product_id'=>$product->id, 'option_id'=>$src_value->option_id));
                                        if(!$dst_value)
                                                continue;
                                        $dst_value->setScenario('init');
                                        $dst_value->value = @$data['value'];
                                        //$dst_value->desc = @$data['desc'];
                                        $dst_value->save();
                                }

                                $product->updateSphinx();
                        }

                        $response = array(
                                'html'=>$this->renderPartial('_productRow', array('model'=>$product, 'options'=>$category->options, 'class'=>'unsaved'), true),
                                'result'=>true,
                        );

                        die(CJSON::encode($response));
                }
                throw new CHttpException(404);
        }

        /**
         * Image uploader for Cover and Option images
         */
        public function actionImageUpload($type, $pid, $oid = null)
        {
                $product = $this->loadModel((int) $pid);

                if(!$product)
                        throw new CHttpException(404);

		$product->setImageType('product');
                $file = UploadedFile::loadImage($product, 'file', '', true);

                if(!$file)
                        throw new CHttpException(400);

                if($type == 'cover' && $pid && !$oid) {
                        // Cover updating
                        $product->image_id = $file->id;
                        $product->save(false);

                } elseif($type == 'value' && $pid && $oid) {
                        // Option value updating
                        $value = Value::model()->findByAttributes(array('product_id'=>$product->id, 'option_id'=>(int) $oid));
                        if(!$value)
                                throw new CHttpException(500);

                        Yii::app()->dbcatalog2->createCommand()
                                ->insert('cat_value_file', array('value_id'=>$value->id, 'file_id'=>$file->id));

                } elseif($type == 'image' && $pid) {
                        // Product image uploading
                        Yii::app()->dbcatalog2->createCommand()
                                ->insert('cat_product_image', array('product_id'=>$product->id, 'file_id'=>$file->id));
                }

                $this->layout = false;
                $response = array('result'=>true, 'html'=>$this->renderPartial('_image', array('file'=>$file, 'product'=>$product, 'type'=>$type, 'value'=>isset($value) ? $value : null), true));
                die(CJSON::encode($response));
        }


	/**
	 * Загрузка изображений товара "из внешки" по url
	 * @param integer $pid id товара
	 *
	 * @throws CHttpException
	 */
	public function actionImageUrlUpload($pid)
	{
		$product = $this->loadModel((int) $pid);

		$type = Yii::app()->request->getParam('type');
		$url = Yii::app()->request->getParam('url');

		if ( !$type || !$url || !in_array($type, array('cover', 'image')) )
			throw new CHttpException(400);

		/**
		 * Создание темпового файла
		 */
		$tempFile = tempnam(sys_get_temp_dir(), 'php');

		/**
		 * Загрузка файла в темповый
		 */
		$fp = fopen($tempFile, 'w');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		$result = curl_exec($ch);

		/**
		 * Определение mimetype файла
		 */
		$mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		curl_close($ch);
		fclose($fp);

		if ( !$result )
			die(CJSON::encode(array('success'=>false, 'message'=>'Некорректный URL')));

		/**
		 * Определение имени файла
		 */
		$urlInfo = parse_url($url);
		$filePathInfo = pathinfo($urlInfo['path']);

		$_FILES = array(
			'Product'=>array(
				'name'=>array(
					'file'=>$filePathInfo['basename'],
				),
				'type'=>array(
					'file'=>$mime,
				),
				'tmp_name'=>array(
					'file'=>$tempFile,
				),
				'error'=>array(
					'file'=>0,
				),
				'size'=>array(
					'file'=>filesize($tempFile),
				),
			)
		);

		$product->setImageType('product');
		$file = UploadedFile::loadImage($product, 'file', '', true, null, false, null, true, true);

		if(!$file)
			throw new CHttpException(400);

		if ( $type == 'cover' ) {
			// Cover updating
			$product->image_id = $file->id;
			$product->save(false);

		} elseif($type == 'image') {
			// Product image uploading
			Yii::app()->dbcatalog2->createCommand()
				->insert('cat_product_image', array('product_id'=>$product->id, 'file_id'=>$file->id));
		}

		$this->layout = false;
		$response = array('result'=>true, 'html'=>$this->renderPartial('_image', array('file'=>$file, 'product'=>$product, 'type'=>$type, 'value'=>isset($value) ? $value : null), true));
		die(CJSON::encode($response));
	}

        /**
         * Dleting file of cover or option file
         * @param $id - file_id integer
         * @param null $pid integer
         * @param null $vid integer
         */
        public function actionDeleteFile()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $type = Yii::app()->request->getParam('type');
                $file_id = Yii::app()->request->getParam('file_id');
                $pid = Yii::app()->request->getParam('pid');
                $vid = Yii::app()->request->getParam('vid');

                // cover deleting
                if($type == 'cover' && $pid) {
                        $product = Product::model()->findByPk((int) $pid);
                        if(!$product || $product->image_id != (int) $file_id)
                                throw new CHttpException(404);
                        $product->image_id = null;
                        $product->save(false);

                        die(CJSON::encode(array('result'=>true)));
                }

                // product images deleting
                if ($type == 'image' && $pid) {
                        $result = Yii::app()->dbcatalog2->createCommand()
                                ->delete('cat_product_image', 'product_id=:product_id and file_id=:file_id', array(':product_id'=>(int) $pid, ':file_id'=>(int) $file_id));

                        if($result)
                                die(CJSON::encode(array('result'=>true)));
                }

                // option file deleting
                if($type == 'value' && $vid) {
                        $result = Yii::app()->dbcatalog2->createCommand()
                                ->delete('cat_value_file', 'value_id=:value_id and file_id=:file_id', array(':value_id'=>(int) $vid, ':file_id'=>(int) $file_id));

                        if($result)
                                die(CJSON::encode(array('result'=>true)));
                }
                die(CJSON::encode(array('result'=>false)));
        }

        /**
         * Создание связки с аналогичным товаром
         * @throws CHttpException
         */
        public function actionCreateSimilar()
        {
                $this->layout = false;

                $html = ''; // html для отдачи

                $pid = (int) Yii::app()->request->getParam('pid');
                $spid = (int) Yii::app()->request->getParam('spid');

                if(!Yii::app()->request->isAjaxRequest || !$pid || !$spid)
                        throw new CHttpException(400);

                if(!Product::model()->exists('id=:pid', array(':pid'=>$pid)) || !Product::model()->exists('id=:spid', array(':spid'=>$spid)))
                        die(CJSON::encode(array('success'=>false, 'pid'=>$pid, 'spid'=>$spid, 'message'=>'Одного из связываемых товаров не существует')));


                /**
                 * Сохранение связки товар-аналог
                 */
                if(!$this->existSimilar($pid, $spid)) {
                        $this->saveSimilar($pid, $spid);
                        $html.=$this->renderPartial('_similarRow', array('pid'=>$pid, 'spid'=>$spid), true);
                }
                else
                        die(CJSON::encode(array('success'=>false, 'pid'=>$pid, 'spid'=>$spid, 'message'=>'Связка уже существует')));

                /**
                 * Сохранение взаимообратной связки аналог-товар
                 */
                if(!$this->existSimilar($spid, $pid))
                        $this->saveSimilar($spid, $pid);

                /**
                 * Все аналогичные товары для spid (привязываемого аналогичного товара)
                 */
                $spid_similars = Yii::app()->dbcatalog2->createCommand()
                        ->select('similar_product_id')
                        ->from('cat_similar_product')
                        ->where('product_id=:spid AND similar_product_id<>:pid', array(':spid'=>$spid, ':pid'=>$pid))
                        ->queryAll();

                /**
                 * Связывание pid с аналогами spid (связываем товар с аналогами аналогичного), бред
                 */
                foreach($spid_similars as $sspid) {

                        /**
                         * Сохранение связки
                         */
                        if($this->existSimilar($pid, $sspid['similar_product_id']))
                                continue;
                        $this->saveSimilar($pid, $sspid['similar_product_id']);
                        $html.=$this->renderPartial('_similarRow', array('pid'=>$pid, 'spid'=>$sspid['similar_product_id']), true);

                        /**
                         * Сохранение взаимообратной связки
                         */
                        if(!$this->existSimilar($sspid['similar_product_id'], $pid))
                                $this->saveSimilar($sspid['similar_product_id'], $pid);
                }

                die(CJSON::encode(array(
                        'success'=>true,
                        'pid'=>$pid, 'spid'=>$spid,
                        'html'=>$html,
                )));
        }

        private function existSimilar($pid, $spid)
        {
                $exist = Yii::app()->dbcatalog2->createCommand()->select('count(product_id)')
                        ->from('cat_similar_product')
                        ->where('product_id=:pid and similar_product_id=:spid', array(':pid'=>$pid, ':spid'=>$spid))
                        ->queryScalar();

                if($exist)
                        return true;

                return false;
        }

        private function saveSimilar($pid, $spid)
        {
                $result = Yii::app()->dbcatalog2->createCommand()->insert('cat_similar_product', array(
                        'product_id'=>$pid,
                        'similar_product_id'=>$spid,
                ));

                if($result)
                        return true;

                return false;
        }

        /**
         * Удаление связки с аналогичным товаром
         * @throws CHttpException
         */
        public function actionDeleteSimilar()
        {
                $this->layout = false;

                // товар
                $pid = (int) Yii::app()->request->getParam('pid');
                // аналог товара
                $spid = (int) Yii::app()->request->getParam('spid');

                if(!Yii::app()->request->isAjaxRequest || !$pid || !$spid)
                        throw new CHttpException(400);

                // удаление связки
                $success = Yii::app()->dbcatalog2->createCommand()->delete('cat_similar_product',
                        'product_id=:pid and similar_product_id=:spid',
                        array(':pid'=>(int) $pid, ':spid'=>(int)$spid)
                );

                if($success)
                        die(CJSON::encode(array('success'=>true, 'pid'=>$pid, 'spid'=>$spid)));
                else
                        die(CJSON::encode(array('success'=>false, 'pid'=>$pid, 'spid'=>$spid, 'message'=>'Ошибка удаления')));
        }

        /**
         * Автокомплит страны
         * @param $term
         * @throws CHttpException
         */
        public function actionCountry($term)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);
                $this->layout = false;
                $data = Yii::app()->dbcatalog2->createCommand("SELECT t.id, t.name FROM `country` t WHERE t.name LIKE '" . CHtml::encode($term) . "%'")->queryAll();
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

        /**
         * Валидатор для добавления и удаления значения опции "Цвет" товара
         * @throws CHttpException
         */
        public function actionColorValue()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $action = Yii::app()->request->getParam('action');
                $value = Yii::app()->request->getParam('value');
                $value_id = Yii::app()->request->getParam('value_id');

                $model = Value::model()->findByPk((int) $value_id);
                if(!$model)
                        die(CJSON::encode(array('success'=>false, 'message'=>'Внутренняя ошибка. Обратиться к прогерам')));

                $color = CatColor::model()->findByPk((int) $value);
                if(!$color)
                        die(CJSON::encode(array('success'=>false, 'message'=>'Цвет не указан')));

                $values = $model->value;
                $html = '';

                if($action == 'add') {
                        $html = CHtml::openTag('li')
                                . $color->name.' ['.CHtml::tag('span', array('class'=>'color-value-delete', 'style'=>'cursor:pointer; color:#0069D6;', 'value_id'=>$model->id, 'value'=>$color->id), 'x').']'
                                . CHtml::hiddenField('Value['.$model->id.'][value][]', $value)
                                . CHtml::closeTag('li');
                }

                if($action == 'delete') {
                        //if(!in_array($value, $values))
                        //        die(CJSON::encode(array('success'=>false, 'message'=>'Нечего удалять')));
                }

                die(CJSON::encode(array('success'=>true, 'message'=>'', 'html'=>$html)));
        }

	/**
	 * ПОлучает статистику по добавлению товаров разными пользователями.
	 */
	public function actionGetStatProduct()
	{
		$stat_from = strtotime(Yii::app()->request->getParam('stat_from').' 00:00:00');
		$stat_to = strtotime(Yii::app()->request->getParam('stat_to').' 23:59:59');

		$command = Yii::app()->dbcatalog2->createCommand();
		$command->select('COUNT(*) as cnt, t.user_id, CONCAT_WS(" ", user.firstname, user.lastname) as user_name');
		$command->from(Product::model()->tableName().' as t');
		$command->where('t.create_time >= :ct1 AND t.create_time <= :ct2 and t.status <> :st', array(':ct1' => $stat_from, ':ct2' => $stat_to, ':st'=>Product::STATUS_DELETED));
		$command->join = 'LEFT JOIN myhome.user ON user.id = t.user_id';
		$command->group = 't.user_id';
		$command->order = 'cnt DESC';
		$result = $command->queryAll();

		if (!$result)
			$result = array();

		die(json_encode($result, JSON_NUMERIC_CHECK));
	}

	/**
	 * Действие запускается в отдельном javascript окне и служит для
	 * обрезания фотографий под нужный размер с помощью JCrop
	 *
	 * @param $type string Тип изображения
	 * @param $pid Идентификатор товара
	 */
	public function actionCropCover($type, $pid, $fileid)
	{
		$this->layout = false;

		// Флаг о том, что был произведен кроп.
		$isCroped = false;


		$product = Product::model()->findByPk((int)$pid);

		// Если из формы пришел новый fileId, то используем его
		if (isset($_POST['fileid']))
			$fileid = (int)$_POST['fileid'];

		$uFile = UploadedFile::model()->findByPk((int)$fileid);

		if ( ! $product && ! $uFile)
			throw new CException(404);


		// Ссылка на фотку
		$src = '/'.$uFile->getFullname();
		$fullPath = Yii::getPathOfAlias('webroot').$src;
		// Оригинальные размеры
		list($orgWidth, $orgHeight) = getimagesize($fullPath);

		if (Yii::app()->request->getParam('action') == 'crop')
		{
			// Коэффициент множителя согласно пропорции,
			// с которой мы уменьшили выводимую оригинальную фотографию
			$f = 100 / $_POST['percent'];

			// Параметры, применяемые к картинке-источнику
			$src_offset_x = intval($_POST['x'] * $f);
			$src_offset_y = intval($_POST['y'] * $f);
			$src_width = intval($_POST['w'] * $f);
			$src_height = intval($_POST['h'] * $f);

			$imageHandler = new imageHandler($fullPath, imageHandler::FORMAT_JPEG);

			$imageHandler->jCrop($src_width, $src_height, $src_offset_x, $src_offset_y, $src_width, $src_height);

			$size = $imageHandler->getImageSize();

			// Эмулируем руками некоторое поведение класса CUploadedFile
			$customFile = new AnObj(array());
			$customFile->extensionName = 'jpg';
			$customFile->size = $size;
			$customFile->imageHandler = $imageHandler;
			$customFile->saveAs = function($path) use($customFile) { return $customFile->imageHandler->saveImage($path); };


			$product->setImageType('product');
			$file = UploadedFile::loadImage($product, 'file', '', true, $customFile);

			if ( ! $file) {
				echo CHtml::errorSummary($product);
			} else {
				if ($type == 'cover')
				{
					$product->image_id = $file->id;
					$product->save(false);
				}
				elseif ($type == 'image')
				{
					// Product image uploading
					Yii::app()->dbcatalog2->createCommand()
						->insert('cat_product_image', array('product_id'=>$product->id, 'file_id'=>$file->id));

					// Если дополнительные изображения, то старую фотку удаляем.
					Yii::app()->dbcatalog2->createCommand()
						->delete('cat_product_image', 'product_id = :pid AND file_id = :fid', array(':pid'=>$product->id, ':fid'=>$uFile->id));
				}

				// Считываем данные по новой обрезанной картинке
				$uFile = UploadedFile::model()->findByPk($file->id);
				$src = '/'.$uFile->getFullname();
				$fullPath = Yii::getPathOfAlias('webroot').$src;
				list($orgWidth, $orgHeight) = getimagesize($fullPath);

				$isCroped = true;
			}

		}


		$this->render('cropCover', array(
			'pid'       => $product->id,
			'isCroped'  => $isCroped,
			'uFile'    => $uFile,
			//'orgSrc'    => $src,
			'type'      => $type,
			'orgWidth'  => $orgWidth,
			'orgHeight' => $orgHeight
		));
	}

	public function actionGetProductRow($pid)
	{
		$product = Product::model()->findByPk((int)$pid);

		if ( ! $product)
			throw new CException(404);

		// категория исходного продукта
		$category = Category::model()->findByPk((int)$product->category_id);

		$response = array(
			'html'=>$this->renderPartial('_productRow', array('model'=>$product, 'options'=>$category->options, 'class'=>'unsaved'), true),
			'result'=>true,
		);

		die(CJSON::encode($response));
	}

        /**
         * Выводит оригинал изображение в браузере
         * @param $file_id
         */
        public function actionShowOriginalImage($file_id) {

                $this->layout = false;

                echo CHtml::image($this->createUrl('/download/productImgOriginal', array('file_id'=>$file_id)));

                Yii::app()->end();
        }

	/**
	 * Аякс метод, отдающий html-форму для установки цен на товар в магазинах
	 * @param $pid
	 *
	 * @throws CHttpException
	 */
	public function actionAjaxProductStoreBind($pid)
	{
		$this->layout = false;

		if ( !Yii::app()->request->isAjaxRequest )
			throw new CHttpException(400);

		$product = $this->loadModel((int) $pid);

		$store = new Store('search');

		$formData = null;

		if ( isset($_POST['Store']) ) {

			$formData = $_POST['Store'];
			Yii::app()->cache->set('adminProductBindFormDataUser_' . Yii::app()->user->id, $_POST['Store']);
		} else {
			$formData = Yii::app()->cache->get('adminProductBindFormDataUser_' . Yii::app()->user->id);
		}

		// ищем магазины
		if ( $formData )
			$store->attributes = $formData;

		$formHtml = $this->renderPartial('_productStoreBind', array(
			'product'=>$product,
			'store'=>$store,
		), true);

		die(CJSON::encode(array('success'=>true, 'html'=>$formHtml, 'title'=>'#' . $product->id . ' "' . $product->name . '" - Установка цен')));
	}

	/**
	 * Сохранение-удаление цен на товар в магазинах
	 * @param $store_id
	 * @param $product_id
	 */
	public function actionAjaxProductStoreBindPrice($store_id, $product_id)
	{
		$product = $this->loadModel((int) $product_id);

		$store = Store::model()->findByPk((int) $store_id);
		if (!$store)
			die(CJSON::encode(array('success'=>false, 'error'=>'Магазин не найден')));

		$price = str_replace(' ', '', Yii::app()->request->getParam('price'));
		$url = Yii::app()->request->getParam('url');

		$storePrice = StorePrice::model()->findByAttributes(array(
			'store_id'=>$store->id,
			'product_id'=>$product->id,
		));

		// удаление связки товар-магазин (если цена пустая)
		if ( $price == "") {

			// если связки не было
			if ( !$storePrice )
				die(CJSON::encode(array('success'=>true)));

			// удаление связки
			if ( $storePrice && $storePrice->delete() )
				die(CJSON::encode(array('success'=>true)));
			else
				die(CJSON::encode(array('success'=>false, 'error'=>'Ошибка удаления цены')));

		// создание связки
		} else {

			$price = (int) $price;

			// если связки не было, то создаем ее
			if ( !$storePrice ) {
				$storePrice = new StorePrice();
				$storePrice->store_id = $store->id;
				$storePrice->product_id = $product->id;
				$storePrice->price_type = StorePrice::PRICE_TYPE_MORE;
				$storePrice->status = StorePrice::STATUS_AVAILABLE;
			}

			$storePrice->price = $price;
			$storePrice->by_vendor = 0;
			$storePrice->url = urldecode($url);


			if ( $storePrice->save() )
				die(CJSON::encode(array('success'=>true)));
			else {
				$error_text = '';
				foreach($storePrice->getErrors() as $attribute=>$errors) {
					foreach($errors as $error) {
						$error_text.=$attribute . ': ' . $error . "\n";
					}
				}

				die(CJSON::encode(array('success'=>false, 'error'=>$error_text)));
			}

		}
	}

	/**
	 * Массовое присвоение цены магазинам
	 */
	public function actionAjaxProductSelectedStoreBindPrice($product_id)
	{
		$product = $this->loadModel((int) $product_id);

		$price = str_replace(' ', '', Yii::app()->request->getParam('price'));
		$store_ids = array_map('intval', CJSON::decode(Yii::app()->request->getParam('store_ids')));

		if ( !$product || $price == '' || !$store_ids )
			die(CJSON::encode(array('success'=>false)));

		$price = intval($price);

		foreach ($store_ids as $sid) {

			if ( !Store::model()->exists('id=:id', array(':id'=>$sid)) )
				continue;

			$storePrice = StorePrice::model()->findByAttributes(array(
				'store_id'=>$sid,
				'product_id'=>$product->id,
			));

			// если связки не было, то создаем ее
			if ( !$storePrice ) {
				$storePrice = new StorePrice();
				$storePrice->store_id = $sid;
				$storePrice->product_id = $product->id;
				$storePrice->price_type = StorePrice::PRICE_TYPE_MORE;
				$storePrice->status =  StorePrice::STATUS_AVAILABLE;
			}

			$storePrice->price = $price;
			$storePrice->by_vendor = 0;

			$storePrice->save();
		}

		die(CJSON::encode(array('success'=>true, 'price'=>$price)));
	}
}


/**
 * Класс-шаблон для создания анонимных классов.
 */
class AnObj
{
	protected $methods = array();

	public function __construct(array $options)
	{
		$this->methods = $options;
	}

	public function __call($name, $arguments)
	{
		$callable = null;
		if (array_key_exists($name, $this->methods))
			$callable = $this->methods[$name];
		elseif(isset($this->$name))
			$callable = $this->$name;

		if (!is_callable($callable))
			throw new BadMethodCallException("Method {$name} does not exists");

		return call_user_func_array($callable, $arguments);
	}
}