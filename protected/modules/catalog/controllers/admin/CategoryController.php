<?php

class CategoryController extends AdminController
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
	public function actionCreate($in = null)
	{
                /**
                 * Проверка наличия родительской категории для создаваемой
                 */
                $root = Category::model()->findByPk((int)$in);
                if(!$root)
                        throw new CHttpException(404);

                if(Product::model()->exists('category_id=:cid', array(':cid'=>$root->id)))
                        throw new CHttpException(404);

		$model=new Category;

		if(isset($_POST['Category']))
		{
			$model->attributes=$_POST['Category'];
                        $model->user_id=Yii::app()->user->id;

                        if($model->appendTo($root))
                                $this->redirect(array('update','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
                        'root'=>$root,
                        'errors'=>array(),
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
                $root=$model->parent()->find();
                $errors=array();



		if(isset($_POST['Category']))
		{
			/**
			 * Сохраняем предыдущий статус
			 */
			$model->previousStatus = $model->status;

			$model->attributes=$_POST['Category'];
                        $model->saveNode();

                        /**
                         * Сохранение опций категории и их допустимых значений
                         */
                        foreach($model->daoOptions as $option)
                        {
                                /**
                                 * Пропускаем обновление дочерних опций
                                 */
                                if($option['parent_id'])
                                        continue;

                                if(isset($_POST['Option'][$option['id']]))
                                {
                                        $opt_model = new Option('update');
                                        $opt_model->id = $option['id'];
                                        $opt_model->attributes = $option;
                                        $opt_model->attributes = $_POST['Option'][$option['id']];
                                        $params = $opt_model->getParamsArray();

                                        if(isset($_POST['Option'][$option['id']]['params'])) {
                                                foreach($_POST['Option'][$option['id']]['params'] as $param_key=>$param_value) {
                                                        $params[$param_key] = $param_value;
                                                }
                                                $opt_model->param = $params;
                                        }

                                        if(!$opt_model->validate())
                                                $errors['Option'][$option['id']] = $opt_model->getErrors();

                                        Yii::app()->db->createCommand()
                                                ->update($opt_model->tableName(), $opt_model->attributes, 'id=:id',array(':id'=>$option['id']));

                                        /**
                                         * Сохранение значений
                                         */
                                        foreach($opt_model->getDaoAvailableValues() as $value) {

                                                if(isset($_POST['Value'][$value['id']]))
                                                {
                                                        $val_model = new Value('optionValue');
                                                        $val_model->id = $value['id'];
                                                        $val_model->attributes = $value;
                                                        $val_model->attributes=$_POST['Value'][$value['id']];
                                                        $val_model->position = $value['position'];
                                                        if(!$val_model->validate())
                                                                $errors['Value'][$value['id']] = $val_model->getErrors();

                                                        Yii::app()->db->createCommand()
                                                                ->update($val_model->tableName(), $val_model->attributes, 'id=:id',array(':id'=>$value['id']));
                                                }
                                        }
                                }
                        }

                        if(!$model->getErrors() && !$errors)
                                $this->redirect(array('index','cid'=>$root->id));
		}

		$this->render('update',array(
			'model'=>$model,
                        'root'=>$root,
                        'errors'=>$errors,
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
			$this->loadModel($id)->deleteNode();
                        $options = Option::model()->findAllByAttributes(array('category_id'=>(int) $id));
                        foreach($options as $option)
                        {
                                $option->deleteValues();
                                $option->delete();
                        }

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

        /**
         * Перемещение категорий
         * @param integer $cid
         * @param integer $to
         * @throws CDbException
         * @throws CHttpException
         */
        public function actionMove($cid = null, $to = null)
        {
                $model = Category::model()->findByPk((int)$cid);
                $root=$model->parent()->find();

                if(!$model || !$to)
                        throw new CDbException(404);

                switch($to) {
                        case 'up' :
                                $prev = $model->prev()->find();
                                if($prev)
                                        $model->moveBefore($prev);
                                break;
                        case 'down' :
                                $next = $model->next()->find();
                                if($next)
                                        $model->moveAfter($next);
                                break;
                        default :
                                throw new CHttpException(404);
                                break;
                }

                $this->redirect(array('index','cid'=>$root->id));
        }

	/**
	 * Manages all models.
	 */
	public function actionIndex($cid = null)
	{
		/**
		 * Поиск детей запрашиваемой категории
		 */
		if ($cid) {
			$root = Category::model()->findByPk((int)$cid);
		} else {
			$root = Category::getRoot();
		}


		$product = new Product('search');
		if ($root->id != 1) {
			$product->category_id = $root->id;
		}

		$request = Yii::app()->getRequest();
		$date_from = $request->getParam('date_from');
		$date_to = $request->getParam('date_to');
		$bind_store = $request->getParam('bind_store');

		if (isset($_GET['Product'])) {
			$product->attributes = $_GET['Product'];
			$productDataProvider = $product->search($date_from, $date_to, $bind_store);

			return $this->render('productSearch', array(
				'dataProvider' => $productDataProvider,
				'product'      => $product,
				'date_from'    => $date_from,
				'date_to'      => $date_to,
				'bind_store'   => $bind_store
			));
		}

		$categories = $root->children()->findAll();

		if (!$categories && $root->id != 1) {
			return $this->redirect(array('admin/product/index', 'cid' => $root->id));
		}

		$dataProvider = new CArrayDataProvider($categories, array(
            'pagination'=>array(
                'pageSize'=>200,
            )));

		$model = new Category('search');
		$model->unsetAttributes(); // clear any default values
		if (isset($_GET['Category'])) {
			$model->attributes = $_GET['Category'];
		}

		return $this->render('list', array(
			'dataProvider' => $dataProvider,
			'root'         => $root,
			'product'      => $product,
			'date_from'    => $date_from,
			'date_to'      => $date_to,
			'bind_store'   => $bind_store
		));
	}

        /**
         * Ajax создание новой опции для категории
         * @param integer $category_id
         * @throws CHttpException
         */
        public function actionOptionCreate($category_id = null)
        {
                $this->layout = false;

                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(404);

                $category = $this->loadModel($category_id);
                $option = new Option('init');
                $option->category_id = $category->id;
                $option->type_id = Option::TYPE_INPUT;
                $option->save();
                $option->initProductValues();

                $response = array(
                        'html'=>$this->renderPartial('_optionRow', array('model'=>$option, 'groups'=>$category->groupsArray), true),
                        'result'=>true,
                );

                die(CJSON::encode($response));
        }

        /**
         * Ajax создание нового допустимого значения для опции с параметром valueList (@see Option::$typeParams)
         * @param integer $option_id
         * @param string $val
         * @throws CHttpException
         */
        public function actionOptionValueCreate($option_id = null, $val = null)
        {
                $this->layout = false;

                if(!Yii::app()->request->isAjaxRequest || !$val)
                        throw new CHttpException(404);

                $option = Option::model()->findByPk((int)$option_id);

                if(!$option)
                        throw new CHttpException(404);

                $value = new Value('optionValue');
                $value->option_id = $option->id;
                $value->value = $val;
                $value->save();

                $response = array(
                        'html'=>$this->renderPartial('_optionValueRow', array('model'=>$value), true),
                        'result'=>true,
                );

                die(CJSON::encode($response));
        }

        /**
         * Обновление типа опции
         * @param integer $type
         * @param integer $option_id
         * @param bool $ignore_warn
         * @throws CHttpException
         */
        public function actionGetTypeParams($type = null, $option_id = null, $ignore_warn = false)
        {
                $this->layout = false;

                if(!Yii::app()->request->isAjaxRequest || !isset(Option::$typeParams[(int)$type]))
                        throw new CHttpException(404);

                $option = Option::model()->findByPk((int)$option_id);

                if(!$option)
                        throw new CHttpException(404);


                /**
                 * Для опции Габарит
                 */
                if(($type == Option::TYPE_SIZE && $option->type_id == Option::TYPE_INPUT) || ($type == Option::TYPE_INPUT && $option->type_id == Option::TYPE_SIZE)) {

                        if(!$ignore_warn && $option->checkUsage())
                        {
                                $result = false;
                                $message = "Данная опция уже используется некоторыми товарными позициями. Значения опций будут сохранены. Всё ok!";
                        }
                        elseif ($ignore_warn || !$option->checkUsage())
                        {
                                $option->type_id = (int)$type;
                                $option->save();
                                $result = true;
                                $message = '';
                        }
                } elseif (($type == Option::TYPE_INPUT && $option->type_id == Option::TYPE_TEXTAREA) || ($type == Option::TYPE_TEXTAREA && $option->type_id == Option::TYPE_INPUT)) {

                        if(!$ignore_warn && $option->checkUsage())
                        {
                                $result = false;
                                $message = "Данная опция уже используется некоторыми товарными позициями. Значения опций будут сохранены. Всё ok!";
                        }
                        elseif ($ignore_warn || !$option->checkUsage())
                        {
                                $option->type_id = (int)$type;
                                $option->save();
                                $result = true;
                                $message = '';
                        }

                } else {

                        /**
                         * Для остальных опций
                         */
                        if(!$ignore_warn && $option->checkUsage())
                        {
                                $result = false;
                                $message = "Данная опция уже используется некоторыми товарными позициями. Смена её типа приведет к удалению старых значений этой опции у всех товаров.
                         Вы по-прежнему хотите сменить тип данной опции?";
                        }
                        elseif ($ignore_warn || !$option->checkUsage())
                        {
                                $option->clearValues();
                                $option->type_id = (int)$type;
                                $option->save();
                                $result = true;
                                $message = '';
                        }
                }


                $response = array(
                        'params'=>Option::$typeParams[(int)$type],
                        'result'=>$result,
                        'message'=>$message,
                        'old_value'=>$option->type_id,
                        'paramsFormHtml'=>$option->getParamsForm(),
                );

                die(CJSON::encode($response));
        }

        /**
         * Удаление опции
         * @param integer $option_id
         * @param bool $ignore_warn
         * @throws CHttpException
         */
        public function actionOptionDelete($option_id = null, $ignore_warn = false)
        {
                $this->layout = false;

                if(!Yii::app()->request->isAjaxRequest || !$option_id)
                        throw new CHttpException(404);

                $option = Option::model()->findByPk((int)$option_id);

                if(!$option)
                        throw new CHttpException(404);

                if(!$ignore_warn && $option->checkUsage())
                {
                        $result = false;
                        $message = "Данная опция уже используется некоторыми товарными позициями. Удаление приведет к потере значений этой опции у всех товаров.
                        Вы по-прежнему хотите удалить данную опцию?";
                }
                elseif ($ignore_warn || !$option->checkUsage())
                {
                        $option->deleteValues();
                        $option->delete();
                        $result = true;
                        $message = '';
                }

                $response = array(
                        'result'=>$result,
                        'message'=>$message,
                );

                die(CJSON::encode($response));

        }

        /**
         * Удаление допустимого значения опции
         * @param integer $value_id
         * @throws CHttpException
         */
        public function actionValueDelete($value_id = null)
        {
                $this->layout = false;

                if(!Yii::app()->request->isAjaxRequest || !$value_id)
                        throw new CHttpException(404);

                $value = Value::model()->findByPk((int)$value_id);

                if(!$value)
                        throw new CHttpException(404);

                $value->delete();

                $response = array(
                        'result'=>true,
                );

                die(CJSON::encode($response));
        }

        /**
         * Автокомплит, выдающий список ключей родительской категории
         * @param null $cid
         * @param null $term
         * @throws CHttpException
         */
        public function actionParentKeys($cid = null, $term = null)
        {
                $this->layout = false;

                if(!Yii::app()->request->isAjaxRequest || !$cid)
                        throw new CHttpException(404);

                $cat = Category::model()->findByPk((int)$cid);
                $parent_cat = $cat->parent()->find();

                $options = Yii::app()->db->createCommand("SELECT t.id, t.name, t.key FROM `cat_option` t WHERE t.key LIKE '" . CHtml::encode($term) . "%' AND t.category_id = " . $parent_cat->id)->queryAll();
                $results = array();

                foreach($options as $option) {
                        $results[] = array(
                                'label'=>$option['key'] . ' (' . $option['name'] . ')',
                                'value'=>$option['key'],
                                'id'=>$option['id'],
                        );
                }
                die(CJSON::encode($results));
        }

        /**
         * Добавляет к набору опций категории опцию ее родителя
         * @param integer $category_id - текущая категория
         * @param integer $option_id - опция родителя
         */
        public function actionUseParentOption($option_id = null, $parent_option_id = null)
        {
                $this->layout = false;

                if(!Yii::app()->request->isAjaxRequest || !$option_id)
                        throw new CHttpException(404);

                $option = Option::model()->findByPk((int)$option_id);
                $parent_option = Option::model()->findByPk((int)$parent_option_id);

                if(!$parent_option || !$option)
                        throw new CHttpException(404);

                $option->setScenario('useParent');
                $option->parent_id = $parent_option->id;
                $option->save();


                $response = array(
                        'html'=>$this->renderPartial('_optionRow', array('model'=>$option, 'groups'=>$option->category->groupsArray), true),
                        'result'=>true,
                );

                die(CJSON::encode($response));
        }

        /**
         * Создание группы опций категории
         * @throws CHttpException
         */
        public function actionGroupCreate()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $category = $this->loadModel((int) Yii::app()->request->getParam('category_id'));
                $group_name = Yii::app()->request->getParam('group_name');

                if(!$group_name)
                        throw new CHttpException(404);

                $groups = $category->groupsArray;
                $gid = time();
                $groups[$gid] = $group_name;
                $category->groups = serialize($groups);
                $category->saveNode(false, array('groups'));

                $category->groups;

                die(CJSON::encode(array(
                        'success'=>true,
                        'html'=>'<li>'.CHtml::textField("[$gid]group", $group_name, array('disabled'=>true)).' '.
                                CHtml::tag('span', array("class"=>"group_delete", "gid"=>$gid), 'удал.').'</li>',
                )));
        }

        /**
         * Удаление группы опций категории
         * @throws CDbException
         * @throws CHttpException
         */
        public function actionGroupDelete()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $category = $this->loadModel((int) Yii::app()->request->getParam('category_id'));
                $gid = Yii::app()->request->getParam('gid');

                $groups = $category->groupsArray;

                if(isset($groups[$gid]) || is_null($groups[$gid]))
                        unset($groups[$gid]);

                $category->groups = serialize($groups);
                $category->saveNode(false, array('groups'));

                die(CJSON::encode(array(
                        'success'=>true,
                )));
        }

        /**
         * Проверяет возможность установки опции типа Габариты как фильтруемой
         * @throws CHttpException
         */
        public function actionSizeOptionFilterCheck()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $option = Option::model()->findByPk((int) Yii::app()->request->getParam('oid'));
                $category = $this->loadModel(Yii::app()->request->getParam('cid'));

                if(!$category || !$option || $option->category_id != $category->id)
                        throw new CHttpException(404);

                $params = $category->getParamsArray();

                if(isset($params['filterable_'.$option->type_id]))
                        $sizes = $params['filterable_'.$option->type_id];
                else
                        $sizes = array();

                $sizes_qt = count($sizes);

                if($sizes_qt >= Option::MAX_FILTERABLE_OPTION_QT)
                        die(CJSON::encode(array(
                                'success'=>false,
                                'message'=>'Опцию нельзя использовать в фильтре. Максимально допустимое кол-во опций данного типа, разрешенных для использования в фильтре: ' . Option::MAX_FILTERABLE_OPTION_QT,
                        )));
                else
                        die(CJSON::encode(array('success'=>true)));
        }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Category::model()->findByPk((int) $id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

        /**
         * Сортировка опций и значений опций
         * @param $id
         */
        public function actionSort($id)
        {
                $model=$this->loadModel($id);

                if(Yii::app()->request->isAjaxRequest)
                        die(json_encode(array(
                                'id'=>$model->id,
                                'name'=>$model->name,
                                'groups'=>$model->getGroupsArray(),
                        ), JSON_NUMERIC_CHECK));

                $this->render('sort',array(
                        'model'=>$model,
                ));
        }

        public function actionSortApi($class)
        {
                $command = Yii::app()->db->createCommand();

                /**
                 * Получение объектов
                 */
                if(Yii::app()->request->isPutRequest || Yii::app()->request->isPostRequest) {

                        $data = RESTfulHelper::getPutJson();

                        switch ($class) {
                                case 'Options' :
                                        $option = Option::model()->findByPk((int) $data['id']);
                                        if(!$option)
                                                RESTfulHelper::getStatusCodeMessage(404, 'Option not found');
                                        $option->position = (int) $data['position'];
                                        $option->save(false, array('position'));
                                        RESTfulHelper::sendResponse(200, json_encode(array('id'=>$option->id), JSON_NUMERIC_CHECK));

                                case 'Values' :
                                        $value = Value::model()->findByPk((int) $data['id']);
                                        if(!$value)
                                                RESTfulHelper::getStatusCodeMessage(404, 'Value not found');
                                        $value->position = (int) $data['position'];
                                        $value->save(false, array('position'));
                                        RESTfulHelper::sendResponse(200, json_encode(array('id'=>$value->id), JSON_NUMERIC_CHECK));
                        }

                } else {

                        switch ($class) {
                                case 'Options' :
                                        $category_id = Yii::app()->request->getParam('category_id');
                                        $options = $command->select('id, key, name, position')->from(Option::model()->tableName())
                                                ->where('category_id=:cid', array(':cid'=>(int) $category_id))
                                                ->order('position')->queryAll();
                                        RESTfulHelper::sendResponse(200, json_encode($options, JSON_NUMERIC_CHECK));

                                case 'Values' :
                                        $option_id = Yii::app()->request->getParam('option_id');
                                        $values = $command->select('id, value, position')->from(Value::model()->tableName())
                                                ->where('option_id=:oid and product_id is null', array(':oid'=>(int) $option_id))
                                                ->order('position')->queryAll();
                                        RESTfulHelper::sendResponse(200, json_encode($values, JSON_NUMERIC_CHECK));
                        }
                }
        }

        /**
         * Автокомлит для категорий
         * @param $term
         * @throws CHttpException
         */
        public function actionAcCategory($term)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $data = Yii::app()->db->createCommand("SELECT t.id, t.name FROM `cat_category` t WHERE t.name LIKE '" . CHtml::encode($term) . "%'")->queryAll();
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
	 * Страница настроек для категорий.
	 * Список помещений и популярных урлов только для листьев
	 */
	public function actionSettings()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );

		/** @var $model Category */
		$model = $this->loadModel($id);
		$mainRooms = MainRoom::model()->findAllByAttributes(array('status'=>MainRoom::STATUS_ENABLED));

		if ( $request->getIsPostRequest() && $model->isLeaf() ) {
			$sRooms = $request->getParam('CategoryRoom', array());
			CategoryRoom::updateRooms($sRooms, $model->id);
		} else {
			$sRooms = CategoryRoom::getSelectedRooms($model->id);
		}
		$parent = $model->parent()->find();

		$this->render('settings', array(
			'model'=>$model,
			'mainRooms'=>$mainRooms,
			'sRooms'=>$sRooms,
			'parent'=>$parent,
		));
	}

	/**
	 * Загрузка изображений на fileApi
	 * @param $cid - Category id
	 */
	public function actionUpload($cid)
	{
		$category = Category::model()->findByPk(intval($cid));
		if (is_null($category))
			die( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$category->setImageType('category');

		$image = UploadedFile::loadImage($category, 'file', '');

		if ($image) {
			$category->image_id = $image->id;
			$category->saveNode(false);

			die( json_encode( array('success'=>true, 'src'=>'/'.$image->getPreviewName(Category::$preview['crop_120'])), JSON_NUMERIC_CHECK ) );
		} else {
			die( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );
		}
	}

}
