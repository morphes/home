<?php

class CategoryController extends FrontController
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
			$this->menuIsActiveLinkOnlyParent = true;
		}
                return parent::beforeAction($action);
        }

	/**
	 * Просмотр товаров каталога
	 *
	 * @param integer $id - ID категории
	 */
	public function actionList($id = 0)
	{
		//Флаг на то будет ли отображен функционал добавления
		//В папку
		$addToFolder = false;
		// Для БОльшой медведицы вызываем старый метод.
		$mall = Cache::getInstance()->mallBuild;

		$this->layout = '//layouts/new_main';

		/** @var $category Category */
		$category = Category::model()->findByPk((int)$id);

		if (!$category instanceof Category) {
			// Получаем root
			$category = Category::model()->findByPk(1);
		}

		$city = Cache::getInstance()->city;


		if ($category->name != 'root') {
			// Если есть активная категория, получаем кусок дерева
			$categories = Category::getCategoriesWithActive($category, $city);
		} else {
			// Если нет активной категории, получаем узлы 1-го уровня
			$categories = Category::model()
				->getRoot()
				->children()
				->findAll(
					'status = :active',
					array(':active' => Category::STATUS_OPEN)
				);
		}


		/* -------------------------------------------------------------
		 *  Выбранные опции товара из фильтра
		 * -------------------------------------------------------------
		 */

		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();

		$selected = array(
			'vendors'        => $request->getParam('vendors'),
			'rooms'          => $request->getParam('rooms'),
			'style'          => $request->getParam('style'),
			'colors'          => $request->getParam('colors'),
			'vendor_country' => (int)$request->getParam('vendor_country'),
			'price_from'     => $request->getParam('price_from', 0),
			'price_to'       => $request->getParam('price_to', $category->getMaxPrice())
		);

		if ($selected['price_from'] > $selected['price_to']) {
			$tmp = $selected['price_to'];
			$selected['price_to'] = $selected['price_from'];
			$selected['price_from'] = $tmp;
		}


		/* -------------------------------------------------------------
		 *  Получаем данные из ДатаПровайдера
		 * -------------------------------------------------------------
		 */
		$floatRanges['price'] = array();

		$floatRanges['price']['from'] = (float)$selected['price_from'];

		if ((float)$selected['price_to'] > 0) {
			$floatRanges['price']['to'] = (float)$selected['price_to'];
		}

		// Получаем данные для провайдера, попутно дополняем $selected
		$providerOptions = $this->_getOptionsForProvider($selected, $category);

		$pageSize = $this->_getPageSizeForProvider();

		$sphinxClient = Yii::app()->search;
		$dataProvider = new CSphinxDataProvider($sphinxClient, array(
			'index'            => 'product2',
			'modelClass'       => 'Product',
			'matchMode'        => SPH_MATCH_EXTENDED,
			'sortMode'         => SPH_SORT_EXTENDED,
			'sortExpr'         => $this->_getSortStringForProvider(),
			'filters'          => $this->_getFilterForProvider($category, $selected),
			'filterRange'      => $providerOptions['ranges'],
			'filterFloatRange' => $floatRanges,
			'query'            => $providerOptions['query'],
			'pagination'       => array('pageSize' => $pageSize),
		));

		// Обработка случая ничего не найдено в городе, фильтр пуст и не ajax
		if (
			$dataProvider->getTotalItemCount() == 0
			&& $city instanceof City
			&& count($_GET) == 1
			&& !$request->getIsAjaxRequest()
		) {

			$htmlEmpty = $this->_getHtmlForEmptyCat($category, $city, $mall);

		} else {
			$htmlEmpty = $this->renderPartial('_listNotFound', array(), true);

		}

		if ($mall && $dataProvider->getTotalItemCount() == 0) {
			$htmlEmpty = '<div class="-col-9"><p class="-large -gutter-top">'
				.'К сожалению, в нашем каталоге еще нет товаров из ТВК «Большая Медведица».<br>
				      Рекомендуем вам посмотреть <a onclick="CCommon.setUrl(\'products\'); ">каталог всех товаров</a>, представленных на MyHome'
				.'</p></div>';
		}


		/* -------------------------------------------------------------
		 *  Рендерим представление
		 * -------------------------------------------------------------
		 */

		// Получаем ТЦ по поддомену
		if ($mall)
		{
			$this->layout = '//layouts/layoutBm';
			$this->bodyClass = 'goods goods-category bm-promo folders';
			$viewName = '//catalog2/category/bmListGrid';
			$viewType = intval( $request->getParam('view_type', 2) );

			if(Yii::app()->getUser()->role==User::ROLE_MALL_ADMIN)
			{
				if(Yii::app()->getUser()->getId() == $mall->admin_id)
				{
					$addToFolder = true;
				}
			}

			$city = null;
			$categories = $this->_clearEmptyCategory($categories, $mall, $category->id);

		} else {
			$viewType = intval( $request->getParam('view_type', 2) );

			$this->bodyClass = 'goods goods-category ';
			$viewName = '//catalog2/category/listGrid';
		}

		// Определяем тип отображения (большие или маленькие карточки)
		if (!in_array($viewType, array(1,2))) {
			$viewType = 2;
		}


		$cityId = ($city instanceof City) ? $city->id : 0;
		$categoryId = ($category instanceof Category) ? $category->id : 0;

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



		return $this->render($viewName, array(
			'storeCount'  => $storeCount,
			'categories'  => $categories,
			'city'        => $city,
			'selected'    => $selected,
			'products'    => $dataProvider,
			'viewType'    => $viewType,
			'category'    => $category,
			'pageSize'    => $pageSize,
			'addToFolder' => $addToFolder,
			'htmlEmpty'   => $htmlEmpty
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
	 * Метод удалаяет из массива категорий те, внутри которых нет ни одного
	 * товара для торгового центра $mall
	 *
	 * @param $categories Category[]
	 * @param $mall MallBuild
	 * @param $activeCategoryId integer Идентификатор активной категории
	 *
	 * @return array
	 */
	private function _clearEmptyCategory($categories, $mall, $activeCategoryId)
	{
		if (!is_array($categories) || empty($categories)) {
			return array();
		}


		$result = $qnt = Yii::app()->cache->get(Category::getCacheKeyCategoryMall($mall->id, $activeCategoryId));

		if (!$result) {

			$result = array();

			// Список всех непустых категорий ТЦ.
			$notEmpty = Category::getNotEmptyByMall($mall->id);

			// Проходим список полученных категорий и решаем, нужно
			// ли их оставлять в реузльтате.
			foreach ($categories as $cat) {

				$found = false;

				// Если категория конечная, то саму ее нужно проверить
				// в списке допустимых
				if ($cat->isLeaf()) {

					if (in_array($cat->id, $notEmpty)) {
						$found = true;
					}

				} else {
					/* Если категория не конечная, то ее наличие
					   в списке разрешено в случае, если какой-то из
					   ее детей есть в списке разрешенных */

					$lastChild = $cat->getLastDescendants();

					foreach ($lastChild as $child) {
						$found = $found || in_array($child['id'], $notEmpty);
						if ($found == true) {
							break;
						}
					}
				}


				if ($found == true) {
					$result[] = $cat;
				}
			}


			Yii::app()->cache->set(
				Category::getCacheKeyCategoryMall($mall->id, $activeCategoryId),
				$result,
				Cache::DURATION_DAY
			);
		}

		return $result;
	}


        /**
         * Обработчик запросов на отображение списка категорий (при нажатии на стрелку в breadcrumbs каталога товаров)
         * @param $category_id - категория, по стрелке которой был произведен клик
         * @throws CHttpException
         */
        public function actionAjaxLoadChilds($category_id)
        {
                $this->layout = false;

                if(!Yii::app()->getRequest()->getIsAjaxRequest())
                        throw new CHttpException(400);

		/** @var $model Category */
		$model = $this->loadModel($category_id);

                /** Получение корня дерева категорий */
                $root = Category::getRoot();

                /**
                 * Если клик произошел по стрелке "Товары", сразу формируется ответ
                 * с контентом вида основных разделов сайта
                 */
                if($root->id == $model->id) {
                        $html = $this->renderPartial('_ajaxLoadChildsSitelinks',array(), true);
                        die(CJSON::encode(array('success'=>true, 'html'=>$html)));
                }

		$categoryList = null;
		// Костыль для моллов
		$mall = Cache::getInstance()->mallBuild;
		if ( $mall instanceof MallBuild ) {
			$categoryList = Category::getNotEmptyByMall($mall->id);
		} else {
			$city = Yii::app()->getUser()->getSelectedCity();
			if ($city instanceof City) { // Фильтр по наличию товара и по городу
				$categoryList = Category::getNotEmptyByCity($city->id);
			}
		}

		/** @var $categories массив категорий на одном уровне с текущей */
		if ( $categoryList===null ) {
			$categories = Category::model()->findAll(array(
				'condition' => 'rgt-lft=1 AND product_qt>0 AND status='.Category::STATUS_OPEN,
				'order' => 'lft ASC',
			));
		} elseif ( !empty($categoryList) ) {
			$categories = Category::model()->findAll(array(
				'condition' => 'id IN ('.implode(',', $categoryList).') AND status='.Category::STATUS_OPEN,
				'order' => 'lft ASC',
			));
		} else {
			$categories = array();
		}

		$html = $this->renderPartial('_ajaxLoadChildsItem', array('categories'=>$categories, 'currentId'=>$model->id), true);

		die( json_encode( array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK) );
        }

        /**
         * Возвращает html список ckeckboxes производителей в указанной стране
         * @throws CHttpException
         */
        public function actionAjaxVendorsList()
        {
                $this->layout = false;

                $country_id = (int) Yii::app()->request->getParam('country_id');
                $category_id = (int) Yii::app()->request->getParam('category_id');

                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $vendors = Vendor::getVendorsByCountry($country_id, $category_id);

                die(CJSON::encode(array(
                        'success'=>true,
                        'html'=>CHtml::checkBoxList('vendors', '', CHtml::listData($vendors, 'id', 'name'), array('class'=>'textInput')))));
        }

        /**
         * Возвращает объект категории
         * @param $id
         * @return CActiveRecord
         * @throws CHttpException
         */
        public function loadModel($id)
        {
                $model=Category::model()->findByPk((int) $id);
                if($model===null)
                        throw new CHttpException(404);
                return $model;
        }


	/**
	 * Возвращает строку, определяющую способ соритровки данных в дата
	 * провайдере.
	 *
	 * @return string
	 */
	private function _getSortStringForProvider()
	{
		$cook_sort = Yii::app()->request->cookies['product_filter_sort'];

		$sort = $cook_sort ? $cook_sort->value : 'price';
		$sort = explode('_', $sort);

		$order = isset($sort[1]) ? $sort[1] : 'ASC';

		// заплатка для красноярска
		if ( $sort[0] == 'default' && isset(Cache::getInstance()->city->id) && Cache::getInstance()->city->id == 4149) {
			return 'prt_by_cat DESC, sort_rand DESC, sort_default DESC';
		}

		switch ($sort[0]) {
			case 'date':
				$sortString = 'sort_date ASC, create_time ' . $order;
				break;
			case 'price':
				$sortString = 'price '.$order;
				break;
			case  'default':
				$sortString = 'sort_default DESC'; // добавить , create_time DESC (когда придумают нормальную приоритезацию)
				break;
			default:
				$sortString = '@weight DESC';
				break;
		}

		return $sortString;
	}


	/**
	 * Возвращает массив определяющий фильтрацию провайдера.
	 * 
	 * @param Category|null  $category Выбранная пользователем категория 
	 * @param array $selected Массив параметров фильтра.
	 *
	 * @return array Ассоциативный массив для передачи его в DataProvider
	 */
	private function _getFilterForProvider($category = null, $selected = array())
	{
		$filters = array();

		// Получаем ТЦ по поддомену
//		if ($mall = Cache::getInstance()->mallBuild)
//		{
//			$filters['mall_ids'] = $mall->id;
//		} else {
//			// фильтр по городам
//			/** @var $city City */
//			$city = Cache::getInstance()->city;
//			if ($city instanceof City) {
//				$filters['store_ids'] = StoreGeo::getStoreList($city->id);
//
//				if (empty($filters['store_ids'])) {
//					$filters['store_ids'] = array(0 => 0);
//				}
//			}
//		}

        $city = Cache::getInstance()->city;
        if ($city instanceof City) {
        $filters['city_ids'] = $city->id;
        }


		if ($category->isLeaf()) {
			$filters['category_id'] = $category->id;

		} else {

			$res = $category->getLastDescendants();
			if ($res) {
				foreach ($res as $item) {
					$filters['category_id'][] = $item['id'];
				}
			}
		}
		/*echo '<pre>';
		var_dump($filters);
		exit();*/


		if ($selected['vendor_country'] && empty($selected['vendors'])) {

			/* Выборка по всем производителям в стране (для сео),
			 * если не выбран ни один вендор, но выбрана страна */
			$filters['vendor_id'] = array_map(
				'intval',
				Vendor::getVendorsByCountry(
					$selected['vendor_country'],
					$category->id,
					true
				)
			);

			// Если ничего не найдено, удаляем из фильтра, чтобы
			// дата провайдер не упал
			if (empty($filters['vendor_id'])) {
				unset($filters['vendor_id']);
			}

		} elseif ($selected['vendors']) {

			// Иначе выбор по выбранным вендорам
			$filters['vendor_id'] = array_map('intval', $selected['vendors']);
		}


		return $filters;
	}


	/**
	 * Возвращает текущий pageSize для дата провайдера.
	 *
	 * @return int
	 */
	private function _getPageSizeForProvider()
	{
		$cook_page_size = Yii::app()->request->cookies['product_filter_pagesize'];
		if (!$cook_page_size) {

			// если список от ТЦ, то по-умолчанию выводим по 90 товаров
			if ( Cache::getInstance()->mallBuild )
				$pageSize = 90;
			else
				$pageSize = Product::DEFAULT_PAGESIZE;

		} elseif ($cook_page_size && !isset(Config::$productFilterPageSizes[$cook_page_size->value])) {
			$pageSize = Product::DEFAULT_PAGESIZE;

		} else {
			$pageSize = $cook_page_size->value;
		}
		
		return intval($pageSize);
	}


	/**
	 * @param array $selected Массив выбранных параметров в фильтре. Получаем
	 * 	по ссылке. В процессе обработки модифицируем этот массив.
	 * @param Category $category Выбранная пользователем категория.
	 *
	 * @return array array('ranges' => array, 'query' => string)
	 */
	private function _getOptionsForProvider(&$selected, $category)
	{
		$result = array(
			'ranges' => array(),
			'query' => ''
		);

		/* -------------------------------------------------------------
		 *  Инициализация опций товаров для текущей категории
		 *  и условий фильтрации по ним
		 * -------------------------------------------------------------
		 */

		$request = Yii::app()->getRequest();

		if ($category->isLeaf())
		{
			$params = $category->getParamsArray();

			foreach ($category->getDaoOptions() as $option) {

				/*
				 * Получение значения текущей опции из фильтра
				 */
				$option_value = $request->getParam($option['key']);

				/*
				 * Если из фильтра не пришло данных для опции - переход к следующей
				 * Текущая в фильтрации товаров не участвует
				 */
				if (is_null($option_value) || $option_value == '') {
					continue;
				}

				if ($option_value == -1) {
					$option_value = 0;
				}

				$option_key = $option['key'];
				$selected[$option_key] = $option_value;

				/**
				 * Формирование условия фильтрации для Multiple оцпии
				 * Добавляет условик фильтрации для каждого из значений опции, переданных из фильтра
				 */
				if ($option['type_id'] == Option::TYPE_SELECTMULTIPLE
					|| $option['type_id'] == Option::TYPE_COLOR
				) {
					if (!is_array($option_value)) {
						continue;
					}

					$option_value = array_map('intval', $option_value);
					$tmp = array();
					foreach ($option_value as $mval) {
						$tmp[] = $option_key . ':' . $mval;
					}
					$queryArray[] = '( "' . implode('" | "', $tmp) . '" )';

				} elseif (
					$option['type_id'] == Option::TYPE_SIZE
					&& isset($params['filterable_' . $option['type_id']])
					&& in_array($option['id'], $params['filterable_' . $option['type_id']])
				) {
					if (!is_array($option_value)) {
						continue;
					}
					if ($option_value['to'] == '' && $option_value['from'] == '') {
						continue;
					}
					/* Если конечный размер не указан,
					ставим неявно большое значение */
					if ($option_value['to'] == '') {
						$option_value['to'] = 10000;
					}


					if (intval($option_value['from']) > intval($option_value['to'])) {
						$from = (int)$option_value['to'];
						$to = (int)$option_value['from'];
					} else {
						$from = (int)$option_value['from'];
						$to = (int)$option_value['to'];
					}

					$selected[$option_key] = array('from' => $from, 'to' => $to);
					$opt_val_field = array_search(
						$option['id'],
						$params['filterable_' . $option['type_id']]
					);
					$result['ranges'][$opt_val_field] = array('from' => $from, 'to' => $to);

				} else {

					if (!is_array($option_value)) {
						$option_value = CHtml::encode($option_value);
						$queryArray[] = '"' . $option_key . ':' . $option_value . '"';
					}

				}
			}
		}



		if ( !is_null($selected['rooms']) && $selected['rooms'] != -1 ) {
			$queryArray[] = '"' . 'room' . ':' . intval($selected['rooms']) . '"';
		}

		// поиск по стилям товаров
		if ( !is_null($selected['style']) ) {
			$style = Yii::app()->dbcatalog2->createCommand()->select('name')
				->from(Style::model()->tableName())->where('id=:id', array(':id'=>(int)$selected['style']))->queryScalar();

			if ($style)
				$result['query'].= ' @styles "' . $style .'"';
		}

		// поиск по цветам товаров
		if ( !is_null($selected['colors']) && is_array($selected['colors']) ) {
			$colors = CatColor::getAll(true);
			$color_names = array();
			foreach (array_map('intval', $selected['colors']) as $color_id) {
				if ( !isset($colors[$color_id]) )
					continue;
				$color_names[] = '"' . $colors[$color_id] . '"';
			}
			$result['query'].= ' @colors ( ' . implode(' | ', $color_names) . ' ) ';
		}

		if ( isset($queryArray) && !empty($queryArray) ) {
			$result['query'].= ' @options ( ' . implode(' & ', $queryArray) . ' ) ';
		}

		//var_dump($result['query']);die();

		return $result;
	}


	/**
	 * Возвращает html код для случая, когда в выбранной категории не найдено
	 * товаров. Выводится или предложение в других городах или инфа о том,
	 * что товаров еще нет.
	 *
	 * @param $category Category
	 * @param $city City
	 * @param $mall MallBuild
	 *
	 * @return string HTML код
	 */
	private function _getHtmlForEmptyCat($category, $city, $mall)
	{
		$htmlOut = '';

		$cacheKey = 'NOTFOUND_PRODUCTS2:' . $category->id . ':' . $city->region_id;

		$cities = Yii::app()->cache->get($cacheKey);
		
		if (!is_array($cities) || empty($cities) ) {
			$cityList = City::model()->findAll(
				array(
					'condition' => 'region_id=:rid AND t.id<>:id',
					'index'     => 'id',
					'params'    => array(':id' => $city->id, ':rid' => $city->region_id),
				)
			);

			if (empty($cityList)) {
				$htmlOut = $this->renderPartial(
					'_listNotFound',
					array('model' => $category, 'city' => $city),
					true
				);
				goto the_end;
			}



			$cities = array();


			// Получаем список категорий
			$categoryIds = array();
			if ($category->isLeaf()) {
				$categoryIds[] = $category->id;
			} else {
				$res = $category->getLastDescendants();
				foreach ($res as $r) {
					$categoryIds[] = $r['id'];
				}
			}

			/** @var $cityItem City */
			$presql = 'SELECT COUNT(*) as qt FROM {{product2}}'
				. ' WHERE store_ids IN (:stores) AND category_id IN (:catids)'
				. ' GROUP by category_id LIMIT 200';
			foreach ($cityList as $cityItem)
			{
				// Вставляем в запрос идентификаторы категорий
				$sql = str_replace(':catids', implode(',', $categoryIds), $presql);

				$storeList = StoreGeo::getStoreList($cityItem->id);
				$data = array();
				if (!empty($storeList)) {
					$sql = str_replace(':stores', implode(',', $storeList), $sql);
					$data = Yii::app()
						->sphinx
						->createCommand($sql)
						->queryAll();
				}


				if ($data) {
					$sum = 0;
					foreach ($data as $d) {
						$sum += $d['qt'];
					}
					
					$cities[$cityItem->rating] = array(
						'city'  => $cityItem,
						'count' => $sum
					);
				}
			}
			unset($cityList);
			ksort($cities);
			Yii::app()->cache->set($cacheKey, $cities, Cache::DURATION_HOUR);
		}

		if (empty($cities)) {

			$htmlOut = $this->renderPartial(
				'_listNotFound',
				array(
					'model' => $category,
					'city'  => $city,
					'mall'  => $mall
				),
				true
			);
			goto the_end;

		} else {

			$htmlOut = $this->renderPartial(
				'_listFound',
				array(
					'cities' => $cities,
					'model'  => $category,
					'city'   => $city,
					'mall'   => $mall
				),
				true
			);
			goto the_end;
		}


		the_end:
		return $htmlOut;
	}
}