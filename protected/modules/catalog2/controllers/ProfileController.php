<?php

class ProfileController extends FrontController
{

	public function filters()
	{
		return array(
			'accessControl',
		);
	}

	public function accessRules()
	{
		return array(
			array('allow',
			    'roles'=>array(User::ROLE_STORES_ADMIN, User::ROLE_STORES_MODERATOR, User::ROLE_POWERADMIN, User::ROLE_MALL_ADMIN),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}
	
        /**
         * Удаление товара
         * @throws CHttpException
         */
        public function actionProductDelete()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $id = Yii::app()->request->getParam('id');

                $model = Product::model()->findByAttributes(array('id'=>(int)$id, 'user_id'=>Yii::app()->user->id));

                if(!$model)
                        throw new CHttpException(404);

                $model->status = Product::STATUS_DELETED;
                $model->save(false);
        }

        /**
         * Выбор категории для добавляемого товара
         * @return string
         */
        public function actionProductSelectCategory()
        {
                /**
                 * Очистка всех недосозданных товаров пользователя
                 */
                $old_in_progress = Product::model()->findAll('status=:progress and user_id=:current', array(':current'=>Yii::app()->user->id,':progress'=>Product::STATUS_IN_PROGRESS));
                foreach($old_in_progress as $oip)
                        $oip->hardDelete();

                /**
                 * Создание нового товара
                 */
                $model = new Product('init');

                /**
                 * Если категория нового товара пришла в запросе - создается новый товар с этой категорией
                 * и пользователь редиректится на форму редактирования товара
                 */
                if(isset($_POST['Product']) && isset($_POST['Product']['category_id'])) {

                        $category = Category::model()->findByPk((int)$_POST['Product']['category_id']);

                        if(!$category || $category->children()->exists())
                                throw new CHttpException(404);

                        $model->category_id = (int) $_POST['Product']['category_id'];
                        $model->user_id = Yii::app()->user->id;
                        $model->status = Product::STATUS_IN_PROGRESS;
                        // редирект на форму редактирования товара
                        if($model->save())
                                return $this->redirect($this->createUrl('productUpdate', array('id'=>$model->id)));
                }

                $root = Category::getRoot();
                $mcats = $root->children()->findAll();

                /**
                 * Спсисок последних 10 категорий, в которые пользователь добавлял товары
                 */
                $command = Yii::app()->dbcatalog2;
                $latest_cats_ids = $command->createCommand()->from('cat_product')
                        ->selectDistinct('category_id')
                        ->where('user_id=:uid and (status<>:st1 or status<>:st2)', array(':uid'=>Yii::app()->user->id, ':st1'=>Product::STATUS_DELETED, ':st2'=>Product::STATUS_IN_PROGRESS))
                        ->limit(10)->order('id desc')->queryColumn();

                $latest_cats = array();
                foreach($latest_cats_ids as $lcid){
                        $lc = $command->createCommand()->select('id, name')->from('cat_category')->where('id=:id', array(':id'=>$lcid))->queryRow();
                        if($lc) $latest_cats[$lc['id']] = $lc['name'];
                }

                /**
                 * Открытие формы выбора категории добавляемого товара
                 */
                return $this->render('product_select_category', array(
                        'model'=>$model,
                        'mcats'=>$mcats,
                        'root'=>$root,
                        'latest_cats'=>$latest_cats,
                ), false, array('profileStore', array('user' => Yii::app()->user->model)));
        }

        /**
         * Редактирование товара
         * @param $id
         * @return string
         * @throws CHttpException
         */
        public function actionProductUpdate($id)
	{
                /**
                 * Поиск запрошенного товара в базе с учетом владельца и статуса
                 */
                $criteria = new CDbCriteria();
                $criteria->compare('status', array(Product::STATUS_IN_PROGRESS, Product::STATUS_MODERATE, Product::STATUS_REJECTED));
                $criteria->compare('user_id', Yii::app()->user->id);
                $criteria->compare('id', (int) $id);
                $model = Product::model()->find($criteria);

                if(!$model || !$model->category_id)
                        throw new CHttpException(404);

                $model->setScenario('update');
                $errors = array();

                /**
                 * Если товар добавляется, то выставляется флаг подсвечивания товара в списке, как новодобавленного
                 * Если товар редактируется, то товар не подсвечивается в списке после сохранения
                 */
                if($model->status == Product::STATUS_IN_PROGRESS)
                        Yii::app()->user->setFlash('highlight_product', $model->id);

                if(isset($_POST['Product'])) {

                        $model->attributes = $_POST['Product'];
                        $model->save();
                        $errors['Product'] = $model->getErrors();

                        /**
                         * Для формы Simple
                         * Сохранение новых изображений с их описанием
                         */
                        if(isset($_FILES['Product'])) {

                                foreach($_FILES['Product']['name'] as $file_key=>$file_value){

                                        $model->setImageType('product');

                                        $file = UploadedFile::loadImage($model, $file_key, '', false, null, true, array('width'=>300, 'height'=>300));

                                        if(!$file || $file->getErrors())
                                                continue;

                                        if(!$model->image_id) {
                                                $model->image_id = $file->id;
                                                $model->save(false);

                                        } else {
                                                Yii::app()->dbcatalog2->createCommand()
                                                        ->insert('cat_product_image', array('product_id'=>$model->id, 'file_id'=>$file->id));
                                        }
                                }
                        }

                        /**
                         * Сохранение значений
                         */
                        foreach($model->values as $value)
                        {
                                $value->setScenario($model->getScenario());
                                $value->value = @$_POST['Value'][$value->id]['value'];
                                if(!$value->save())
                                        $errors['Value'][$value->id] = $value->getErrors();
                        }

                        if(empty($errors['Product']) && empty($errors['Value'])) {

                                $model->status = Product::STATUS_MODERATE;
                                $model->save(false, array('status'));

                                /**
                                 * Список всех магазинов пользователя
                                 */
                                $availableStoreIds = Store::getStoresForOwner(Yii::app()->user->id, true);

                                /**
                                 * Сохранение товара в магазины
                                 */
                                if(isset($_POST['for_stores']) ) {

                                        $store_ids = array_map('intval', $_POST['for_stores']);

                                        foreach($store_ids as $sid) {

                                                if(!in_array($sid, $availableStoreIds))
                                                        continue;

                                                $store_price = StorePrice::model()->findByAttributes(array('store_id'=>$sid,'product_id'=>$model->id));

                                                $price = floatval(Yii::app()->request->getParam("Store_{$sid}_product_price"));
                                                $product_status = (int) Yii::app()->request->getParam("Store_{$sid}_product_status");
                                                $price_type = (int) Yii::app()->request->getParam("Store_{$sid}_product_price_type");

                                                if(!isset(StorePrice::$statuses[$product_status]))
                                                        $product_status = StorePrice::STATUS_AVAILABLE;

                                                if(!isset(StorePrice::$price_types[$price_type]))
                                                        $price_type = StorePrice::PRICE_TYPE_MORE;

                                                if(!$store_price)
                                                        $store_price = new StorePrice();

                                                $store_price->price = $price;
                                                $store_price->by_vendor = 0;
                                                $store_price->product_id = $model->id;
                                                $store_price->status = $product_status;
                                                $store_price->price_type = $price_type;
                                                $store_price->store_id = $sid;
                                                $store_price->save();
                                        }

                                        /**
                                         * Удаление цен на товар в магазинах, с которых сняты галочки наличия
                                         */
                                        $forDeleteSids = array_diff($availableStoreIds, $store_ids);
                                        if(!empty($forDeleteSids))
                                                Yii::app()->dbcatalog2->createCommand()
                                                        ->delete('cat_store_price', 'product_id=:pid and store_id in ('.implode(',', $forDeleteSids).')', array(':pid'=>$model->id));

                                } else {
                                        /**
                                         * Если список магазинов для данного товара не указан, то происходит удаление цен для всех магазинов у данного товара
                                         * (На случай, если ранее цена была указана, а теперь сняли все ценники с товара)
                                         */
                                        if(!empty($availableStoreIds))
                                                Yii::app()->dbcatalog2->createCommand()
                                                        ->delete('cat_store_price', 'product_id=:pid and store_id in ('.implode(',', $availableStoreIds).')', array(':pid'=>$model->id));
                                }

                                /**
                                 * Обработчик нажатия "Сохранить и добавить еще один товар"
                                 */
                                if(isset($_POST['continue']) && $_POST['continue']) {
                                        $new_model = new Product('init');
                                        $new_model->category_id = $model->category_id;
                                        $new_model->vendor_id = $model->vendor_id;
                                        $new_model->country = $model->country;
                                        $new_model->user_id = Yii::app()->user->id;
                                        $new_model->status = Product::STATUS_IN_PROGRESS;
                                        $new_model->save();
                                        $this->redirect($this->createUrl('productUpdate', array('id'=>$new_model->id)));

                                } else {
                                        $this->redirect($this->createUrl('list'));
                                }
                        }
                }

                return $this->render('product_form', array(
                        'model'=>$model,
                        'errors'=>$errors,
                        'fileApi'=>Yii::app()->user->getFileApiSupport(),
                ), false, array('profileStore', array('user' => Yii::app()->user->model)));
	}

        /**
         * Загрузка изображений товара
         * @throws CHttpException
         */
        public function actionProductUploadImage()
        {
                if(!Yii::app()->request->isPostRequest)
                        throw new CHttpException(400);

                $model = Product::model()->findByAttributes(array(
                        'id'=>Yii::app()->request->getParam('pid'),
                        'user_id'=>Yii::app()->user->id,
                ));

                if(!$model || !in_array($model->status, array(Product::STATUS_IN_PROGRESS, Product::STATUS_REJECTED, Product::STATUS_MODERATE)))
                        throw new CHttpException(404);


                $model->setImageType('product');
                $file = UploadedFile::loadImage($model, 'file', '', false, null, true, array('width'=>300, 'height'=>300));
                $file_errors = $file->getErrors();

                if($file_errors && isset($file_errors['file'])) {
                        $error_message = $file_errors['file'][0];
                        die(CJSON::encode(array('success'=>false, 'message'=>$error_message)));
                }


                if(!$model->image_id) {
                        $model->image_id = $file->id;
                        $model->save(false);

                } else {
                        Yii::app()->dbcatalog2->createCommand()
                                ->insert('cat_product_image', array('product_id'=>$model->id, 'file_id'=>$file->id));
                }

                $this->layout = false;

                die(CJSON::encode(array('success'=>true, 'html'=>$this->renderPartial('_product_form_image', array('file'=>$file), true))));
        }


        /**
         * Возвращает по ajax html подкатегорий для указанной категории
         * Используется в view actionProductSelectCategory
         * @throws CHttpException
         */
        public function actionGetSubCategories()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $category = Category::model()->findByPk((int) Yii::app()->request->getParam('cid'));

                if(!$category)
                        throw new CHttpException(404);

                /**
                 * Проверка на наличие товаров в текущей категории
                 */
                if(Product::model()->exists('category_id=:cid', array(':cid'=>$category->id)))
                        die(json_encode(array('success'=>false, 'message'=>'Данная категория не содержит подкатегорий.')));

                $html = CHtml::openTag('ul', array('class'=>'depth2'));

                // переданная категория - root (генерация списка подкатегорий для всех главных категорий)
                if($category->id == Category::getRoot()->id) {

                        $mcats = $category->children()->findAll();
                        foreach($mcats as $mcat)
                                $html.= $this->getSubCategoriesListHtml($mcat);

                // генерация списка подкатегорий для указанной категории
                } else {

                        $html.= $this->getSubCategoriesListHtml($category);
                }

                $html.= CHtml::closeTag('ul');

                die(json_encode(array('success'=>true, 'html'=>$html)));
        }

        /**
         * Формирует html список подкатегорий указанной категории
         * @param $category CActiveRecord Category
         * @return string
         */
        protected function getSubCategoriesListHtml($category)
        {
                /**
                 * получение подкатегорий текущей категории
                 */
                $subcats = $category->children()->findAll();
                $html = '';

                foreach($subcats as $scat) {

                        $html.= CHtml::openTag('li', array('cid'=>$scat->id));

                        /**
                         * Получение дочерних подкатегорий
                         */
                        $scat_subcats = $scat->children()->findAll();

                        $html.= CHtml::link($scat->name);

                        if($scat_subcats) {

                                $html.= CHtml::openTag('ul', array('class'=>'depth3'));

                                foreach($scat_subcats as $scat_subcat)
                                        $html.= CHtml::openTag('li', array('cid'=>$scat_subcat->id)) . CHtml::link($scat_subcat->name) . CHtml::closeTag('li');

                                $html.= CHtml::closeTag('ul');
                        }

                        $html.= CHtml::closeTag('li');
                }
                return $html;
        }

        /**
         * Удаление изображения товара
         * @param null $pid
         * @param null $fid
         * @throws CHttpException
         */
        public function actionProductDeleteImage()
        {
                if(!Yii::app()->request->isPostRequest)
                        throw new CHttpException(400);

                $fid = Yii::app()->request->getParam('fid');
                $pid = Yii::app()->request->getParam('pid');

                $model = Product::model()->findByPk((int) $pid);
                $file = UploadedFile::model()->findByPk((int) $fid);

                if(!$model || !$file || $model->user_id != Yii::app()->user->id || $file->author_id != Yii::app()->user->id)
                        throw new CHttpException(403);

                // если файл был обложкой
                if($model->image_id == $file->id) {

                        // поиск другого изображения товара, которое можно выставить обложкой
                        $new_image_id = Yii::app()->dbcatalog2->createCommand()->select('file_id')->from('cat_product_image')
                                ->where('product_id=:pid', array(':pid'=>$model->id))->order('file_id asc')->queryScalar();

                        // если изображений не найдено, то просто убирается обложка товара
                        if(!$new_image_id) {
                                $model->image_id = null;

                        // если изображение найдено, то оно выставляется новой обложкой товара
                        } else {
                                $model->image_id = $new_image_id;

                                // удаление изображения из списка обычных изображений товаров, т.к. оно выставлено обложкой
                                Yii::app()->dbcatalog2->createCommand()
                                        ->delete('cat_product_image', 'product_id=:pid and file_id=:fid', array(':pid'=>$model->id, ':fid'=>$new_image_id));
                        }

                        // сохранение товара
                        $model->save(false);

                // если файл был обычным изображением товара (не обложкой)
                } else {
                        // удаление этого файла из списка изображений товара
                        Yii::app()->dbcatalog2->createCommand()
                                ->delete('cat_product_image', 'product_id=:pid and file_id=:fid', array(':pid'=>$model->id, ':fid'=>$file->id));
                }

                die(CJSON::encode(array('success'=>true)));
        }

        /**
         * Список товаров пользователя
         */
        public function actionList()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

                $criteria = new CDbCriteria();
                $user_id = Yii::app()->user->id;

                $criteria->compare('user_id', $user_id);
                $criteria->compare('status', array(Product::STATUS_MODERATE, Product::STATUS_REJECTED, Product::STATUS_ACTIVE));

                $cat_ids = array_map('intval', Yii::app()->request->getParam('categories', array()));
                $vendor_ids = array_map('intval', Yii::app()->request->getParam('vendors', array()));
                $order_type = Yii::app()->request->getParam('order');
                $order_field = Yii::app()->request->getParam('sort');
                $searchWord = Yii::app()->request->getParam('searchWord');

                $session = Yii::app()->session;

                if(Yii::app()->request->isPostRequest && Yii::app()->request->isAjaxRequest) {
                        $session->add('store_profile_selected_cats', $cat_ids);
                        $session->add('store_profile_selected_vendors', $vendor_ids);
                } else {
                        $cat_ids = $session->get('store_profile_selected_cats');
                        $vendor_ids = $session->get('store_profile_selected_vendors');
                }

                // TODO: Заменить лайковый поиск на sphinx
                if($searchWord)
                        $criteria->compare('name', CHtml::encode($searchWord), true);

                /**
                 * Установка сортировки по колонкам таблицы товаров
                 */
                if($order_field && in_array($order_type, array('asc', 'desc'))) {
                        switch($order_field) {
                                case 'vendor' : $criteria->order = 'vendor_id ' . $order_type; break;
                                case 'category' : $criteria->order = 'category_id ' . $order_type; break;
                                case 'name' : $criteria->order = 'name ' . $order_type; break;
                                case 'status' : $criteria->order = 'status ' . $order_type; break;
                                case 'date' : $criteria->order = 'create_time ' . $order_type; break;
                        }
                } else {
                        $criteria->order = 'id desc';
                }

                if($cat_ids)
                        $criteria->compare('category_id', $cat_ids);

                if($vendor_ids)
                        $criteria->compare('vendor_id', $vendor_ids);

                $dataProvider=new CActiveDataProvider('Product', array(
                        'criteria'=>$criteria,
                        'pagination'=>array(
                                'pageSize'=>20,
                        ),
                ));

                $dataProvider->getData();

                /**
                 * Отдача контента для инфинити-скролла (контент текущей страницы)
                 */
                if(Yii::app()->request->isAjaxRequest) {

                        $html = '';

                        foreach($dataProvider->getData() as $data) {
                                $html.=$this->renderPartial('_product_list_item', array('data'=>$data), true);
                        }

                        /**
                         * Вставка элемента, содержащего ссылку на следующую страницу
                         */
                        if($dataProvider->pagination->currentPage < $dataProvider->pagination->pageCount - 1)
                                $html.=CHtml::hiddenField('next_page_url', $dataProvider->pagination->createPageUrl($this, $dataProvider->pagination->currentPage + 1));
                        else
                                $html.=CHtml::hiddenField('next_page_url', 0);

                        die(json_encode(array('success'=>true, 'html'=>$html, 'productQt'=>$dataProvider->getTotalItemCount()), JSON_NUMERIC_CHECK));
                }

                $this->render('product_list', array(
			'dataProvider' => $dataProvider,
			'categories'   => $this->getUsedCategories($user_id),
			'vendors'      => $this->getUsedVendors($user_id),
			'cat_ids'      => $cat_ids,
			'vendor_ids'   => $vendor_ids,
                ), false, array('profileStore', array('user' => Yii::app()->user->model)));
        }

        /**
         * Возвращает список категорий, в который указанный пользователь добавлял товары
         * @param $user_id
         * @return array
         */
        private function getUsedCategories($user_id)
        {
                $connection = Yii::app()->dbcatalog2;

                $cats_ids = $connection->createCommand()->selectDistinct('category_id')
                        ->from('cat_product')->where('user_id=:id and category_id is not null', array(':id'=>(int) $user_id))->queryColumn();

                if($cats_ids)
                        $categories = $connection->createCommand()->select('id, name')
                                ->from('cat_category')->where('id in ('.implode(',', $cats_ids).')')->queryAll();
                else
                        $categories = array();

                return $categories;
        }

        /**
         * Возвращает список производителей, по которым указанный пользователь добавлял товары
         * @param $user_id
         * @return array
         */
        private function getUsedVendors($user_id)
        {
                $connection = Yii::app()->dbcatalog2;

                $vendor_ids = $connection->createCommand()->selectDistinct('vendor_id')
                        ->from('cat_product')->where('user_id=:id and vendor_id is not null', array(':id'=>(int) $user_id))->queryColumn();

                if($vendor_ids)
                        $vendors = $connection->createCommand()->select('id, name')
                                ->from('cat_vendor')->where('id in ('.implode(',', $vendor_ids).')')->queryAll();
                else
                        $vendors = array();

                return $vendors;
        }

        /**
         * Проверка наличия незаконченных товаров от пользователя
         */
        public function actionCheckProductInProgress()
        {
                $product_in_progress = Product::model()->find('status=:progress and user_id=:current', array(':current'=>Yii::app()->user->id,':progress'=>Product::STATUS_IN_PROGRESS));

                if($product_in_progress) {
                        die(json_encode(array('exists'=>true, 'cat_name'=>$product_in_progress->category->name, 'link'=>$this->createUrl('productUpdate', array('id'=>$product_in_progress->id)))));
                } else {
                        die(json_encode(array('exists'=>false)));
                }
        }

        /**
         * Список магазинов пользователя
         */
        public function actionStoreList()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

		/*
		 * Запрос в итоге возваращает список магазинов для текущего админа, и выводит количество товаров,
		 * которые привязаны к этому магазину. Товары считаются только со статусом "активен"
		 */
		$criteria = new CDbCriteria();
		$criteria->select = 't.*, (SELECT COUNT(DISTINCT p.id) FROM cat_store_price sp INNER JOIN cat_product p ON p.id = sp.product_id WHERE sp.store_id = t.id AND sp.by_vendor = 0 AND p.`status` = :pst) as prod_qt';
		$criteria->join = 'LEFT JOIN cat_store_moderator sm ON sm.store_id=t.id';
		$criteria->condition = '(t.admin_id=:uid OR sm.moderator_id=:uid)';
		$criteria->params = array(':uid'=>Yii::app()->user->id, ':pst' => Product::STATUS_ACTIVE);
		$criteria->group = 't.id';



                $order_type = Yii::app()->request->getParam('order');
                $order_field = Yii::app()->request->getParam('sort');

                /**
                 * Установка сортировки по колонкам таблицы магазинов
                 */
                if($order_field && in_array($order_type, array('asc', 'desc'))) {
                        switch($order_field) {
                                case 'address' : $criteria->order = 'address ' . $order_type; break;
                                case 'name' : $criteria->order = 'name ' . $order_type; break;
                                case 'prod_qt' : $criteria->order = 'prod_qt ' . $order_type; break;
                        }
                } else {
                        $criteria->order = 'id desc';
                }

                $dataProvider=new CActiveDataProvider('Store', array(
                        'criteria'=>$criteria,
                        'pagination'=>array(
                                'pageSize'=>20,
                        ),
                ));

                /**
                 * Отдача контента для инфинити-скролла (контент текущей страницы)
                 */
                if(Yii::app()->request->isAjaxRequest) {

                        $html = '';

                        foreach($dataProvider->getData() as $data) {
                                $html.=$this->renderPartial('_store_list_item', array('data'=>$data), true);
                        }

                        /**
                         * Вставка элемента, содержащего ссылку на следующую страницу
                         */
                        if($dataProvider->pagination->currentPage < $dataProvider->pagination->pageCount - 1)
                                $html.=CHtml::hiddenField('next_page_url', $dataProvider->pagination->createPageUrl($this, $dataProvider->pagination->currentPage + 1));
                        else
                                $html.=CHtml::hiddenField('next_page_url', 0);

                        die(json_encode(array('success'=>true, 'storesQt'=>$dataProvider->getTotalItemCount(), 'html'=>$html), JSON_NUMERIC_CHECK));
                }

                $this->render('//catalog2/profile/store_list', array(
                        'dataProvider'=>$dataProvider,
                ), false, array('profileStore', array('user' => Yii::app()->user->model)));
        }


	/**
	 * Статистика просмотра магазина с идентификатором $id
	 *
	 * @param $id Идентификатор магазина, на который мы смотрим
	 *
	 * @throws CHttpException
	 */
	public function actionStoreStat($id)
	{
		//Переливание статистики из Reddis в mysql при открытии статистики
		StatStore::updateStatStoreMySql('STAT:STORE:' . $id . ':*');

		/** @var $store Store */
		$store = Store::model()->findByPk($id);
		if (
			!$store
			|| $store->tariff_id == Store::TARIF_FREE
			|| !$store->isOwner(Yii::app()->user->id)
		) {
			throw new CHttpException(404);
		}

		$this->bodyClass = 'profile stat';


		// Получаем статистические данные по магазину
		$dateFrom = Yii::app()->request->getParam('dateFrom');
		$dateTo = Yii::app()->request->getParam('dateTo');

		$stat = StatStore::getStatData($store->id, $dateFrom, $dateTo);

		$this->render('//catalog2/profile/store_stat', array(
			'store' => $store,
			'stat'  => $stat
		), false, array('profileStore', array('user' => Yii::app()->user->model)));
	}

	public function actionAjaxGetStoreStat($id)
	{
		$store = Store::model()->findByPk($id);
		if (
			!$store
			|| $store->tariff_id == Store::TARIF_FREE
			|| !$store->isOwner(Yii::app()->user->id)
		) {
			throw new CHttpException(404);
		}

		$dateFrom = Yii::app()->request->getParam('dateFrom');
		$dateTo = Yii::app()->request->getParam('dateTo');

		$stat = StatStore::getStatData($store->id, $dateFrom, $dateTo);

		$html = $this->renderPartial(
			'//catalog2/profile/_statistic',
			array('stat' => $stat),
			true
		);

		exit(json_encode(array(
			'success' => true,
			'html'    => $html
		)));
	}

        /**
         * Создание нового магазина
         */
        public function actionStoreCreate()
        {
                $model = new Store('offline');

                if(isset($_POST['Store']))
                {
                        $model->attributes=$_POST['Store'];
			$model->type = Store::TYPE_OFFLINE;
                        $model->setTimeFromForm();
                        $model->user_id = Yii::app()->user->id;
                        $model->admin_id = Yii::app()->user->id;
                        $model->geocode = CHtml::encode(YandexMap::getGeocode('г.'.(isset($model->city->name) ? $model->city->name : '').', '.$model->address));
                        $model->tariff_id = Store::TARIF_FREE;

                        if($model->save()) {
                                /**
                                 * Сохранение логотипа
                                 */
                                $model->setImageType('logo');
                                $file = UploadedFile::loadImage($model, 'logo', '', true);
                                if($file) {
                                        $model->image_id = $file->id;
                                        $model->save(false, array('image_id'));
                                }

                                $this->redirect(array('storeList'));
                        }

                }

                $this->render('store_form', array(
                        'model'=>$model,
			'city' => null
                ), false, array('profileStore', array('user' => Yii::app()->user->model)));
        }

        /**
         * Создание нового магазина
         */
        public function actionStoreUpdate($id)
        {
		/** @var $model Store */
		$model = Store::model()->findByPk((int)$id);

		if (!$model || !$model->isOwner(Yii::app()->user->id))
			throw new CHttpException(403);


		$storeGeo = StoreGeo::model()->findByAttributes(array(
			'store_id' => $model->id
		));
		if (!$storeGeo) {
			$storeGeo = new StoreGeo();
		}

		/* Если пришли на редактирование магазина через главную страницу
		 * магазина, то нужно будет после сохранения формы редиректнуть обратно
		 */
		$storeUrl = Store::getLink($model->id, 'moneyAbout');
		if (trim($storeUrl, '/') == trim($_SERVER['HTTP_REFERER'], '/')) {
			Yii::app()->user->setReturnUrl($storeUrl);
		}


		if (isset($_POST['Store'])) {
			$model->attributes = $_POST['Store'];
			$model->type = Store::TYPE_OFFLINE;
			$model->setTimeFromForm();
			$model->geocode = CHtml::encode(YandexMap::getGeocode('г.' . (isset($model->city->name)
				? $model->city->name
				: '') . ', ' . $model->address));

			// Город
			if ($_POST['StoreGeo']) {
				$storeGeo->attributes = $_POST['StoreGeo'];
				$storeGeo->store_id = $model->id;
				$storeGeo->type = StoreGeo::TYPE_CITY;

				$storeGeo->_resetStoreCity();
			}

			$storeValid = $model->validate();
			$storeGeoValid = $storeGeo->validate();

			if (!$storeGeoValid) {
				$model->addError('city_id', 'Необходимо заполнить поле город');
			}

			if ($storeValid && $storeGeoValid) {

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

				/* Если есть возвратный URL и он равен главной
				 * странице магазина, то переходим на нее.
				 */
				$url = Yii::app()->user->returnUrl;
				if (trim($url, '/') == trim($storeUrl, '/')) {
					Yii::app()->user->setReturnUrl(null);
					$this->redirect($url);
				} else {
					$this->redirect(array('storeList'));
				}
			}
		}


		$city = City::model()->findByPk((int)$storeGeo->geo_id);


		$this->render('store_form', array(
			'model'    => $model,
			'storeGeo' => $storeGeo,
			'city'     => $city
		), false, array('profileStore', array('user' => Yii::app()->user->model)));
        }

        /**
         * Список товаров магазина с возмоностью привязки новых
         * @param int $store_id - id магазина
         * @throws ChttpException
         */
        public function actionStoreProductList($store_id=null)
        {
                /**
                 * Магазины, которые администрирует или модерирует текущий пользователь
                 * Выводятся в выпадающем списке в view store_product_list
                 * @var array of Store
                 */
                $stores = Store::getStoresForOwner(Yii::app()->user->id);

                /**
                 * Создание переменной, инициализируемой объектом магазина
                 * @var null/Store
                 */
                $store = null;

                // поиск объекта магазина, если указан его id
                $store = Store::model()->findByPk((int) $store_id);

                if(!$store)
                        throw new CHttpException(404);

                if (!$store->isOwner(Yii::app()->user->id))
                        throw new CHttpException(403);

                /**
                 * Критерий выборки товаров, попадающих в отображающийся список на строне view
                 * @var CDbCriteria
                 */
                $criteria = new CDbCriteria();
                // выбрать все поля из product и цену из cat_store_price
                $criteria->select = 't.*, cs.price, cs.price_type, cs.discount, cs.url';
                // в список попадают только активные товары
                $criteria->compare('t.status', Product::STATUS_ACTIVE);

                // получение данных от фильтра
                $cat_ids = array_map('intval', Yii::app()->request->getParam('categories', array()));
                $vendor_ids = array_map('intval', Yii::app()->request->getParam('vendors', array()));
                $order_type = Yii::app()->request->getParam('order');
                $order_field = Yii::app()->request->getParam('sort');
                $searchWord = Yii::app()->request->getParam('searchWord');

                $onlyStoreProducts = Yii::app()->request->getParam('onlyStoreProducts', 0);



		if ($onlyStoreProducts) {
			// критерий выборки только тех товаров, которые уже продаются в указанном магазине
			$criteria->join = 'INNER JOIN `cat_store_price` cs ON cs.product_id=t.id AND cs.store_id=:store_id AND cs.by_vendor=0';
		} else {
			// критерий выборки всех товаров myhome с join'ом цен на товары в указанном магазине
                        $criteria->join = 'LEFT JOIN `cat_store_price` cs ON cs.product_id=t.id AND cs.store_id=:store_id AND cs.by_vendor=0';
		}

                $criteria->params[':store_id'] = $store->id;

                /*// сохранение данных фильтра в сессию
                $session = Yii::app()->session;
                if(Yii::app()->request->isAjaxRequest) {
                        $session->add('store_profile_spl_selected_cats', $cat_ids);
                        $session->add('store_profile_spl_selected_vendors', $vendor_ids);
                } else {
                        $cat_ids = $session->get('store_profile_spl_selected_cats');
                        $vendor_ids = $session->get('store_profile_spl_selected_vendors');
                }*/

                // поиск по названию товара
		if ($searchWord) {
			$criteria->compare('name', CHtml::encode($searchWord), true);
		}

                // Установка сортировки по колонкам таблицы товаров
		if ($order_field && in_array($order_type, array('asc', 'desc'))) {
			switch ($order_field) {
				case 'name' :
					$criteria->order = 'name ' . $order_type;
					break;
			}
		} else {
			$criteria->order = 'id desc';
		}

		// фильтр по категориям
		if ($cat_ids) {
			$criteria->compare('category_id', $cat_ids);
		}
		// фильтр по производителям
		if ($vendor_ids) {
			$criteria->compare('vendor_id', $vendor_ids);
		}

                /**
                 * Провайдер для выводимых в списке товаров
                 * @var CActiveDataProvider
                 */
                $dataProvider = new CActiveDataProvider('Product', array(
			'criteria'   => $criteria,
			'pagination' => array(
				'pageSize' => 20,
			),
                ));

                // если запрос не аяксовый, то подготавливаются данные для отображения фильтра по категориям и производителям
		if (!Yii::app()->request->isAjaxRequest) {
			$categories = Yii::app()->dbcatalog2
				->createCommand('SELECT t.id, t.name FROM cat_category t WHERE (t.rgt - t.lft) = 1 ORDER BY t.level')
				->queryAll();
			$vendors = Yii::app()->dbcatalog2
				->createCommand()
				->select('id, name')
				->from('cat_vendor')
				->queryAll();
		}

                // рендер списка товаров
                $this->render('store_product_list', array(
			'dataProvider'      => $dataProvider,
			'stores'            => $stores,
			'store'             => $store,
			'categories'        => isset($categories) ? $categories : array(),
			'vendors'           => isset($vendors) ? $vendors : array(),
			'cat_ids'           => $cat_ids,
			'vendor_ids'        => $vendor_ids,
			'onlyStoreProducts' => $onlyStoreProducts,
                ), false, array('profileStore', array('user' => Yii::app()->user->model)));
        }

        /**
         * Сохранение цены магазина для указанного товара
         */
        public function actionSavePrice()
        {
                // получение данных для сохранения
                $store_id = (int) Yii::app()->request->getParam('store_id');
                $product_id = (int) Yii::app()->request->getParam('product_id');
                $price = Yii::app()->request->getParam('price');
                $price_type = (int) Yii::app()->request->getParam('price_type');
                $enabled = (int) Yii::app()->request->getParam('enabled');
		$url =  Yii::app()->request->getParam('url');
		$discount = floatVal(str_replace(',', '.', Yii::app()->request->getParam('discount')));


		/** @var $store Store Магазин, в который добавляется товар */
		$store = Store::model()->findByPk((int) $store_id);

		if (!$store || !$store->isOwner(Yii::app()->user->id)) {
			throw new CHttpException(403);
		}

                // поиск цены на товар от данного магазина (если есть)
                $store_price = StorePrice::model()->findByAttributes(array(
			'store_id'   => $store_id,
			'product_id' => $product_id
		));

                // если запрос на удаление связки
		if ($store_price && !$enabled) {
			$store_price->delete();
			/** @var $product Product */
			$product = Product::model()->findByPk($product_id);
			$product->updateSphinx();
			$store->updateSphinx();
			die(CJSON::encode(array(
				'success' => true,
				'productQt' => $store->productQt
			)));
		}

                // создание связки товар-магазин, если ее еще нет
		if (!$store_price) {

                        // проверка на наличие указанного магазина
			if (!$store) {
				die(CJSON::encode(array(
					'success' => false,
					'message' => 'Указанный магазин не существует'
				)));
			}

                        // проверка на наличие и активность указанного товара
			if (!Product::model()->exists(
				'id=:pid and status=:st',
				array(':pid' => $product_id, ':st' => Product::STATUS_ACTIVE)
			)) {
				die(CJSON::encode(array(
					'success' => false,
					'message' => 'Указанный товар не существует или не активен'
				)));
			}

                        // создание новой связки магазин-товар
                        $store_price = new StorePrice();
                }

                // если тип цены указан некорректно, то устанавливается значение по-умолчанию
		if (!isset(StorePrice::$price_types[$price_type])) {
			$price_type = StorePrice::PRICE_TYPE_MORE;
		}

                // заполнение связки товар-магазин данными (цена, тип цены и т.п.)
                $store_price->store_id = $store_id;
                $store_price->product_id = $product_id;
		$store_price->status = StorePrice::STATUS_AVAILABLE;
                $store_price->price = $price;
		$store_price->url = $url;
                $store_price->price_type = $price_type;
		$store_price->by_vendor = 0;

		if ($store->tariff_id == Store::TARIF_MINI_SITE) {
			$store_price->setDiscount($discount);
		}

		if (!$store_price->save()) {
			// если связка не сохранилась, то информируем клиента об ошибке

			$message = 'Ошибка';
			foreach ($store_price->getErrors() as $attribute_errors) {
				$message = $attribute_errors[0];
			}
			die(CJSON::encode(array(
				'success' => false,
				'message' => $message
			)));

		} else {
			// информирование клиента об успешном сохранении связки

			/** @var $product Product */
			$product = Product::model()->findByPk($product_id);
			$product->updateSphinx();
			$store->updateSphinx();
			die(CJSON::encode(array(
				'success'   => true,
				'productQt' => $store->productQt
			)));
		}
        }

        /**
         * Удаление магазина
         * @throws CHttpException
         */
        public function actionStoreDelete()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $store_id = (int) Yii::app()->request->getParam('store_id');

                if(!$store_id)
                        die(CJSON::encode(array('success'=>false, 'message'=>'Не указан магазин для удаления')));

                $store = Store::model()->findByPk($store_id);

                if (!$store->isOwner(Yii::app()->user->id))
                        throw new CHttpException(403);

                if(!$store)
                        die(CJSON::encode(array('success'=>false, 'message'=>'Удаляемый магазин не существует')));

                // удаление связок товаров с удаляемым магазином
                StorePrice::model()->deleteAllByAttributes(array('store_id'=>$store->id));

                // удаление магазина
                $store->delete();

                die(CJSON::encode(array('success'=>true)));
        }

        /**
         * Удаление изображения магазина
         * @param $store_id
         * @throws CHttpException
         */
        public function actionStoreDeleteImage($store_id)
        {
                $store = Store::model()->findByPk((int) $store_id);

                if (!$store || !$store->isOwner(Yii::app()->user->id))
                        throw new CHttpException(403);

                $store->image_id = null;
                $store->save(false, array('image_id'));
                die(CJSON::encode(array('success'=>true)));
        }

        /**
         * Копирование товаров из магазина в магазин
         */
        public function actionProductCopy($from_store=null)
        {
                /**
                 * Типы копирования товаров
                 */
                $copy_types = array(
                        1=>'Копировать с добавлением новых товаров к списку',
                        2=>'Копировать с заменой всего списка товаров',
                );

                /**
                 * Массив ошибок копирования
                 */
                $errors = array();

                // получение списка всех магазинов пользователя
                $stores = Store::getStoresForOwner(Yii::app()->user->id);

                // проверка на административные права пользователя для указанного магазина
                if ($from_store && !isset($stores[(int) $from_store]))
                        throw new CHttpException(403);

                // если магазин, с которого копировать товары, указан, то выставляем его выбранным по-умолчанию
                if ($from_store)
                        $selected_store = Store::model()->findByPk($from_store);
                else
                        $selected_store = !empty($stores) ? reset($stores) : null;

                $copy_type = Yii::app()->request->getParam('copy_type');
                $to_stores = Yii::app()->request->getParam('to_stores');

                // валидация и копирование товаров (если пришел запрос на копирование)
                if($selected_store && $copy_type && $to_stores) {

                        // валидация запроса
                        if(!array_key_exists($copy_type, $copy_types))
                                $errors[] = 'Некорректный тип копирования';
                        if(!is_array($to_stores) || count($to_stores) == 0)
                                $errors[] = 'Не указаны магазины, в которые происходит копирование';

                        if(empty($errors)) {

                                $connection = Yii::app()->dbcatalog2;

                                // получение списка товаров для вставки в другие магазины
                                $products_for_copy = $connection->createCommand()->select('product_id, price, status, price_type')
                                        ->from('cat_store_price')
                                        ->where('store_id=:sid', array(':sid'=>$selected_store->id))->queryAll();

                                foreach($to_stores as $to_store_id) {

                                        $to_store = $connection->createCommand()->from('cat_store')
                                                ->where('id=:sid', array(':sid'=>(int) $to_store_id))->queryRow();

                                        // проверка на наличие магазина и прав управления для текущего пользователя
                                        if (!$to_store || !array_key_exists($to_store['id'], $stores))
                                                continue;

                                        // копирование с заменой списка товаров
                                        if ($copy_type == 2) {
                                                // очистка
                                                $connection->createCommand()->delete('cat_store_price', 'store_id=:sid', array(':sid'=>$to_store['id']));
                                        }

                                        // транзакционное копирование товаров в магазин
                                        $transaction = $connection->beginTransaction();
                                        foreach($products_for_copy as $product) {

                                                // если копирование без замены, то проверяем наличие копируемого товара в магазине
                                                if($copy_type == 1) {
                                                        $exists = $connection->createCommand()->from('cat_store_price')
                                                                ->where('store_id=:sid and product_id=:pid', array(':sid'=>$to_store['id'], ':pid'=>$product['product_id']))->queryRow();
                                                        if($exists)
                                                                continue;
                                                }
                                                // копирование
                                                $connection->createCommand()
                                                        ->insert('cat_store_price', array(
                                                                'store_id'=>$to_store['id'],
                                                                'product_id'=>$product['product_id'],
                                                                'price'=>$product['price'],
                                                                'status'=>$product['status'],
                                                                'price_type'=>$product['price_type'],
                                                                'create_time'=>time(),
                                                                'update_time'=>time(),
                                                        )
                                                );
                                        }
                                        $transaction->commit();
                                }

                                $this->redirect($this->createUrl('productCopySuccess', array(
                                        'from_store'=>$selected_store->id,
                                        'to_stores_ids'=>$to_stores,
                                )));
                        }
                }

                // рендер формы копирования товаров
                $this->render('product_copy', array(
                        'stores'=>$stores,
                        'selected_store'=>$selected_store,
                        'copy_types'=>$copy_types,
                        'to_stores'=>$to_stores,
                        'errors'=>$errors,
                ), false, array('profileStore', array('user' => Yii::app()->user->model)));
        }

        /**
         * Вывод результата копирования товаров из магазина в магазин
         * @throws CHttpException
         */
        public function actionProductCopySuccess()
        {
                $from_store = Yii::app()->request->getParam('from_store');
                $to_stores_ids = Yii::app()->request->getParam('to_stores_ids');

                // все магазины, которыми текущий пользователь может управлять
                $stores = Store::getStoresForOwner(Yii::app()->user->id);

                // проверка на право управления магазином, с которого копировали товары
                if (!isset($stores[(int) $from_store]))
                        throw new CHttpException(403);

                // получение магазина, с которого копировали товары
                $from_store = Store::model()->findByPk((int) $from_store);

                // удаление магазинов, которыми пользователь не имеет права управлять
                $to_stores_ids = array_map('intval', $to_stores_ids);
                foreach($to_stores_ids as $key=>$to_store_id) {
                        if (!isset($stores[$to_store_id]))
                                unset($to_stores_ids[$key]);
                }

                $to_stores = Store::model()->findAll('id in (' . implode(',',$to_stores_ids) . ')');

                $this->render('product_copy_success', array(
                        'from_store'=>$from_store,
                        'to_stores'=>$to_stores,
                ), false, array('profileStore', array('user' => Yii::app()->user->model)));
        }

        /**
         * Редактирование витрины товаров для магазинов с платными тарифами
         * @param $id
         * @param $from_card параметр, отвечающий за то, что после сохранения витрины нужно редиректить юзера на карточку магазина
         * @throws CHttpException
         */
        public function actionStoreShowcase($id, $from_card=false)
        {
                $model = Store::model()->findByPk((int) $id);

                if (!$model || $model->tariff_id == Store::TARIF_FREE || !$model->isOwner(Yii::app()->user->id))
                        throw new CHttpException(404);

                $model->setScenario('showcase-update');
                $errors = array();

                if (isset($_POST['Product'])) {

                        $showcase = $model->getShowcase_data();

                        // заполнение массива showcase данными из POST
                        foreach($_POST["Product"] as $key=>$prod){
                                if (!array_key_exists($key, $showcase))
                                        continue;
                                if (!Product::model()->exists('id=:id', array(':id'=>(int) $prod['pid'])) && !empty($prod['pid']))
                                        continue;
                                $showcase[$key] = (int) $prod['pid'];

                        }
                        $model->setShowcase_data($showcase);

                        // отключили валидацию
                        /*// проверка на заполненность всех элементов витрины
                        foreach($showcase as $key=>$item) {
                                if(!$item) $errors[]=$key;
                        }
                        */
                        if (!$errors && $model->save()) {
                                if (!$from_card)
                                        $this->redirect('/catalog/profile/storeList');
                                else
                                        $this->redirect($this->createUrl('/catalog2/store/index', array('id'=>$model->id)));
                        }


                }

                $this->render('store_showcase', array(
                        'model'=>$model,
                        'connection'=>Yii::app()->dbcatalog2,
                        'errors'=>$errors,
                ), false, array('profileStore', array('user' => Yii::app()->user->model)));
        }


	/**
	 * Список папок пользователя
	 */
	public function actionFolderList()
	{
		$this->bodyClass = 'profile folders';

		$criteria = new CDbCriteria();
		$criteria->condition = 'user_id=:userId AND status<>:status';
		$criteria->params = array(':userId' => Yii::app()->user->id, ':status' => CatFolders::STATUS_DELETED);
		$items = CatFolders::model()->findAll($criteria);
		$this->render('//catalog2/profile/folder_list', array('items' => $items), false, array(
			'profileMall', array(
				'user' => Yii::app()->user->model,
			)
		));
	}
}