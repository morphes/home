<?php

class ProductController extends FrontController
{
	public function beforeAction($action)
	{
		$mall = Cache::getInstance()->mallBuild;
		if ($mall instanceof MallBuild) {
			$this->menuIsActiveLink           = false;
			$this->menuIsActiveLinkOnlyParent = false;
			$this->menuActiveKey = null;
		} else {
			$this->menuActiveKey              = 'product_catalog1';
			$this->menuIsActiveLink           = true;
			$this->menuIsActiveLinkOnlyParent = false;
		}

		$this->bodyClass = 'goods goods-item';
		Yii::app()->clientScript->registerCssFile('/css-new/generated/goods.css');
		Yii::app()->clientScript->registerScriptFile('/js-new/catalog2.js');

		return parent::beforeAction($action);
	}


	/**
	 * @var объект запрошенного товара
	 */
	private $_model;


	public function filters()
	{
		return array(
			'ajaxAction + createFeedback, deleteFeedback, ',
		);
	}


	/**
	 * Фильтр для ajax экшнов
	 *
	 * @param $filterChain
	 *
	 * @throws CHttpException
	 */
	public function filterAjaxAction($filterChain)
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
			Yii::log(Yii::app()->request->userAgent . "\n" . Yii::app()->request->userHost, CLogger::LEVEL_ERROR);
			throw new CHttpException(400);
		}

		$this->layout = false;

		$filterChain->run();
	}


	/**
	 * Карточка товара
	 * @param $id
	 */
	public function actionIndex($id, $cid = false)
	{
		// Получаем ТЦ по поддомену
		if ($mall = Cache::getInstance()->mallBuild) {
			return $this->actionIndexOld($id);
		}

        if ($cid) {
            $this->redirect(Product::getLink($id), true, 301);
        }

		$store = false;
		$stores = array();
		$inStore = false;
		$storesOnline = array();

		$viewName = '//catalog2/product/index';
		$this->bodyClass = 'goods goods-item';
		$this->layout = '//layouts/grid_main';
		$storesInUserCity = false;

		/** @var $model Product Запрашиваемый товар */
		$model = $this->loadModel($id);

		// Задачем канонический URL
		$this->canonicalUrl = $this->createAbsoluteUrl($model->getLink($model->id));

		//пробуем получить store_id
		$storeId = (int)Yii::app()->request->getParam('store_id');

		$city = City::model()->findByPk((int)$cid);

		$feedbacks = new CActiveDataProvider('Feedback', array(
			'criteria' => array(
				'condition' => 'product_id=:pid and parent_id is null',
				'join'      => 'left join myhome.user author on author.id=user_id',
				'order'     => 't.create_time DESC',
				'params'    => array(':pid' => $model->id),
				'limit'     => 4,
			),
		));
		$feedbacks->getData();

		$userCity = Yii::app()->user->getSelectedCity();
		if (!$userCity) {
			$userCity = Yii::app()->user->getDetectedCity();
		}

		// Получаем список всех ID городов, в которых есть текущий товар
		$citiesWithProduct = $this->getStoreCities($model);

		/*/**
		 * Сортировка результатов по алфавиту
		 * Нужно для вывода магазинов в других
		 * городах
		 */
		$sorted = array();
		$count = 0;
		foreach ($citiesWithProduct as $item) {
			$key = mb_substr($item['name'], 0, 1, 'UTF-8');

			if (!array_key_exists($key, $sorted))
				$count = 0;

			$sorted[$key]['data'][] = $item;
			$sorted[$key]['count'] = ++$count;
		}
		//Если установлен store_id то выводим в карточке
		//товаров только этот магазин
		if ($storeId) {
			$store = Store::model()->findByPk($storeId);
			// Наращиваем просмотры товаров, как товара текущего магазина
			StatStore::hit($storeId, StatStore::TYPE_HIT_OWN_PRODUCT);

			// Наращиваем просмотры товаров, как товара из общей массы
			StatStore::hit($storeId, StatStore::TYPE_HIT_COMMON_PRODUCT);

			//Блок похожие товары для магазинов с платными тарифами.
			$relatedItemsData = array();
			//И ставим флаг на наличие товара в городе пользователя
			//что бы не отработало условие на вывод отсутсвия товара
			//в городе пользователя
			$storesInUserCity = true;
			$inStore = true;
		} //Если установлен город то получаем магазины из него
		//В обход проверки города пользователя
		elseif ($city) {
			// Получаем магазины по прямой связке через StorePrice
			$stores = self::getStoresByStorePrice($city->id, $model->id, 1000, $mall);
			$storesInUserCity = true;
			$store = reset($stores);
		} else {
			StatStore::hitAllStores($model->id);
			// и флаг о том, что город пользователя есть среди этого списка.
			foreach ($citiesWithProduct as $ct) {
				if ($ct['id'] == $userCity->id)
					$storesInUserCity = true;
			}

			// если товар не продается в городе пользователя, то
			// ищем ближайший город, в котором есть товар
			// и добавляем его для вывода в блоке "Где купить"
			if (!$storesInUserCity && $userCity->lat && $userCity->lng) {

				// минимальное расстояние до ближайшего города с магазином
				$min_distance_swp = null;
				// ключ ближайшего города в массиве городов, продающих товар ($citiesWithProduct)
				$min_distance_swp_key = null;

				foreach ($citiesWithProduct as $swp_key => $swp) {

					// пропуск городов, координаты которых не известны
					if (!$swp['lat'] || !$swp['lng'])
						continue;

					// сохранение расстояния до города в общий массив
					$citiesWithProduct[$swp_key]['distance'] = YandexMap::distance($userCity->lat, $userCity->lng, $swp['lat'], $swp['lng']);

					// если расстояние до текущего города меньше, чем то, что было ранее определено минимальным,
					// то обновляем информацию о минимальном расстоянии и городе с минимальной удаленностью,
					if ($citiesWithProduct[$swp_key]['distance'] < $min_distance_swp || is_null($min_distance_swp_key)) {
						$min_distance_swp = $citiesWithProduct[$swp_key]['distance'];
						$min_distance_swp_key = $swp_key;
					}
				}
			}

			// Перечень городов (в порядке приоритета), по которым нужно будет искать магазины
			// В него попадает Город пользователя, Москва, и Случайный город
			if ($storesInUserCity) {
				$resCity[$userCity->id] = $userCity->id;
			} else {
				@$resCity[$citiesWithProduct[$min_distance_swp_key]['id']] = $citiesWithProduct[$min_distance_swp_key]['id'];
			}

			// добавляем Москву для вывода (всегда)
			$resCity[City::ID_MOSCOW] = City::ID_MOSCOW;

			$stores = array();

			$relatedItemsData = array();
			$maxResultStores = 100;

			foreach ($resCity as $rc) {

				// Получаем магазины по прямой связке через StorePrice
				foreach (self::getStoresByStorePrice($rc, $model->id, $maxResultStores, $mall) as $key => $store) {
					$stores[$key] = $store;
				}

				// Получаем магазины по прямой связке через StorePrice
				foreach (self::getStoriesOnline($rc, $model->id, $maxResultStores) as $key => $store) {
					$storesOnline[$key] = $store;
				}

				// Если набрали уже магазинов
				if (!empty($stores)) {
					// Ограничиваем выборку по магазинам
					if (count($stores) > $maxResultStores)
						$stores = array_slice($stores, 0, $maxResultStores);

					$city = City::model()->findByPk($rc);
					break;
				}
			}

			$store = reset($stores);
		}
		//Если нет оффлайн магазинов то вставляем онлайн
		if(!$store) {
			$store = reset($storesOnline);
		}


		$cityId = ($city instanceof City) ? $city->id : 0;
		$categoryId = $model->category->id;

		$storeList = StoreGeo::getStoreList($cityId);
		$storeCount = 0;
		if (!empty($storeList)) {

			$sql = 'SELECT COUNT(*) FROM {{store2}} WHERE `status`='.Store::STATUS_ACTIVE.' AND id IN ('.implode(',', $storeList).') '
				.$this->_getCategoryFilter($categoryId). ' GROUP BY `status`';
			$storeCount = Yii::app()->sphinx->createCommand($sql)->queryScalar();

		} elseif (empty($cityId)) {

			$sql = 'SELECT COUNT(*) FROM {{store2}} WHERE `status`='.Store::STATUS_ACTIVE.' '.$this->_getCategoryFilter($categoryId). ' GROUP BY `status`';
			$storeCount = Yii::app()->sphinx->createCommand($sql)->queryScalar();
		}


		/**
		 * Рендеринг карточки товара
		 */

		$this->render($viewName, array(
			'model'            => $model,
			'stores'           => $stores,
			'store'            => $store,
			'feedbacks'        => $feedbacks,
			'storesInUserCity' => $storesInUserCity,
			'selectedCity'     => $userCity,
			'sorted'           => $sorted,
			'inStore'          => $inStore,
			'storesOnline'     => $storesOnline,
			'storeCount'       => $storeCount
		));
	}

	/**
	 * Возвращает кусок условия WHERE для SQL запроса с фильтрацией
	 * по категории.
	 *
	 * @param $category_id
	 *
	 * @return string
	 */
	private function _getCategoryFilter($category_id)
	{
		// Ограничение по категории
		$whereCategory = '';
		if ($category_id > 0) {

			$ids = $this->_getCategoryIds($category_id);

			if (!empty($ids)) {
				$whereCategory = ' AND category_ids IN (' . implode(',', $ids) . ')';
			}

		}

		return $whereCategory;
	}

	/**
	 * Возвращает массив категорий, в которых непосредственно лежат товары.
	 *
	 * @param $category_id
	 *
	 * @return array
	 */
	private function _getCategoryIds($category_id)
	{
		$ids = array();

		/** @var $model Category */
		$cat = Category::model()->findByPk((int)$category_id);

		if($cat) {
			if (abs($cat->lft - $cat->rgt) == 1) {
				// Если это конченая категория
				$ids = array($cat->id);
			} else {
				// Если категория не конечная, берем список вложенных узлов.
				$children = $cat->getLastDescendants();
				if ($children) {
					foreach ($children as $ch) {
						$ids[] = $ch['id'];
					}
				}
			}

			return $ids;
		} else {
			throw new CHttpException(404);
		}


	}


	/**
	 * Старая карточка товара
	 * Пока используется для
	 * каталога Бм
	 * @param $id
	 */
	public function actionIndexOld($id)
	{
		/** @var $model Product Запрашиваемый товар */
		$model = $this->loadModel($id);

		//Флаг на то будет ли отображен функционал добавления
		//В папку
		$addToFolder = false;

		// Получаем ТЦ по поддомену
		$mallBuild = Cache::getInstance()->mallBuild;

		$this->layout = '//layouts/layoutBm';
		$this->bodyClass = 'bm-promo goods-item';

		$this->hide_div_content = true;

		if (Yii::app()->user->id == $mallBuild->admin_id) {
			$addToFolder = true;
		}

		$feedbacks = new CActiveDataProvider('Feedback', array(
			'criteria' => array(
				'condition' => 'product_id=:pid and parent_id is null',
				'with'      => array('author'),
				'order'     => 't.create_time DESC',
				'params'    => array(':pid' => $model->id),
				'limit'     => 4,
			),
		));
		$feedbacks->getData();

		$mallBuild = Cache::getInstance()->mallBuild;
		$resCity = array();

		$resCity[] = $mallBuild->city_id;
		$storesInUserCity = true;


		$store = Store::model()->findByPk((int)Yii::app()->request->getParam('store_id'));
		$city = null;

		// ==> если в get параметрах указан store_id, то выводим только его в блоке "Где купить"
		if ($store) {

			$stores = array($store);

			// Наращиваем просмотры товаров, как товара текущего магазина
			StatStore::hit($store->id, StatStore::TYPE_HIT_OWN_PRODUCT);

			// Наращиваем просмотры товаров, как товара из общей массы
			StatStore::hit($store->id, StatStore::TYPE_HIT_COMMON_PRODUCT);

			//Блок похожие товары для магазинов с платными тарифами.
			$relatedItemsData = array();

			//Если тариф не бесплатный то выводим блок похожие товары
			if ($store->tariff_id != Store::TARIF_FREE) {
				$relatedItemsData = Product::model()->getRelatedProducts($model->category_id, $store->id, $model->id);
			}
		} else {
			// =>> иначе ищем все магазины, продающие товар в городе пользователя

			/* Наращиваем просмотры товаров для всех магазинов,
			 * которые продают текущий товар
			 */
			StatStore::hitAllStores($model->id);

			$stores = array();
			$relatedItemsData = array();
			$maxResultStores = 5;

			foreach ($resCity as $rc) {

				// Получаем магазины по прямой связке через StorePrice
				foreach (self::getStoresByStorePrice($rc, $model->id, $maxResultStores, $mallBuild) as $key => $store) {
					$stores[$key] = $store;
				}

				// Если набрали уже магазинов
				if (!empty($stores)) {
					// Ограничиваем выборку по магазинам
					if (count($stores) > $maxResultStores)
						$stores = array_slice($stores, 0, $maxResultStores);

					$city = City::model()->findByPk($rc);
					break;
				}
			}
		}


		// Наращиваем счетчик просмотра
		$model->incrementView();


		/**
		 * Рендеринг карточки товара
		 */
		$this->render('//catalog2/product/bmIndex', array(
			'model'            => $model,
			'feedbacks'        => $feedbacks,
			'city'             => $city,
			'storesInUserCity' => $storesInUserCity,
			'stores'           => $stores,
			'store_id'         => Yii::app()->request->getParam('store_id'),
			'relatedItemsData' => $relatedItemsData,
			'addToFolder'      => $addToFolder,
		));
	}


	/**
	 * Возвращает ассоциативный массив магазинов для товара $product_id и в городе $city_id
	 *
	 * @param $city_id           integer Инденификатор города
	 * @param $product_id        Integer Индентификатор товара
	 * @param $max_result_stores Максимльное количество магазинов в результате выборке
	 *
	 * @return array Массив магазинов, проиндексированный ID магазинов.
	 */
	private function getStoresByStorePrice($city_id, $product_id, $max_result_stores, $mall = null)
	{
		if (is_null($mall)) {
			// Ищем магазины по указанному товару и городу
			$join = 'LEFT JOIN cat_store_price csp ON csp.store_id = t.id '
				. 'LEFT JOIN cat_store_city as sc ON sc.store_id=t.id';
			$stores = Store::model()->findAll(array(
				'select'    => 't.*',
				'condition' => 'sc.city_id = :cid AND csp.product_id = :pid AND t.type=:type',
				'join'      => $join,
				'params'    => array(':cid' => $city_id, ':pid' => $product_id, ':type' => Store::TYPE_OFFLINE),
				'limit'     => $max_result_stores,
				'order'     => 'mall_build_id desc, tariff_id desc, csp.by_vendor asc, -csp.price asc, name asc',
				'index'     => 'id',
			));
		} else {
			// Ищем магазины по указанному товару и городу
			$join = 'LEFT JOIN cat_store_price csp ON csp.store_id = t.id '
				. 'LEFT JOIN cat_store_city as sc ON sc.store_id=t.id';
			$stores = Store::model()->findAll(array(
				'select'    => 't.*',
				'condition' => 'sc.city_id = :cid AND csp.product_id = :pid AND t.mall_build_id = :mid AND t.type=:type',
				'join'      => $join,
				'params'    => array(':cid' => $city_id, ':pid' => $product_id, ':mid' => $mall->id, ':type' => Store::TYPE_OFFLINE),
				'limit'     => $max_result_stores,
				'order'     => 'tariff_id desc, csp.by_vendor asc, -csp.price asc, name asc',
				'index'     => 'id',
			));
		}

		return $stores;
	}


	/**
	 * Метод возвразает онлайн магазины в городе
	 * @param $city_id
	 * @param $product_id
	 * @param $max_result_stores
	 *
	 * @return array|CActiveRecord|mixed|null
	 */
	private function  getStoriesOnline($city_id, $product_id, $max_result_stores)
	{
		$join = 'LEFT JOIN cat_store_price csp ON csp.store_id = t.id '
			. 'LEFT JOIN cat_store_city as sc ON sc.store_id=t.id';
		$stores = Store::model()->findAll(array(
			'select'    => 't.*',
			'condition' => 'sc.city_id = :cid AND csp.product_id = :pid AND t.type=:type',
			'join'      => $join,
			'params'    => array(':cid' => $city_id, ':pid' => $product_id, ':type' => Store::TYPE_ONLINE),
			'limit'     => $max_result_stores,
			'order'     => 'tariff_id desc, csp.by_vendor asc, -csp.price asc, name asc',
			'index'     => 'id',
		));

		return $stores;
	}


	/**
	 * Возвращает ассоциативный массив магазинов для производителя $vendor_id в городе $city_id.
	 *
	 * @param $city_id           integer Идентфикатор города
	 * @param $vendor_id         integer Идентификатор производителя
	 * @param $max_result_stores integer Максимальное количество магазинов в результате выборки.
	 *
	 * @return array Массив магазинов, проиндексированный ID магазинов.
	 */
	private function getStoresByVendors($city_id, $vendor_id, $max_result_stores, $mall = null)
	{
		if (is_null($mall)) {
			// Ищем магазины по указанному производителю и городу
			$stores = Store::model()->findAll(array(
				'select'    => 't.*, mbs.mall_build_id',
				'condition' => 'city_id=:cid AND sv.vendor_id=:vid',
				'join'      => 'LEFT JOIN cat_store_vendor sv ON sv.store_id=t.id'
				. ' LEFT JOIN mall_build_store mbs ON t.id = mbs.store_id',
				'params'    => array(':cid' => $city_id, ':vid' => $vendor_id),
				'limit'     => $max_result_stores,
				'order'     => 'tariff_id desc',
				'index'     => 'id'
			));
		} else {
			// Ищем магазины по указанному производителю и городу с учетом Торгового Центра
			$stores = Store::model()->findAll(array(
				'select'    => 't.*, mbs.mall_build_id',
				'condition' => 'city_id=:cid AND sv.vendor_id=:vid AND mbs.mall_build_id = :mid',
				'join'      => 'LEFT JOIN cat_store_vendor sv ON sv.store_id=t.id'
				. ' INNER JOIN mall_build_store mbs ON mbs.store_id = t.id',
				'params'    => array(':cid' => $city_id, ':vid' => $vendor_id, ':mid' => $mall->id),
				'limit'     => $max_result_stores,
				'index'     => 'id',
				'order'     => 'tariff_id desc',
			));
		}

		return $stores;
	}


	/**
	 * Расширенная карточка товара (Характеристики)
	 * @param $id
	 */
	public function actionDescription($id)
	{
		/** @var $model Product Запрошенный товар */
		$model = $this->loadModel($id);

		// Получаем ТЦ по поддомену
		$mall = Cache::getInstance()->mallBuild;
		if ($mall instanceof MallBuild) {
			$this->bodyClass = 'bm-promo goods-item';
			$this->layout = '//layouts/layoutBm';
			$viewName = '//catalog2/product/bmDescription';

		} else {
			//Редирект на новую карточку товара
			$this->redirect($model->getElementLink(), true, 301);
		}

		// Наращиваем счетчик просмотра
		$model->incrementView();

		/* Наращиваем просмотры товаров для всех магазинов,
		 * которые продают текущий товар
		 */
		StatStore::hitAllStores($model->id);

		/**
		 * Рендеринг карточки товара
		 */
		$this->render($viewName, array(
			'model'    => $model,
			'store_id' => Yii::app()->request->getParam('store_id'),
		));
	}


	/**
	 * Обсуждения товара (комментарии)
	 */
	public function actionComment($id)
	{
		/** @var $model Product Запрошенный товар */
		$model = $this->loadModel($id);

		// Получаем ТЦ по поддомену
		if ($mall = Cache::getInstance()->mallBuild) {
			$this->layout = '//layouts/layoutBm';
			$this->bodyClass = 'bm-promo goods-item';

			$viewName = '//catalog2/product/bmComment';
		} else {
			//Редирект на новую карточку товара
			$this->redirect($model->getElementLink(), true, 301);
		}


		// Наращиваем счетчик просмотра
		$model->incrementView();

		/* Наращиваем просмотры товаров для всех магазинов,
		 * которые продают текущий товар
		 */
		StatStore::hitAllStores($model->id);

		/**
		 * Рендеринг страницы обсуждения товара товара
		 */
		$this->render($viewName, array(
			'model'    => $model,
			'store_id' => Yii::app()->request->getParam('store_id'),
		));
	}


	/**
	 * Отзывы о товаре
	 */
	public function actionFeedback($id)
	{
		/** @var $model Product Запрашиваемый товар */
		$model = $this->loadModel($id);
		$feedback = new Feedback();

		// Получаем ТЦ по поддомену
		if ($mall = Cache::getInstance()->mallBuild) {

			$this->layout = '//layouts/layoutBm';
			$this->bodyClass = 'bm-promo goods-item';

			$viewName = '//catalog2/product/bmFeedback';
		} else {
			//Редирект на новую карточку товара
			$this->redirect($model->getElementLink(), true, 301);
		}


		/**
		 * Инициализация текущего pagesize
		 * если pagesize нет в куках, то записывается стандартное значение pagesize в куки.
		 * если pagesize есть в куках, то проверяется ее корректность. в случае некорректного значения
		 * в куки записывается дефолтный pagesize в куки
		 */
		$cook_pagesize = Yii::app()->request->cookies['product_feedback_pagesize'];
		if (!$cook_pagesize) {
			$pagesize = Feedback::DEFAULT_PAGESIZE;
		} elseif ($cook_pagesize && !isset(Config::$productFeedbackPageSizes[$cook_pagesize->value])) {
			$pagesize = Feedback::DEFAULT_PAGESIZE;
		} else {
			$pagesize = $cook_pagesize->value;
		}

		$cook_sort = Yii::app()->request->cookies['product_feedback_sort'];
		if (!$cook_sort)
			$sort = 'date_desc';
		else $sort = $cook_sort->value;
		$sort = explode('_', $sort);
		$order = isset($sort[1]) ? $sort[1] : 'DESC';
		switch ($sort[0]) {
			case 'date':
				$sortString = 't.create_time ' . $order;
				break;
			case 'mark' :
				$sortString = 't.mark ' . $order;
				break;
			default:
				$sortString = 't.create_time DESC';
				break;
		}


		$feedbacks = new CActiveDataProvider('Feedback', array(
			'criteria'   => array(
				'condition' => 'product_id=:pid and parent_id is null',
				'with'      => array('author'),
				'order'     => $sortString,
				'params'    => array(':pid' => $model->id),
			),
			'pagination' => array(
				'pageSize' => (int)$pagesize,
			),
		));
		$feedbacks->getData();


		// Наращиваем счетчик просмотра
		$model->incrementView();

		/* Наращиваем просмотры товаров для всех магазинов,
		 * которые продают текущий товар
		 */
		StatStore::hitAllStores($model->id);

		/**
		 * Рендеринг страницы обсуждения товара товара
		 */
		$this->render($viewName, array(
			'model'     => $model,
			'feedback'  => $feedback,
			'feedbacks' => $feedbacks,
			'pagesize'  => $pagesize,
			'sort'      => $sort,
			'store_id'  => Yii::app()->request->getParam('store_id'),
		));
	}


	/**
	 * Создание фидбека
	 * @param $id
	 */
	public function actionCreateFeedback($id)
	{
		/**
		 * Получение объекта комментируемого товара
		 */
		$model = $this->loadModel($id);
		$feedback = new Feedback('feedback');

		if (isset($_POST['Feedback']) && !$model->checkFeedback) {
			$feedback->attributes = $_POST['Feedback'];
			$feedback->user_id = Yii::app()->user->id;
			$feedback->product_id = $model->id;
			if ($feedback->save()) {
				$html = $this->renderPartial('//catalog2/product/_feedbackItemIndexPageGrid', array('data' => $feedback), true);
				die(CJSON::encode(array('success' => true, 'errors' => array(), 'html' => $html)));
			} else {
				$errors = array();
				foreach ($feedback->getErrors() as $error) {
					if (!empty($error[0]))
						$errors[] = $error[0];
				}
				die(CJSON::encode(array('success' => false, 'errors' => $errors)));
			}
		}


		die(CJSON::encode(array('success' => false, 'errors' => array('Неизвестная ошибка'))));
	}


	/**
	 * Создание и редактирование ответа на фидбек
	 */
	public function actionFeedbackAnswer()
	{
		$feedbackId = Yii::app()->request->getParam('commentid');
		$message = Yii::app()->request->getParam('message');
		$answerId = Yii::app()->request->getParam('answerid');

		if (!$feedbackId || !$message)
			die(json_encode(array('success' => false, 'message' => 'Некорректный запрос')));

		$feedback = Feedback::model()->findByPk((int)$feedbackId);
		if (!$feedback)
			die(json_encode(array('success' => false, 'message' => 'Отзыва, на который вы отвечаете, не существует')));

		$model = $this->loadModel($feedback->product_id);
		if (!$model)
			die(json_encode(array('success' => false, 'message' => 'Товара, для которого оставлен отзыв, не существует')));

		if (!$model->isSeller)
			die(json_encode(array('success' => false, 'message' => 'Недостаточно прав для создания ответов на отзывы о данном товаре')));

		// редактирование ответа
		if ($answerId) {
			$answer = Feedback::model()->findByAttributes(array('id' => (int)$answerId, 'user_id' => Yii::app()->user->id));
			if (!$answer || $answer->parent_id != $feedback->id)
				die(json_encode(array('success' => false, 'message' => 'Редактируемого ответа не существует')));
			// создание нового ответа
		} else {
			if (Feedback::model()->exists('parent_id=:pid and user_id=:uid', array(':pid' => $feedback->id, ':uid' => Yii::app()->user->id)))
				die(json_encode(array('success' => false, 'message' => 'Нельзя оставлять более одного ответа на отзыв')));

			$answer = new Feedback('answer');
			$answer->parent_id = $feedback->id;
			$answer->product_id = $model->id;
		}

		$answer->message = CHtml::encode($message);
		$answer->user_id = Yii::app()->user->id;

		$html = $this->_generateAnswerLabel(Yii::app()->user->model->name, $feedback->product_id, $answer->message, $answer->user_id);

		if ($answer->save())
			die(json_encode(array('success' => true, 'answerId' => $answer->id, 'html' => $html)));
		else
			die(json_encode(array('success' => false, 'message' => 'Внутренняя ошибка сервера')));
	}


	/**
	 * Вспомогательная функция
	 * Генерит html лейбл для вывода его перед сообщением ответа на отзыв
	 *
	 * @param $userName
	 * @param $productId
	 * @param $message
	 *
	 * @return string
	 */
	protected function _generateAnswerLabel($userName, $productId, $message, $adminId, $showControls = true)
	{
		$userStoresName = Yii::app()->dbcatalog2->createCommand()->select('name')
			->from('cat_store s')
			->join('cat_store_price sp', 'sp.store_id=s.id and sp.product_id=:pid', array(':pid' => $productId))
			->where('admin_id=:aid', array(':aid' => $adminId))
			->limit(1)
			->queryScalar();

		$storeLabel = '';
		if ($userStoresName)
			$storeLabel = ', представитель магазина «' . $userStoresName . '»';

		$html = '<span>' . $userName . $storeLabel . ':</span> '
			. '<span class="text">' . $message . '</span>';

		if (Yii::app()->user->id == $adminId && $showControls)
			$html .= '<div class="admin_controls">'
				. '<span class="edit"><i></i></span><span class="del"><i></i></span></div>';

		return $html;
	}


	/**
	 * Удаление ответа на фидбек
	 */
	public function actionDeleteFeedbackAnswer()
	{
		$feedbackId = Yii::app()->request->getParam('commentid');
		$answerId = Yii::app()->request->getParam('answerid');

		if (!$feedbackId || !$answerId)
			die(json_encode(array('success' => false, 'message' => 'Некорректный запрос')));

		$feedback = Feedback::model()->findByPk((int)$feedbackId);
		if (!$feedback)
			die(json_encode(array('success' => false, 'message' => 'Отзыва, для которого удаляется ответ, не существует')));

		$model = $this->loadModel($feedback->product_id);
		if (!$model)
			die(json_encode(array('success' => false, 'message' => 'Товара, для которого оставлен отзыв, не существует')));

		if (!$model->isSeller)
			die(json_encode(array('success' => false, 'message' => 'Недостаточно прав для удаления ответов на отзывы о текущем товаре')));

		$answer = Feedback::model()->findByAttributes(array('id' => (int)$answerId, 'user_id' => Yii::app()->user->id));
		if (!$answer || $answer->parent_id != $feedback->id)
			die(json_encode(array('success' => false, 'message' => 'Удаляемого ответа не существует')));

		$answer->delete();

		die(json_encode(array('success' => true)));
	}


	public function actionStores($id)
	{
		// если в строке get параметров есть store_id, то редиректим на карту выбора магазинов с отмеченным магазином, переданным get'ом
		$store_id = Yii::app()->request->getParam('store_id');
		if ($store_id) {
			$store = Store::model()->findByPk((int)$store_id);
			if ($store)
				$this->redirect($this->createUrl('/product', array('id' => $id, 'action' => 'storesInCity', 'store_id' => $store->id, 'cid' => $store->city_id)), true, 301);
		}

		/** @var $model Product Запрошенный товар */
		$model = $this->loadModel($id);

		// Получаем ТЦ по поддомену
		if ($mall = Cache::getInstance()->mallBuild) {
			$this->bodyClass = 'bm-promo';
			Yii::app()->clientScript->registerCssFile('/css-new/generated/bm.css');

			$this->hide_div_content = true;

			$viewName = '//catalog2/product/bmStores';
		} else {
			//Редирект на новую карточку товара
			$this->redirect($model->getElementLink(), true, 301);
		}


		$cities = $this->getStoreCities($model);

		/**
		 * Сортировка результатов по алфавиту
		 */
		$sorted = array();
		$count = 0;
		foreach ($cities as $item) {
			$key = mb_substr($item['name'], 0, 1, 'UTF-8');

			if (!array_key_exists($key, $sorted))
				$count = 0;

			$sorted[$key]['data'][] = $item;
			$sorted[$key]['count'] = ++$count;
		}

		// Наращиваем счетчик просмотра
		$model->incrementView();

		/**
		 * Рендеринг карточки товара
		 */
		$this->render($viewName, array(
			'model'    => $model,
			'sorted'   => $sorted,
			'store_id' => Yii::app()->request->getParam('store_id'),
		));
	}


	public function actionStoresInCity($id, $cid)
	{
		/** @var $model Product Запрошенный товар */
		$model = $this->loadModel($id);

		// Получаем ТЦ по поддомену
		if ($mall = Cache::getInstance()->mallBuild) {
			$this->layout = '//layouts/layoutBm';
			$this->bodyClass = 'bm-promo goods-item';

			$viewName = '//catalog2/product/bmStoresInCity';
		} else {
			//Редирект на новую карточку товара
			$this->redirect($model->getElementLink(), true, 301);
		}

		$city = City::model()->findByPk((int)$cid);
		if (!$city)
			throw new CHttpException(404);

		// если в строке get параметров есть store_id, то отображаем только этот магазин
		$store = Store::model()->findByPk((int)Yii::app()->request->getParam('store_id'));
		if ($store && $store->city) {
			$stores = array($store);
			$city = $store->city;
			$cities[$city->id] = $city->name;
			// иначе отображаем все магазины в указанном городе
		} else {


			// Получаем магазины по прямой связке через StorePrice
			$stores = self::getStoresByStorePrice($city->id, $model->id, 1000, $mall);

			$cities = array();
			$allowedCity = false;

			/**
			 * Формирование выпадающего списка городов, в которых продают данный товар
			 */
			foreach ($this->getStoreCities($model) as $ct) {
				$cities[$ct['id']] = $ct['name'];
				if ($ct['id'] == $cid)
					$allowedCity = true;
			}

			if (!$allowedCity)
				throw new CHttpException(404);
		}

		/**
		 * Формирование координат для карты
		 */
		$mapData = array();
		foreach ($stores as $store) {
			if ($store->geocode) {
				$geocode = unserialize($store->geocode);
				if ($geocode && is_array($geocode) && isset($geocode[0]) && isset($geocode[1]))
					$mapData[] = array(
						'coord'         => array($geocode[0], $geocode[1]),
						'baloonContent' => $store->getBaloonContent(),
					);
			}
		}


		// Наращиваем счетчик просмотра
		$model->incrementView();

		/* Наращиваем просмотры товаров для всех магазинов,
		 * которые продают текущий товар
		 */
		StatStore::hitAllStores($model->id);


		$this->render($viewName, array(
			'model'    => $model,
			'stores'   => $stores,
			'cities'   => $cities,
			'city'     => $city,
			'mapData'  => $mapData,
			'store_id' => Yii::app()->request->getParam('store_id'),
		));
	}


	/**
	 * Возвращает города, в которых есть магазины, продающие товар.
	 * Список магазинов собирается по двум принципам:
	 * - по прямой связки через cat_store_price
	 * - через производителя
	 *
	 * @param $model - объект товара
	 *
	 * @return array
	 */
	private function getStoreCities($model)
	{

		// По привязкам цен cat_store_price
		$ids = Yii::app()->dbcatalog2->createCommand()->from('cat_store_price c')
			->select('DISTINCT (store_id)')
			->join('cat_store cs', 'cs.id=c.store_id')
			->where('c.product_id = :pid AND cs.type=:type', array(':pid' => $model->id, ':type' => Store::TYPE_OFFLINE))->queryColumn();


		$ids = implode(',', $ids);

		if ($ids)
			$cities = Yii::app()->dbcatalog2->createCommand()
				->select('c.id, c.name, c.lat, c.lng, count(s.store_id) as qt')
				->from(City::model()->tableName() . ' c')
				->join('cat_store_city s', 's.city_id=c.id')
				->where('c.id is not null AND s.store_id in (' . $ids . ')')
				->order('c.name')->group('c.id')->queryAll();
		else
			$cities = array();


		return $cities;
	}

	public function actionGetGalleryAjax()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$post = Yii::app()->request->getPost('item');

		$id = (int)$post['modelId'];

		$model = Product::model()->findByPk($id);


		if(!$model) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$html = $this->renderPartial('//catalog2/product/_gallery',array('model' => $model), true);

		die (json_encode( array('success'=>true, 'html'=>$html) ));
	}


	/**
	 * Возвращает объект товара (в случае если он активен)
	 * @param $id
	 *
	 * @return CActiveRecord
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model = Product::model()->findByPk((int)$id);
		if ($model === null || $model->status != Product::STATUS_ACTIVE)
			throw new CHttpException(404);

		return $model;
	}

    public function actionMapData($productId, $cityId, $limit=5)
    {
        $city = City::model()->findByPk($cityId);
        $product = Product::model()->findByPk($productId);

        if (!$city || !$product) {
            throw new CHttpException(404);
        }

        $stores = $this->_getStoresForProductInCity($productId, $cityId, $limit);
        $mapPoints = $this->_getMapPointsForStores($stores);
        $popupCityStores = CFormatterEx::formatNumeral(count($stores), array('магазин', 'магазина', 'магазинов')) . ' ' . $city->genitiveCase;
        $storesInCityMini = $this->renderPartial('_storesInCityMini', ['stores'=>$stores, 'city'=>$city, 'model'=>$product], true);
        $storesInCityFull = $this->renderPartial('_storesInCityFull', ['stores'=>$stores, 'city'=>$city, 'model'=>$product], true);

        die(json_encode([
            'points'=>$mapPoints,
            'popupCityStores'=>$popupCityStores,
            'currentCityName'=>$city->prepositionalCase,
            'storesInCityMini'=>$storesInCityMini,
            'storesInCityFull'=>$storesInCityFull,
        ]));
    }

    protected function _getStoresForProductInCity($productId, $cityId, $limit=100)
    {
        $stores = [];
        // Получаем магазины по прямой связке через StorePrice
        foreach (self::getStoresByStorePrice($cityId, $productId, $limit) as $key => $store) {
            $stores[$key] = $store;
        }
        // Если набрали уже магазинов
        if (!empty($stores)) {
            // Ограничиваем выборку по магазинам
            if (count($stores) > $limit) {
                $stores = array_slice($stores, 0, $limit);
            }
        }
        return $stores;
    }

    /**
     * Возвращает массив точек с текстом для балуна
     * @param array $stores - магазины, которые необходимо вывести на карте
     * @return array
     */
    protected function _getMapPointsForStores(array $stores)
    {
        $mapPoints = [];
        foreach($stores as $store) {
            if (!($store instanceof Store) || !$store->geocode) {
                continue;
            }
            $geocode = @unserialize($store->geocode);
            if (!$geocode || !is_array($geocode) || !isset($geocode[0]) || !isset($geocode[1])) {
                continue;
            }
            $mapPoints[] = [
                'coord' => [$geocode[0], $geocode[1]],
                'baloonContent' => $store->getBaloonContent(),
            ];
        }
        return $mapPoints;
    }
}