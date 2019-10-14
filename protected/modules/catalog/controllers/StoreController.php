<?php

class StoreController extends FrontController
{
	/**
         * Список магазинов
         */
        public function actionIndex($id)
        {
		if(preg_match("/^\d+$/", $id) == 0) {
			throw new CHttpException(404);
		}

		/** @var $model Store */
		$model = $this->loadModel($id);

                $feedbacks = new CActiveDataProvider('StoreFeedback', array(
                        'criteria'=>array(
                                'condition'=>'store_id=:sid and parent_id=0',
                                'with'=>array('author'),
                                'order'=>'t.create_time desc',
                                'params'=>array(':sid'=>$model->id),
                                'limit'=>10,
                        ),
                ));
                $feedbacks->getData();


		/*
		 * Редиректим на красивый урл для магазина с тарифом "Минисайт"
		 */
		if ($model->tariff_id == Store::TARIF_MINI_SITE && $model->subdomain) {

			$this->redirect(Yii::app()->createAbsoluteUrl(
				'catalog/store/moneyIndex',
				array('sub' => $model->subdomain->domain)
			));
		}

		// Наращиваем счетчик просмотра
		$model->incrementView();


		// Наращиваем счетчик просмотра
		StatStore::hit($model->id, StatStore::TYPE_HIT_STORE);

		//Блок похожие магазины
		// Если магазин визитка то выводи похожие магазины
		$relatedShops = array();
		if($model->tariff_id == Store::TARIF_FREE) {
			$relatedShops = $this->_getRelatedShops($model,6);
		}


		$mall = Cache::getInstance()->mallBuild;
		if ($mall instanceof MallBuild) {
			$this->layout = '//layouts/layoutBm';
			$this->bodyClass = 'bm-promo goods-item';
			$viewName = '//catalog/store/bm/index';
		} else {
			$viewName = '//catalog/store/index';
		}


		$this->render($viewName, array(
			'model'        => $model,
			'feedbacks'    => $feedbacks,
			'isOwner'      => $model->isOwner(Yii::app()->user->id),
			'relatedShops' => $relatedShops,
                ));
        }


	/**
	 * Список магазинов на карте
	 */
	public function actionListMap()
	{
		$this->menuActiveKey              = 'product_catalog';
		$this->menuIsActiveLink           = true;
		$this->menuIsActiveLinkOnlyParent = true;


		$this->layout = '//layouts/new_main';
		$this->bodyClass = 'goods shop-list';



		// Получаем город
		$city = Cache::getInstance()->city;
		if ($city instanceof City) {
			$cityId = $city->id;
		} else {
			$cityId = 0;
		}


		// Получаем выбранную категорию
		$categoryId = (int)Yii::app()->request->getParam('cid');

		/* -------------------------------------------------------------
		 *  Получение категорий товаров
		 * -------------------------------------------------------------
		 */
		$categories = $this->_getCategories($cityId);


		$shops = $this->_getShopsForMap($cityId, $categoryId);

		$seoOptimize = $this->_seoOptimize($cityId, $categoryId);

		$this->render('//catalog/store/listmap', array(
			'city'        => $city,
			'cid'         => $categoryId,
			'categories'  => $categories,
			'shops'       => $shops,
			'seoOptimize' => $seoOptimize
		));
	}

	/**
	 * Список магазинов разедленный по буквам и списком категорий.
	 */
	public function actionList()
	{
		$this->menuActiveKey              = 'product_catalog';
		$this->menuIsActiveLink           = true;
		$this->menuIsActiveLinkOnlyParent = true;


		$this->layout = '//layouts/new_main';
		$this->bodyClass = 'goods shop-list';



		// Получаем город
		$city = Cache::getInstance()->city;
		if ($city instanceof City) {
			$cityId = $city->id;
		} else {
			$cityId = 0;
		}

		// Получаем выбранную категорию
		$categoryId = (int)Yii::app()->request->getParam('cid');



		/* -------------------------------------------------------------
		 *  Получение категорий товаров
		 * -------------------------------------------------------------
		 */
		$categories = $this->_getCategories($cityId);

		/* -------------------------------------------------------------
		 *  Получение платных магазинов
		 * -------------------------------------------------------------
		 */
		$moneyShops = $this->_getMoneyShops($cityId, $categoryId, 100);

		/* -------------------------------------------------------------
		 *  Получение списка магазинов по буквам
		 * -------------------------------------------------------------
		 */

		// Итоговый массив магазинов, который заполняется _makeShops()
		$shops = array();


		// Ограничение на вывод количества магазинов в блоках по буквам.
		if ($city instanceof City) {
			$shopsLimit = 1000;
		} else {
			$shopsLimit = 14;
		}


		// Собираем магазины на русские буквы
		$this->_makeShops($shops, Store::$rus, 'rus', $cityId, $categoryId, $shopsLimit);

		// Собираем магазины на латинские буквы
		$this->_makeShops($shops, Store::$eng, 'eng', $cityId, $categoryId, $shopsLimit);

		// Собираем магазины на цифрам
		$this->_makeShops($shops, Store::$num, 'num', $cityId, $categoryId, $shopsLimit);

		// Собираем магазины на спецсимволы
		$this->_makeShops($shops, Store::$spec, 'spec', $cityId, $categoryId, $shopsLimit);


		$seoOptimize = $this->_seoOptimize($cityId, $categoryId);

		$storeList = StoreGeo::getStoreList($cityId);
		$storeCount = 0;
		if (!empty($storeList)) {
			$sql = 'SELECT COUNT(*) FROM {{store}} WHERE `status`='.Store::STATUS_ACTIVE.' AND id IN ('.implode(',', $storeList).') '
				.$this->_getCategoryFilter($categoryId). ' GROUP BY `status`';
			$storeCount = Yii::app()->sphinx->createCommand($sql)->queryScalar();
		} elseif (empty($cityId)) {
			$sql = 'SELECT COUNT(*) FROM {{store}} WHERE `status`='.Store::STATUS_ACTIVE.' '.$this->_getCategoryFilter($categoryId). ' GROUP BY `status`';
			$storeCount = Yii::app()->sphinx->createCommand($sql)->queryScalar();
		}

		$this->render('//catalog/store/list', array(
			'storeCount' => $storeCount,
			'city'        => $city,
			'cid'         => $categoryId,
			'categories'  => $categories,
			'shops'       => $shops,
			'moneyShops'  => $moneyShops,
			'seoOptimize' => $seoOptimize,
			'shopsLimit'  => $shopsLimit
		));
	}


	/**
	 * ФОрмирует специфические h1, title, description и keywords
	 * согласно переданным городу и категории
	 *
	 * @param $cityId Идентификатор города
	 * @param $categoryId Идентификатор категории товаров.
	 *
	 * @return array Ассоциативный массив содержащий h1, title, description,
	 * keywords
	 */
	private function _seoOptimize($cityId, $categoryId)
	{
		/** @var $category Category */
		$category = Category::model()->findByPk((int)$categoryId);
		$city = City::model()->findByPk((int)$cityId);


		$h1 = $title = $description = $keywords = '';

		if ($city) {
			$h1 = 'Магазины ' . $city->genitiveCase;

			$title = 'Товары для дома и ремонта — Магазины в ' . $city->prepositionalCase . ' — MyHome.ru';

			$description = 'Список магазинов ' . $city->genitiveCase . ', где можно купить товары для дома,'
				. ' ремонта и благоустройства. Поиск магазинов по названию или адресу,'
				. ' карта расположения магазинов в ' . $city->prepositionalCase . '.';

			$keywords = 'магазины ' . $city->name . ', товары для дома, товары для ремонта, майхоум, myhome, май хоум, myhome.ru';
		}


		if ($category && !$city) {

			$title = $category->name . ' — Магазины — MyHome.ru';

			$description = 'Список магазинов, где можно купить ' . mb_strtolower($category->accusativeCase, 'UTF-8')
				. ' с каталогом товаров и адресной информацией. Поиск магазинов по'
				. ' названию или адресу, карта расположения магазинов по городам.';

			$keywords = $category->name . ' магазины, ' . $category->name . ' купить,'
				. ' ' . $category->name .' продажа, ' . $category->name . ', товары для дома,'
				. ' товары для ремонта, майхоум, myhome, май хоум, myhome.ru';
		}


		if ($category && $city) {

			if ($category->level == 2) {
				$h1 = 'Магазины ' . mb_strtolower($category->genitiveCase, 'UTF-8') . ' в ' . $city->prepositionalCase;
			} else {
				$h1 = $category->name . ' — ' . 'магазины в ' . $city->prepositionalCase;
			}

			$title = $category->name . ' — Магазины в ' . $city->prepositionalCase . ' — MyHome.ru';

			$description = 'Список магазинов ' . $city->genitiveCase . ', где можно купить'
				. ' ' . mb_strtolower($category->accusativeCase, 'UTF-8') . '  с каталогом товаров и адресной информацией.'
				. ' Поиск магазинов по названию или адресу, карта расположения магазинов в ' . $city->prepositionalCase . '.';

			$keywords = $category->name . ' магазины, ' . $category->name . ' новосибирск, '
				. $category->name . ' купить, ' . $category->name . ', магазины ' . $city->name
				.', товары для дома, товары для ремонта, майхоум, myhome, май хоум, myhome.ru';
		}


		return array(
			'h1'          => $h1,
			'title'       => $title,
			'description' => $description,
			'keywords'    => $keywords
		);
	}


	/**
	 * Возвращает список непустых категорий с учетом возможного города
	 *
	 * @param $cityId Идентификатор города
	 *
	 * @return array
	 */
	private function _getCategories($cityId)
	{
		$categoryList = Category::getNotEmptyByCity((int)$cityId);


		$sql = 'SELECT DISTINCT cc.* FROM cat_category as cc ';
		if (!empty($categoryList)) {
			$sql .= 'INNER JOIN ( '
				.'SELECT DISTINCT t.id, t.lft, t.rgt FROM `cat_category` `t` '
				.'WHERE t.id IN ('.implode(',', $categoryList).') '
				.') as tmp ON tmp.id=cc.id OR (tmp.lft>cc.lft AND tmp.rgt<cc.rgt) ';
		}
		$sql .= 'WHERE cc.level<>1 '
			.'ORDER BY cc.lft';

		$data = Yii::app()->db->createCommand($sql)->queryAll();
		$categories = Category::model()->populateRecords($data);

		return $categories;
	}

	/**
	 * Функция собирает магазины в массив по буквам
	 *
	 * @param array $shops Итоговый массив магазинов
	 * @param array $symbols Массив символов, по которым нужно собрать магазины
	 * @param string $lang Строка-название типа букв
	 * @param City $cityId Идентификатор город
	 * @param integer $category_id Идентификатор категории
	 * @param int $limit Кол-во магазинов, которое собирается по каждому символу
	 */
	private function _makeShops(&$shops, $symbols, $lang, $cityId, $category_id, $limit = 14)
	{
		if ($lang == 'spec' || $lang == 'num') {

			// Обрабатываем все символы как один набор

			$ids = array_map('crc32', $symbols);

			$sql = "SELECT id, is_chain, chain_id, COUNT(*) chain_qt FROM {{store}}"
				. " WHERE "
				. "	type=" . Store::TYPE_OFFLINE . " and "
				. "	first_letter IN (" . implode(',', $ids) . ")"
				. $this->_getCityFilter($cityId)
				. $this->_getCategoryFilter($category_id)
				. ' GROUP BY chain_id'
				. " LIMIT " . $limit;

			$res = Yii::app()->sphinx
				->createCommand($sql)
				->queryAll();


			if ($symbols[0] == '0') {
				$s = '0-9';
			} else {
				$s = $symbols[0];
			}

			$shops[] = array(
				'lang'   => $lang,
				'symbol' => $s,
				'crc32'  => crc32($symbols[0]),
				'ids'    => $res
			);

		} else {

			// Обрабатываем каждый символ отдельно

			foreach ($symbols as $s) {

				// Обрабатываем одиночные буквы алфавитов
				$crc32 = crc32($s);

				$sql = "SELECT id, is_chain, chain_id, COUNT(*) chain_qt FROM {{store}}"
					. " WHERE "
					. "	type=".Store::TYPE_OFFLINE." and "
					. " 	first_letter = :f"
					. $this->_getCityFilter($cityId)
					. $this->_getCategoryFilter($category_id)
					. ' GROUP BY chain_id'
					. " LIMIT " . $limit;

				$res = Yii::app()->sphinx
					->createCommand($sql)
					->bindParam(':f', $crc32)
					->queryAll();

				$shops[] = array(
					'lang'   => $lang,
					'symbol' => $s,
					'crc32'  => $crc32,
					'ids'    => $res
				);
			}

		}
	}


	/**
	 * Возвращает список магазинов с необходимыми данными для вывода
	 * на крате.
	 *
	 * @param $cityId Индентификатор города
	 * @param $categoryId
	 *
	 * @return array
	 */
	private function _getShopsForMap($cityId, $categoryId, $text = '')
	{
		$STEP = 10;
		$index = 0;


		// Ключ для кеша, в который складывается список все полученных магазинов.
		$keyCache = 'ShopsForMap' . $cityId . $categoryId;

		$result = Yii::app()->cache->get($keyCache);

		if (!empty($text)) {
			$result = false;
		}

		if ($result === false)
		{

			// Фильтр по категории
			$whereCat = $this->_getCategoryFilter($categoryId);
			// Фильтр по городу
			$whereCity = $this->_getCityFilter($cityId);

			$whereMatch = ($text) ? " AND MATCH('{$text}*')" : '';

			while (
				($res = Yii::app()->sphinx
					->createCommand(
						'SELECT id FROM {{store}}'
						. ' WHERE tariff_id >= 1 and type=' . Store::TYPE_OFFLINE
						. $whereCat
						. $whereCity
						. $whereMatch
						. ' LIMIT ' . $STEP * $index . ', ' . $STEP
						. ' OPTION max_matches = 100000'
					)
					->queryColumn())
			) {
				$sql = 'SELECT s.id, s.name, s.address, s.geocode, sc.city_id FROM cat_store as s '
					.'INNER JOIN cat_store_city as sc ON sc.store_id=s.id '
					.'WHERE s.type='.Store::TYPE_OFFLINE.' AND s.id IN (' . implode(',', $res) . ')';
				$stores = Yii::app()->db->createCommand($sql)->queryAll();

				foreach ($stores as $st) {

					$geo = unserialize($st['geocode']);
					if (!isset($geo[0]) || !isset($geo[1])) {
						continue;
					}

					$storeUrl = Yii::app()->createAbsoluteUrl(Store::model()->getLink($st['id']));

					$result[] = array(
						'name'    => $st['name'],
						'address' => 'г. ' . City::model()->findByPk($st['city_id'])->name . '<br>'
							. '«<a href="'.$storeUrl.'">' . $st['name'] . '</a>»<br>'
							. $st['address'],
						'lon'     => $geo[0],
						'lat'     => $geo[1]
					);
				}

				$index++;
			}

			Yii::app()->cache->set($keyCache, $result, 3600);
		}

		return $result;
	}

	private function _getMoneyShops($cityId, $categoryId, $limit = 3)
	{
		$moneyShops = array();

		$tariffVizitka = (int)Store::TARIF_FREE;

		$sql = "SELECT id, product_qt FROM {{store}}"
			. " WHERE tariff_id > :tid"
			. $this->_getCityFilter($cityId)
			. $this->_getCategoryFilter($categoryId)
			. " ORDER BY RAND() LIMIT :limit";


		$ids = Yii::app()->sphinx
			->createCommand($sql)
			->bindParam(':tid', $tariffVizitka)
			->bindParam(':limit', $limit)
			->queryAll();


		foreach ($ids as $item) {
			$st = Store::model()->findByPk($item['id']);
			if ($st) {

				$products = array();

				$index = 1;
				// Сначала получаем товары с витрины
				if (!$st->checkEmptyShowcase()) {

					foreach ($st->getShowcase_data() as $pid) {
						$product = Product::model()->find(
							'id = :id AND status = :st',
							array(':id' => $pid, ':st' => Product::STATUS_ACTIVE)
						);
						if (!$product) {
							continue;
						}

						$products[] = $product;

						if ($index++ >= 5) {
							break;
						}
					}
				}
				// Если с витрины не насобирали товаров, получаем из связок.
				if (count($products) < 5) {
					$res = Yii::app()->db
						->createCommand('SELECT DISTINCT product_id FROM cat_store_price WHERE store_id = :sid AND by_vendor = 0 LIMIT :limit')
						->bindValues(array(':sid' => $st->id, ':limit' => 5 - count($products)))
						->queryColumn();

					foreach ($res as $pid) {
						$product = Product::model()->find(
							'id = :id AND status = :st',
							array(
								':id' => $pid,
								':st' => Product::STATUS_ACTIVE
							)
						);
						if (!$product) {
							continue;
						}

						$products[] = $product;
					}
				}


				$moneyShops[] = array('store' => $st, 'products' => $products);
			}
		}

		return $moneyShops;
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
	 * Возваращет кусок условия WHERE для SQL запроса с фильтрацией
	 * по городу
	 *
	 * @param $cityId Идентификатор города
	 *
	 * @return string
	 */
	private function _getCityFilter($cityId)
	{
		// Добавляем в органичения город
		if ($cityId > 0) {
			$storeList = StoreGeo::getStoreList($cityId);
			$whereCity = ' AND id IN (' . implode(',', $storeList) . ') ';
		} else {
			$whereCity = '';
		}

		return $whereCity;
	}


	/**
	 * Возвращает список магазинов по поисковой фразе
	 *
	 * @param $text Поисковая фраза
	 *
	 * @throws CHttpException
	 */
	public function actionAjaxSearch()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Только AJAX Запрос');
		}

		$sphinxClient = Yii::app()->search;

		$filters = array();

		$filters['type'] = Store::TYPE_OFFLINE;

		$text = Yii::app()->request->getParam('text');

		// Фильтрация по городу
		$cityId = (int)Yii::app()->request->getParam('cityId');
		if ($cityId > 0) {
			$filters['@id'] = StoreGeo::getStoreList($cityId);
		}

		// Фильтрация по категории
		$catId = (int)Yii::app()->request->getParam('category');
		if ($catId > 0) {
			$filters['category_ids'] = $this->_getCategoryIds($catId);
		}

		// Поиск специалистов
		$storeProvider = new CSphinxDataProvider($sphinxClient, array(
			'index'          => 'store',
			'modelClass'     => 'Store',
			'query'          => $text . '*',
			'matchMode'      => SPH_MATCH_ALL,
			'pagination'     => array('pageSize' => 51),
			'useGroupAsPk'   => false,
			'additionalAttr' => array(
				'chain_id' => 'chainId',
				'is_chain' => 'isChain',
				'@count'   => 'chainQt'
			),
			'filters'        => $filters,
			'group'          => array(
				'field' => 'chain_id',
				'mode'  => SPH_GROUPBY_ATTR,
				'order' => '@id DESC'
			),
		));


		// Общее количество найденных магазинов
		$storeTotalItem = CFormatterEx::formatNumeral($storeProvider->getTotalItemCount(), array('магазин', 'магазина', 'магазинов'));


		$html = $this->renderPartial('//catalog/store/_storeSearch', array(
			'storeTotalItem' => $storeTotalItem,
			'storeProvider'  => $storeProvider
		), true);

		exit(json_encode(array(
			'success' => true,
			'html'    => $html,
		)));
	}


	/**
	 * Возаращает набор объектов
	 */
	public function actionAjaxSearchForMap()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Только AJAX Запрос');
		}

		// Поисковая фраза
		$text = Yii::app()->request->getParam('text');

		// Фильтрация по городу
		$city = City::model()->findByPk( (int)Yii::app()->request->getParam('cityId') );
		if ($city instanceof City) {
			$cityId = $city->id;
		} else {
			$cityId = 0;
		}

		// Фильтрация по категории
		$categoryId = (int)Yii::app()->request->getParam('categoryId');


		// Получаем магазина для вывода на карте
		$result = $this->_getShopsForMap($cityId, $categoryId, $text);


		$geoCity = null;
		if ($cityId > 0) {
			$geo = unserialize(YandexMap::getGeocode('Россия '.$city->name));
			if (isset($geo[0]) && isset($geo[1])) {
				$geoCity = array($geo[1], $geo[0]);
			}
		}

		exit(json_encode(array(
			'success' => true,
			'objects' => $result,
			'coords'  => $geoCity
		)));
	}


	/**
	 * Возваращает список магазинов указанной сети магазинов
	 *
	 * @param $id Идентификатор сети магазинов.
	 *
	 * @throws CHttpException
	 */
	public function actionAjaxGetChain($id)
	{
		$chain = Chain::model()->findByPk((int)$id);

		if (!$chain) {
			throw new CHttpException(404);
		}

		$chainId = (int)$chain->id;
		$type = Store::TYPE_OFFLINE;

		// Получаем список все ID, принадлежащих сети
		$sql = 'SELECT id FROM {{store}} WHERE chain_id = :cid AND type=:type ORDER BY id ASC LIMIT 2000';
		$storeIds = Yii::app()->sphinx
			->createCommand($sql)
			->bindParam(':cid', $chainId)
			->bindParam(':type', $type)
			->queryAll();

		$html = $this->renderPartial('//catalog/store/_storeChain', array(
			'storeIds' => $storeIds,
			'chain'    => $chain,
		), true);

		exit(json_encode(array(
			'success' => true,
			'html'    => $html,
		)));
	}


	/**
	 * Возвращает список магазинов по конкретной букве
	 *
	 * @param $id Идентификатор (crc32) буквы
	 * @param $lang Тип, к которому относится буква
	 */
	public function actionAjaxGetBySymbol()
	{
		$crc32Letter = (int)Yii::app()->request->getParam('id');
		$cityId = (int)Yii::app()->request->getParam('cityId');
		$catId = (int)Yii::app()->request->getParam('categoryId');
		$lang = Yii::app()->request->getParam('lang');

		if ($lang == 'spec' || $lang == 'num') {

			switch ($lang) {
				case 'spec':
					$sym = Store::$spec;
					break;
				case 'num':
					$sym = Store::$num;
					break;
				default:
					$sym = array();
					break;
			}
			$ids = array_map('crc32', $sym);

			$sql = "SELECT COUNT(*) qt, id, is_chain, chain_id"
				. " FROM {{store}}"
				. " WHERE first_letter IN (" . implode(',', $ids) . ") and type=".Store::TYPE_OFFLINE
				. $this->_getCityFilter($cityId)
				. $this->_getCategoryFilter($catId)
				. " GROUP BY chain_id "
				. " LIMIT 14, 1000";

			$storeIds = Yii::app()
				->sphinx
				->createCommand($sql)
				->queryAll();

		} else {

			$sql = "SELECT COUNT(*) qt, id, is_chain, chain_id"
				. " FROM {{store}}"
				. " WHERE first_letter = :fid and type=".Store::TYPE_OFFLINE
				. $this->_getCityFilter($cityId)
				. $this->_getCategoryFilter($catId)
				. " GROUP BY chain_id "
				. " LIMIT 14, 1000";

			$storeIds = Yii::app()
				->sphinx
				->createCommand($sql)
				->bindParam(':fid', $crc32Letter)
				->queryAll();

		}


		// Массив моделей Store
		$stores = array();
		foreach ($storeIds as $item) {
			/** @var $store Store */
			$store = Store::model()->findByPk($item['id']);
			if (!$store) {
				continue;
			}

			$store->isChain = $item['is_chain'];
			$store->chainId = $item['chain_id'];
			$store->chainQt = $item['qt'];

			$stores[] = $store;
		}

		$html = $this->renderPartial('//catalog/store/_storeBySymbol', array(
			'stores' => $stores
		), true);

		exit(json_encode(array(
			'success' => true,
			'html'    => $html
		)));
	}


	public function filters() {
		return array('accessControl');
	}

	public function accessRules() {

		return array(
		);
	}

        /**
         * Список товаров магазина
         * @param $id
         * @throws CHttpException
         */
        public function actionProducts($id)
        {
                /** @var $model Store */
                $model = $this->loadModel($id);

                // для магазинов с тарифом визитка не отображать данную страницу
//                if ($model->tariff_id == Store::TARIF_FREE)
//                        throw new CHttpException(403);


		/*
		 * Редиректим на красивый урл для магазина с тарифом "Минисайт"
		 */
		if ($model->tariff_id == Store::TARIF_MINI_SITE && $model->subdomain) {

			$this->redirect(Yii::app()->createAbsoluteUrl(
				'catalog/store/moneyProducts',
				array('sub' => $model->subdomain->domain)
			));
		}

		// Наращиваем счетчик просмотра

		StatStore::hit($model->id, StatStore::TYPE_HIT_STORE);


                /**
                 * Инициализация текущего pagesize
                 * если pagesize нет в куках, то записывается стандартное значение pagesize в куки.
                 * если pagesize есть в куках, то проверяется ее корректность. в случае некорректного значения
                 * в куки записывается дефолтный pagesize в куки
                 */
                $cook_pagesize = Yii::app()->request->cookies['store_product_filter_pagesize'];
                if(!$cook_pagesize) {
                        $pagesize = Product::DEFAULT_PAGESIZE;
                } elseif($cook_pagesize && !isset(Config::$productFilterPageSizes[$cook_pagesize->value])) {
                        $pagesize = Product::DEFAULT_PAGESIZE;
                } else {
                        $pagesize = $cook_pagesize->value;
                }

                $cook_sort = Yii::app()->request->cookies['product_store_sort'];
                if(!$cook_sort) $sort = 'date_desc';
                else $sort = $cook_sort->value;
                $sort = explode('_', $sort);
                $order = isset($sort[1]) ? $sort[1] : 'DESC';
                switch ($sort[0]) {
                        case 'date':
                                $sortString = 't.create_time ' . $order;
                                break;
                        default:
                                $sortString = 't.create_time DESC';
                                break;
                }


                /**
                 * Получение списка производителей, которых продает магазин
                 */
                $vendor_ids = $model->getVendors(false, true);
                $condition = array();

                /**
                 * Формирование условия для выборки товаров по производителям
                 */
                $vendor_id = Yii::app()->request->getParam('vendor_id');
                if($vendor_id)
                        $condition[] = "t.vendor_id = {$vendor_id}";
                elseif ($vendor_ids)
                        $condition[] = "t.vendor_id in ({$vendor_ids})";

                /**
                 * Формирование условия для выборки товаров по категориям
                 */
                $category_id = Yii::app()->request->getParam('category_id');
                if($category_id)
                        $condition[] = "t.category_id = {$category_id}";

                if($vendor_id) {
                        $nav_list = $this->getVendorList($model, $vendor_id);
                        $nav_type = 'vendor';
                } else {
                        $nav_list = $this->getCategoryList($model, $category_id);
                        $nav_type = 'category';
                }

                if (!empty($condition))
                        $condition = implode(' AND ', $condition) . ' AND ';
                else
                        $condition = '';

                /**
                 * DataProvider товаров
                 */
                $dataProvider = new CActiveDataProvider('Product', array(
                        'criteria'=>array(
                                'select'=>'t.*',
                                'condition'=> $condition . 'sp.store_id=:sid AND t.status=:stat',
                                'join'=>'LEFT JOIN cat_store_price sp ON sp.product_id = t.id AND sp.by_vendor=0',
                                'params'=>array(':stat'=>Product::STATUS_ACTIVE, ':sid'=>$model->id),
                                'order'=>$sortString
                        ),
                        'pagination' 	=> array('pageSize' => intval($pagesize)),
                ));
                $dataProvider->getData();

		$mall = Cache::getInstance()->mallBuild;
		if ($mall instanceof MallBuild) {
			$this->layout = '//layouts/layoutBm';
			$this->bodyClass = 'bm-promo goods-item';
			$viewName = '//catalog/store/bm/products';
		} else {
			$viewName = '//catalog/store/products';
		}

                /**
                 * Рендер каталога
                 */
                $this->render($viewName, array(
                        'model'=>$model,
                        'nav_list'=>$nav_list,
                        'dataProvider'=>$dataProvider,
                        'pagesize'=>$pagesize,
                        'sort'=>$sort,
                        'nav_type'=>$nav_type,
                ));
        }

        /**
         * Страница отзывов о магазине
         * @param $id integer - id магазина
         */
        public function actionFeedback($id)
        {
		/** @var $model Store */
		$model = $this->loadModel($id);
                $feedback = new StoreFeedback();

                /**
                 * Инициализация текущего pagesize
                 * если pagesize нет в куках, то записывается стандартное значение pagesize в куки.
                 * если pagesize есть в куках, то проверяется ее корректность. в случае некорректного значения
                 * в куки записывается дефолтный pagesize в куки
                 */
                $cook_pagesize = Yii::app()->request->cookies['store_feedback_pagesize'];
                if(!$cook_pagesize) {
                        $pagesize = StoreFeedback::DEFAULT_PAGESIZE;
                } elseif($cook_pagesize && !isset(Config::$storeFeedbackPageSizes[$cook_pagesize->value])) {
                        $pagesize = StoreFeedback::DEFAULT_PAGESIZE;
                } else {
                        $pagesize = $cook_pagesize->value;
                }

                $cook_sort = Yii::app()->request->cookies['store_feedback_sort'];
                if(!$cook_sort) $sort = 'date_desc';
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

                $feedbacks = new CActiveDataProvider('StoreFeedback', array(
                        'criteria'=>array(
                                'condition'=>'store_id=:sid and parent_id=0',
                                'with'=>array('author'),
                                'order'=>$sortString,
                                'params'=>array(':sid'=>$model->id),
                        ),
                        'pagination'=>array(
                                'pageSize'=>(int) $pagesize,
                        ),
                ));
                $feedbacks->getData();


		// Наращиваем счетчик просмотра
		$model->incrementView();

		// Наращиваем счетчик просмотра

		StatStore::hit($model->id, StatStore::TYPE_HIT_STORE);

		$mall = Cache::getInstance()->mallBuild;
		if ($mall instanceof MallBuild) {
			$this->layout = '//layouts/layoutBm';
			$this->bodyClass = 'bm-promo goods-item';
			$viewName = '//catalog/store/bm/feedback';
		} else {
			$viewName = '//catalog/store/feedback';
		}


                $this->render($viewName, array(
                        'model'=>$model,
                        'feedback'=>$feedback,
                        'feedbacks'=>$feedbacks,
                        'pagesize'=>$pagesize,
                        'sort'=>$sort,
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
                $feedback = new StoreFeedback('feedback');

                if(isset($_POST['StoreFeedback']) && !$model->checkFeedback) {
                        $feedback->message = $_POST['StoreFeedback']['message'];
                        $feedback->mark = $_POST['StoreFeedback']['mark'];
                        $feedback->user_id = Yii::app()->user->id;
                        $feedback->store_id = $model->id;
                        $feedback->parent_id = 0;
                        if($feedback->save()) {
                                die(CJSON::encode(array('success'=>true, 'errors'=>array())));
                        } else {
                                $errors = array();
                                foreach($feedback->getErrors() as $error) {
                                        if(!empty($error[0])) $errors[] = $error[0];
                                }

                                die(CJSON::encode(array('success'=>false, 'errors'=>$errors)));
                        }
                }

                die(CJSON::encode(array('success'=>false, 'errors'=>array('Неизвестная ошибка'))));
        }

        /**
         * Создание и редактирование ответа на фидбек
         */
        public function actionFeedbackAnswer()
        {
                $feedbackId = Yii::app()->request->getParam('commentid');
                $message = Yii::app()->request->getParam('message');
                $answerId = Yii::app()->request->getParam('answerid');
		$typeView = Yii::app()->request->getParam('typeView');

                if (!$feedbackId || !$message)
                        die(json_encode(array('success'=>false, 'message'=>'Некорректный запрос')));

                $feedback = StoreFeedback::model()->findByPk((int) $feedbackId);
                if (!$feedback)
                        die(json_encode(array('success'=>false, 'message'=>'Отзыва, на который вы отвечаете, не существует')));

                $model = $this->loadModel($feedback->store_id);
                if (!$model)
                        die(json_encode(array('success'=>false, 'message'=>'Магазина, для которого оставлен отзыв, не существует')));

                if (!$model->isOwner(Yii::app()->user->id))
                        die(json_encode(array('success'=>false, 'message'=>'Недостаточно прав для создания ответов на отзывы магазина "' . $model->name . '"')));


                if ($answerId) {
			// редактирование ответа

                        $answer = StoreFeedback::model()->findByPk((int) $answerId);
                        if (!$answer || $answer->parent_id != $feedback->id) {
				die(json_encode(array(
						'success' => false,
						'message' => 'Редактируемого ответа не существует'
					)
				));
			}
                } else {
			// создание нового ответа

                        if (StoreFeedback::model()->exists('parent_id=:pid', array(':pid'=>$feedback->id))) {
				die(json_encode(array(
						'success' => false,
						'message' => 'Нельзя оставлять более одного ответа на отзыв'
					)
				));
			}

                        $answer = new StoreFeedback('answer');
                        $answer->parent_id = $feedback->id;
                        $answer->store_id = $model->id;
                }

                $answer->message = CHtml::encode($message);
                $answer->user_id = Yii::app()->user->id;

		if ($typeView == 'minisite') {
			$html = '<p class="-gray -em-all">
					<span class="-black">Ответ магазина:</span>'.$answer->message.'
					<span class="controls">
						<i class="-icon-pencil-xs edit_answer_to_review"></i>
						<i class="-icon-cross-circle-xs delete_answer_to_review"></i>
					</span>
				</p>
				<form class="-hidden">
					<textarea rows="8" class="-gutter-bottom">'.$answer->message.'</textarea>
					<button class="-button -button-skyblue answer_to_review">Сохранить</button>
					<span class="-acronym -gutter-left -gray hideReplyForm">Отмена</span>
				</form>
				';
		} else {
			$html = '<span>Ответ магазина:</span> <span class="text">' . $answer->message . '</span><div class="admin_controls"><span class="edit"><i></i></span><span class="del"><i></i></span></div>';
		}


                if ($answer->save())
                        die(json_encode(array('success'=>true, 'answerId'=>$answer->id, 'html'=>$html)));
                else
                        die(json_encode(array('success'=>false, 'message'=>'Внутренняя ошибка сервера')));
        }

        /**
         * Удаление ответа на фидбек
         */
        public function actionDeleteFeedbackAnswer()
        {
                $feedbackId = Yii::app()->request->getParam('commentid');
                $answerId = Yii::app()->request->getParam('answerid');

                if (!$feedbackId || !$answerId)
                        die(json_encode(array('success'=>false, 'message'=>'Некорректный запрос')));

                $feedback = StoreFeedback::model()->findByPk((int) $feedbackId);
                if (!$feedback)
                        die(json_encode(array('success'=>false, 'message'=>'Отзыва, для которого удаляется ответ, не существует')));

                $model = $this->loadModel($feedback->store_id);
                if (!$model)
                        die(json_encode(array('success'=>false, 'message'=>'Магазина, для которого оставлен отзыв, не существует')));

                if (!$model->isOwner(Yii::app()->user->id))
                        die(json_encode(array('success'=>false, 'message'=>'Недостаточно прав для удаления ответов на отзывы магазина "' . $model->name . '"')));

                $answer = StoreFeedback::model()->findByPk((int) $answerId);
                if (!$answer || $answer->parent_id != $feedback->id)
                        die(json_encode(array('success'=>false, 'message'=>'Удаляемого ответа не существует')));

                $answer->delete();

		$html = '
				<span class="-inline -pseudolink -red -em-all toggleReplyForm"><i>Ответить на отзыв</i></span>
				<form class="-hidden">
					<textarea rows="8" class="-gutter-bottom"></textarea>
					<button class="-button -button-skyblue answer_to_review">Сохранить</button>
					<span class="-acronym -gutter-left -gray hideReplyForm">Отмена</span>
				</form>
			';

		die(json_encode(array(
			'success' => true,
			'html'    => $html
		)));
        }


        /**
         * Возвращает список категорий или список производителей указанного магазина
         * Список обновляется в левом блоке навигации по товарам магазина
         * @param $id - магазин
         * @param $type - categories|vendors - тип возвращаемого списка
         */
        public function actionUpdateNavList($id, $type)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $model = $this->loadModel($id);

                $vendor_id = Yii::app()->request->getParam('vendor_id');
                $category_id = Yii::app()->request->getParam('category_id');

                switch($type) {
                        case 'categories' :
                                $html = $this->getCategoryList($model, $category_id);
                                break;
                        case 'vendors' :
                                $html = $this->getVendorList($model, $vendor_id);
                                break;
                        default :
                                $html = $this->getCategoryList($model, $category_id);
                                break;
                }

                die(CJSON::encode(array('html'=>$html)));
        }

        public function loadModel($id)
        {
                $model=Store::model()->findByPk((int) $id);
                if($model===null)
                        throw new CHttpException(404);
                return $model;
        }

	public function actionViewSite($store_id)
	{
		$store = Store::model()->findByPk($store_id);
		if (!$store || !$store->site) {
			throw new CHttpException(404);
		}

		// Нарщиваем переходы на сайт
		StatStore::hit($store->id, StatStore::TYPE_SITE);

		$this->redirect(Amputate::absoluteUrl($store->site));
	}

        /**
         * Возвращает список li категорий, в которых есть товары указанного магазина
         * @param $model - магазин
         * @param $route string - Роут для генерации ссылки на категорию товаров
         * @return string - список <li>
         */
        private function getCategoryList($model, $selected_id, $route = 'products', $typeHtml = 'default')
        {
                $categories = Yii::app()->db->createCommand()
                        ->select('c.id, c.name, count(p.id) as qt')->from('cat_category c')
                        ->rightJoin('cat_product p', 'c.id=p.category_id and p.status=:stat', array(':stat'=>Product::STATUS_ACTIVE))
                        ->join('cat_store_price sp', 'sp.product_id=p.id and sp.by_vendor=0')
                        ->where('sp.store_id=:sid and (c.rgt-c.lft)=1', array(':sid'=>$model->id))->group('c.id')->queryAll();

                $html = '';
		$totalCats = count($categories);

		foreach ($categories as $index => $cat) {

			if ($selected_id == $cat['id']) {
				$class = 'current';
			} else {
				$class = '';
			}

			switch ($typeHtml) {
				case 'moneyIndex':
					// Список категорий для главной страницы магазина с тарифом "Минисайт"
					$html .= CHtml::openTag('li', array('class' => $class))
						. CHtml::link(
							$cat['name'],
							$this->createUrl($route, array('id' => $model->id, 'category_id' => $cat['id']))
						);
					if ($totalCats > 4 || ($totalCats <= 4 && ($index + 1) < $totalCats)) {
						$html .= ',';
					}
					$html .= CHtml::closeTag('li');
					break;

				default:
					// Список категорий для списка товаров магазина с платным тарифом
					$html .= CHtml::openTag('li', array('class' => $class))
						. CHtml::link(
							$cat['name'],
							$this->createUrl($route, array('id' => $model->id, 'category_id' => $cat['id']))
						)
						. CHtml::tag('span', array(), $cat['qt'])
						. CHtml::closeTag('li');
					break;
			}

			if ($typeHtml == 'moneyIndex' && $index >= 4) {
				break;
			}
                }

		if ($typeHtml == 'moneyIndex' && $totalCats > 4) {
			$html .= '<li><a href="'.Store::getLink($model->id, 'moneyProducts').'" class="-pointer-right -red">Все категории</a></li>';
		}

                return $html;
        }

        private function getVendorList($model, $selected_id, $route = 'products')
        {
                $vendors = Yii::app()->db->createCommand()
                        ->select('v.id, v.name, count(p.id) as qt')->from('cat_vendor v')
                        ->join('cat_product p', 'p.vendor_id=v.id  and p.status=:stat', array(':stat'=>Product::STATUS_ACTIVE))
                        ->join('cat_store_price sp', 'sp.product_id=p.id and sp.store_id=:sid and sp.by_vendor=0', array(':sid'=>$model->id))
                        ->where('v.id is not null')->group('v.id')->queryAll();

                $html = '';
                foreach($vendors as $vnd) {
                        if($selected_id == $vnd['id'])
                                $class = 'current';
                        else
                                $class = '';

                        $html.=CHtml::openTag('li', array('class'=>$class))
                                . CHtml::link($vnd['name'], $this->createUrl($route, array('id'=>$model->id, 'vendor_id'=>$vnd['id'])))
                                . CHtml::tag('span', array(), $vnd['qt'])
                                . CHtml::closeTag('li');
                }

                return $html;
        }


	/**
	 * Получаем список похожих магазинов
	 * Исходя из условий
	 * Если перешли на карточку магазина из каталога или
	 * карточки товара то выдаем магазины той же категории
	 * в том же городе
	 * Если пришли из другого места то выдаем магазины которые продают товары в той же категории
	 * что и магазин на который перешли
	 * @param $model
	 * @param $limit
	 *
	 * @return array|bool
	 */
	private function _getRelatedShops($model, $limit)
	{
		//Проверяем есть ли referrer и если есть,  то проверяем пришел ли он с myhome
		$relatedShops = array();

		$urlReferrer = Yii::app()->request->getUrlReferrer();
		if($urlReferrer)
		{
			$urlParse = parse_url($urlReferrer);

		}

		if (isset($urlParse) && Yii::app()->request->getServerName() === $urlParse['host']) {

			$path = $urlParse['path'];

			if (preg_match('@^(/catalog)(?:/([\w, -]+))?(?:/([-\w]+))?$@u', $path, $pathArray)) {
				//Получаем последний элемент массива
				$arrayKeys = array_keys($pathArray);
				$end = end($arrayKeys);
				$lastElement = $pathArray[$end];

				//Если последний элемент цифра значит переход с карточки товара
				if (preg_match("/^\d+$/", $lastElement)) {
					$productModel = Product::model()->findByPk($lastElement);
					//Если найден товар то получаем категорию и делаем запрос на список похожих магазинов
					if ($productModel) {
						$relatedShops = Store::model()->getRelatedShops($productModel->category_id, $model->city_id, $model->id, $limit);
					}
				} else {
					$categoryModel = Category::model()->findByAttributes(array('eng_name' => $lastElement));

					//Если категория существует и это последняя категория то переход с каталога,
					//и делаем запрос на список похожих магазинов
					if ($categoryModel && $categoryModel->rgt - $categoryModel->lft == 1) {
						$relatedShops = Store::model()->getRelatedShops($categoryModel->id, $model->city_id, $model->id, $limit);
					}
				}
			}
		}

		//Если не применились предыдущие условия
		if (!$relatedShops) {
			$usedCategory = $model->getUsedCategory(true);

			if ($usedCategory) {

				for ($i = 0; $i < count($usedCategory); $i++) {
					$relatedShops = array_merge($relatedShops, Store::model()->getRelatedShops($usedCategory[$i], $model->city_id, $model->id, $limit));
					//Если количство магазинов равно лимиту то выходим из цикла
					if (count($relatedShops) == $limit) {
						break;
					}
				}
			}
		}

		($relatedShops) ? $result = $relatedShops : $result = array();

		return $result;
	}


	/**
	 * Главная страница магазина с тарифом "Минисайт"
	 *
	 * @throws CHttpException
	 */
	public function actionMoneyIndex()
	{
		$this->layout = '//layouts/shop';

		$this->bodyClass = array('mini-site', 'mini-site-index');
		$this->htmlClass = array('bg-1');

		/** @var $store Store */
		$store = Cache::getInstance()->store;
		if (!$store) {
			throw new CHttpException(404);
		}

		if ($store->bg_class) {
			$this->htmlClass = $store->bg_class;
		}

		if ($store->head_image_id) {
			$this->bodyClass[] = 'mini-site-full';
		} else {
			$this->bodyClass[] = 'mini-site-min';
		}

		// Последние новости
		$storeNews = StoreNews::model()->findAllByAttributes(array(
			'store_id' => $store->id,
			'status'   => StoreNews::STATUS_PUBLIC
		), array(
			'order' => 'create_time DESC',
			'limit' => '2'
		));


		/* Назначаем параметр для лейаута.
		 * Нужен для генеравции навигации
		 */
		$this->layoutParams['store'] = $store;

		$navListCategory = $this->getCategoryList($store, 0, '/catalog/store/moneyProducts', 'moneyIndex');

		$this->render('//catalog/store/moneyIndex', array(
			'store'           => $store,
			'navListCategory' => $navListCategory,
			'isOwner'         => $store->isOwner(Yii::app()->user->id),
			'storeNews'       => $storeNews
		));
	}


	/**
	 * Фотогалерея магазина с тарифом "Минисайт"
	 *
	 * @throws CHttpException
	 */
	public function actionMoneyGallery()
	{
		$this->layout = '//layouts/shop';

		$this->bodyClass = array('mini-site', 'mini-site-gallery');

		/** @var $store Store */
		$store = Cache::getInstance()->store;

		if (!$store) {
			throw new CHttpException(404);
		}
		if ($store->bg_class) {
			$this->htmlClass = $store->bg_class;
		}

		if ($store->head_image_id) {
			$this->bodyClass[] = 'mini-site-full';
		} else {
			$this->bodyClass[] = 'mini-site-min';
		}

		// Владелец.
		$isOwner = $store->isOwner(Yii::app()->user->id);

		if ($isOwner) {
			$this->bodyClass[] = 'owner';
		}



		$criteria = new CDbCriteria(array(
			'condition' => 'store_id = :sid',
			'params'    => array(':sid' => $store->id),
			'order'     => 'position ASC, create_time DESC'
		));
		if (!$isOwner) {
			$criteria->addCondition('status = :st');
			$criteria->params[':st'] = StoreGallery::STATUS_PUBLIC;
		}
		$photosProvider = new CActiveDataProvider('StoreGallery', array(
			'criteria'   => $criteria,
			'pagination' => array(
				'pageSize' => 24
			)
		));



		/* Назначаем параметр для лейаута.
		 * Нужен для генеравции навигации
		 */
		$this->layoutParams['store'] = $store;

		$this->render('//catalog/store/moneyGallery', array(
			'store'          => $store,
			'photosProvider' => $photosProvider
		));
	}


	/**
	 * Список товаров магазина
	 *
	 * @throws CHttpException
	 */
	public function actionMoneyProducts()
	{
		$this->layout = '//layouts/shop';

		$this->bodyClass = array('mini-site', 'mini-site-goods');

		/** @var $store Store */
		$store = Cache::getInstance()->store;
		if (!$store) {
			throw new CHttpException(404);
		}
		if ($store->bg_class) {
			$this->htmlClass = $store->bg_class;
		}

		if ($store->head_image_id) {
			$this->bodyClass[] = 'mini-site-full';
		} else {
			$this->bodyClass[] = 'mini-site-min';
		}

		// Для магазинов с бесплатным тарифом не отображать данную страницу
		if ($store->tariff_id == Store::TARIF_FREE) {
			throw new CHttpException(403);
		}

		/* Назначаем параметр для лейаута.
		 * Нужен для генеравции навигации
		 */
		$this->layoutParams['store'] = $store;



		// Наращиваем счетчик просмотра
		StatStore::hit($store->id, StatStore::TYPE_HIT_STORE);


		/**
		 * Инициализация текущего pagesize
		 * если pagesize нет в куках, то записывается стандартное значение pagesize в куки.
		 * если pagesize есть в куках, то проверяется ее корректность. в случае некорректного значения
		 * в куки записывается дефолтный pagesize в куки
		 */
		$cook_pagesize = Yii::app()->request->cookies['store_product_filter_pagesize'];
		if (!$cook_pagesize) {
			$pagesize = Product::DEFAULT_PAGESIZE;
		} elseif ($cook_pagesize && !isset(Config::$productFilterPageSizes[$cook_pagesize->value])) {
			$pagesize = Product::DEFAULT_PAGESIZE;
		} else {
			$pagesize = $cook_pagesize->value;
		}

		$cook_sort = Yii::app()->request->cookies['product_store_sort'];
		if (!$cook_sort) {
			$sort = 'date_desc';
		} else {
			$sort = $cook_sort->value;
		}
		$sort = explode('_', $sort);
		$order = isset($sort[1]) ? $sort[1] : 'DESC';
		switch ($sort[0]) {
			case 'date':
				$sortString = 't.create_time ' . $order;
				break;
			default:
				$sortString = 't.create_time DESC';
				break;
		}


		/**
		 * Получение списка производителей, которых продает магазин
		 */
		$vendor_ids = $store->getVendors(false, true);
		$condition = array();

		/**
		 * Формирование условия для выборки товаров по производителям
		 */
		$vendor_id = Yii::app()->request->getParam('vendor_id');
		if ($vendor_id) {
			$condition[] = "t.vendor_id = {$vendor_id}";
		} elseif ($vendor_ids) {
			$condition[] = "t.vendor_id in ({$vendor_ids})";
		}

		/**
		 * Формирование условия для выборки товаров по категориям
		 */
		$category_id = Yii::app()->request->getParam('category_id');
		if ($category_id) {
			$condition[] = "t.category_id = {$category_id}";
		}

		$navListVendor = $this->getVendorList($store, $vendor_id, '/catalog/store/moneyProducts');
		$navListCategory = $this->getCategoryList($store, $category_id, '/catalog/store/moneyProducts');

		if ($vendor_id) {
			$navType = 'vendor';
		} else {
			$navType = 'category';
		}

		// Показывать товары "Все" / "Со скидкой"
		$showType = Yii::app()->request->getParam('show');
		if ($showType == 'discount') {
			$condition[] = 'sp.discount > 0';
		}

		if (!empty($condition)) {
			$condition = implode(' AND ', $condition) . ' AND ';
		} else {
			$condition = '';
		}

		/**
		 * DataProvider товаров
		 */
		$dataProvider = new CActiveDataProvider('Product', array(
			'criteria'=>array(
				'select'    => 't.*',
				'condition' => $condition . 'sp.store_id=:sid AND t.status=:stat',
				'join'      => 'LEFT JOIN cat_store_price sp ON sp.product_id = t.id AND sp.by_vendor=0',
				'params'    => array(':stat' => Product::STATUS_ACTIVE, ':sid' => $store->id),
				'order'     => $sortString
			),
			'pagination' 	=> array('pageSize' => intval($pagesize)),
		));
		$dataProvider->getData();

		// Определяем тип отображения (большие или маленькие карточки)
		$viewType = intval( Yii::app()->request->getParam('view_type', 1) );
		if (!in_array($viewType, array(1, 2))) {
			$viewType = 1;
		}

		//Костыль, но пофик так как все уволены
		$viewType = 2;



		/**
		 * Рендер каталога
		 */
		$this->render('//catalog/store/moneyProducts', array(
			'store'           => $store,
			'navListVendor'   => $navListVendor,
			'navListCategory' => $navListCategory,
			'navType'         => $navType,
			'dataProvider'    => $dataProvider,
			'pagesize'        => $pagesize,
			'sort'            => $sort,
			'viewType'        => $viewType,
			'showType'        => $showType,
		));

	}

	/**
	 * Страница отзывов о магазине
	 * @param $id integer - id магазина
	 */
	public function actionMoneyFeedback()
	{
		$this->layout = '//layouts/shop';

		$this->bodyClass = array('mini-site', 'mini-site-reviews');

		/** @var $store Store */
		$store = Cache::getInstance()->store;

		if (!$store) {
			throw new CHttpException(404);
		}

		if ($store->bg_class) {
			$this->htmlClass = $store->bg_class;
		}

		if ($store->head_image_id) {
			$this->bodyClass[] = 'mini-site-full';
		} else {
			$this->bodyClass[] = 'mini-site-min';
		}

		/* Назначаем параметр для лейаута.
		 * Нужен для генеравции навигации
		 */
		$this->layoutParams['store'] = $store;




		$feedback = new StoreFeedback();

		/**
		 * Инициализация текущего pagesize
		 * если pagesize нет в куках, то записывается стандартное значение pagesize в куки.
		 * если pagesize есть в куках, то проверяется ее корректность. в случае некорректного значения
		 * в куки записывается дефолтный pagesize в куки
		 */
		$cook_pagesize = Yii::app()->request->cookies['store_feedback_pagesize'];
		if (!$cook_pagesize) {
			$pagesize = StoreFeedback::DEFAULT_PAGESIZE;
		} elseif ($cook_pagesize && !isset(Config::$storeFeedbackPageSizes[$cook_pagesize->value])) {
			$pagesize = StoreFeedback::DEFAULT_PAGESIZE;
		} else {
			$pagesize = $cook_pagesize->value;
		}

		$cook_sort = Yii::app()->request->cookies['store_feedback_sort'];
		if (!$cook_sort) {
			$sort = 'date_desc';
		} else {
			$sort = $cook_sort->value;
		}
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

		$feedbacks = new CActiveDataProvider('StoreFeedback', array(
			'criteria'   => array(
				'condition' => 'store_id = :sid and parent_id=0',
				'with'      => array('author'),
				'order'     => $sortString,
				'params'    => array(':sid' => $store->id),
			),
			'pagination' => array(
				'pageSize' => (int)$pagesize,
			),
		));
		$feedbacks->getData();


		// Наращиваем счетчик просмотра
		$store->incrementView();

		// Наращиваем счетчик просмотра

		StatStore::hit($store->id, StatStore::TYPE_HIT_STORE);


		$this->render('//catalog/store/moneyFeedback', array(
			'store'     => $store,
			'feedback'  => $feedback,
			'feedbacks' => $feedbacks,
			'pagesize'  => $pagesize,
			'sort'      => $sort,
		));
	}


	/**
	 * Список новостей магазина с тарифом "Минисайт"
	 *
	 * @throws CHttpException
	 */
	public function actionMoneyNews()
	{
		$this->layout = '//layouts/shop';

		$this->bodyClass = array('mini-site', 'mini-site-news');

		/** @var $store Store */
		$store = Cache::getInstance()->store;
		if (!$store) {
			throw new CHttpException(404);
		}
		if ($store->bg_class) {
			$this->htmlClass = $store->bg_class;
		}

		if ($store->head_image_id) {
			$this->bodyClass[] = 'mini-site-full';
		} else {
			$this->bodyClass[] = 'mini-site-min';
		}

		// Владелец.
		$isOwner = $store->isOwner(Yii::app()->user->id);

		if ($isOwner) {
			$this->bodyClass[] = 'owner';
		}


		// Новости магазина
		$newsProvider = new CActiveDataProvider('StoreNews', array(
			'criteria' => array(
				'order'     => 'create_time DESC',
				'condition' => 'store_id = :sid',
				'params'    => array(':sid' => $store->id)
			),
			'pagination' => array(
				'pageSize' => 10
			),
		));

		/* Назначаем параметр для лейаута.
		 * Нужен для генеравции навигации
		 */
		$this->layoutParams['store'] = $store;

		$this->render('//catalog/store/moneyNews', array(
			'store'        => $store,
			'newsProvider' => $newsProvider
		));
	}


	/**
	 * Детальная страница новости магазина.
	 *
	 * @param $id Идентификатор новости
	 *
	 * @throws CHttpException
	 */
	public function actionMoneyNewsDetail($id)
	{
		$this->layout = '//layouts/shop';

		$this->bodyClass = array('mini-site', 'mini-site-news');

		/** @var $store Store */
		$store = Cache::getInstance()->store;

		if (!$store) {
			throw new CHttpException(404);
		}
		if ($store->bg_class) {
			$this->htmlClass = $store->bg_class;
		}

		if ($store->head_image_id) {
			$this->bodyClass[] = 'mini-site-full';
		} else {
			$this->bodyClass[] = 'mini-site-min';
		}

		// Владелец.
		$isOwner = $store->isOwner(Yii::app()->user->id);

		if ($isOwner) {
			$this->bodyClass[] = 'owner';
		}

		$news = StoreNews::model()->findByPk((int)$id);
		if (!$news) {
			throw new CHttpException(404);
		}

		/* Назначаем параметр для лейаута.
		 * Нужен для генеравции навигации
		 */
		$this->layoutParams['store'] = $store;

		$this->render('//catalog/store/moneyNewsDetail', array(
			'store' => $store,
			'news'  => $news
		));
	}

	/**
	 * Ajax'овый метод для удаления новостей магазина.
	 *
	 * @throws CHttpException
	 */
	public function actionAjaxMoneyNewsDelete()
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(404);
		}

		// Ответное сообщение Ajax запроса.
		$message = '';

		$newsId = Yii::app()->request->getParam('newsId');
		$news = StoreNews::model()->findByPk($newsId);
		if (!$news) {
			throw new CHttpException(404, 'Новость не найдена');
		}


		/** @var $store Store */
		$store = Store::model()->findByPk($news->store_id);
		if (!$store) {
			throw new CHttpException(404, 'Магазин не найден');
		}
		if (!$store->isOwner(Yii::app()->user->id)) {
			throw new CHttpException(403);
		}


		if ($news->delete()) {
			$success = true;
		} else {
			$success = false;
			$message = 'Ошибка при удалении новости';
		}

		exit(json_encode(array(
			'success' => $success,
			'message' => $message
		)));
	}

	public function actionAjaxMoneyNewsDeletePhoto()
	{
		$newsId = Yii::app()->request->getParam('newsId');
		$photoId = Yii::app()->request->getParam('photoId');

		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(404);
		}

		$news = StoreNews::model()->findByPk($newsId);
		if (!$news) {
			throw new CHttpException(404, 'Новость не найдена');
		}

		/** @var $store Store */
		$store = Cache::getInstance()->store;
		if (!$store) {
			throw new CHttpException(404, 'Магазин не найден');
		}

		if ($news->store_id != $store->id) {
			throw new CHttpException(403);
		}


		if ($news->image_id != $photoId) {
			$success = false;
			$message = 'Удаляемая фотография не принадлежит новости.';
			goto the_end;
		}

		$news->image_id = null;
		$news->save(false);

		$success = true;
		$message = '';

		the_end:
		exit(json_encode(array(
			'success' => $success,
			'message' => $message
		)));
	}



	/**
	 * Редактирование существующей новости магазина.
	 *
	 * @throws CHttpException
	 */
	public function actionAjaxMoneyNewsEdit()
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(404);
		}

		// Ответное сообщение Ajax запроса.
		$message = '';

		$storeId = Yii::app()->request->getParam('storeId');
		$newsId = Yii::app()->request->getParam('newsId');


		/** @var $store Store */
		$store = Store::model()->findByPk($storeId);
		if (!$store) {
			throw new CHttpException(404, 'Магазин не найден');
		}
		if (!$store->isOwner(Yii::app()->user->id)) {
			throw new CHttpException(403);
		}

		$news = StoreNews::model()->findByPk($newsId);
		if (!$news) {
			throw new CHttpException(404, 'Новость не найдена');
		}

		$data['newsId'] = $news->id;

		$html = $this->renderPartial('//catalog/store/_moneyNewsPopup', array(
			'store' => $store,
			'data' => $news
		), true);

		exit(json_encode(array(
			'success' => true,
			'data'    => $data,
			'message' => $message,
			'html'    => $html
		)));
	}

	/**
	 * Сохраняет название и описание новости
	 */
	public function actionAjaxMoneyNewsSave()
	{
		$success = false;
		$message = 'Во время сохранения новости произошла ошибка!';

		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(404);
		}

		$request = Yii::app()->getRequest();
		$newsId = (int)$request->getParam('newsId');

		/** @var $news StoreNews */
		$news = StoreNews::model()->findByPk($newsId);

		if (!$news) {
			throw new CHttpException(404, 'Новость не найдена');
		}

		/** @var $store Store */
		$store = Store::model()->findByPk($news->store_id);
		if (!$store) {
			throw new CHttpException(404, 'Новость не принадлежит магазину');
		}
		if (!$store->isOwner(Yii::app()->user->id)) {
			throw new CHttpException(403);
		}

		if (isset($_POST['StoreNews'])) {
			$news->attributes = $_POST['StoreNews'];
			$news->status = StoreNews::STATUS_PUBLIC;

			if ($news->save()) {
				$success = true;
				$message = '';
			} else {
				$success = false;
				$message = $news->getErrors();
			}
		}

		$html = $this->renderPartial('//catalog/store/_moneyNewsItem', array(
			'store' => $store,
			'data'  => $news
		), true);

		exit(json_encode(array(
			'success' => $success,
			'message' => $message,
			'html'    => $html,
			'newsId'  => $news->id
		)));
	}

	/**
	 * Загружает фотографию и другие данные из формы.
	 *
	 * @throws CHttpException
	 */
	public function actionAjaxMoneyNewsImageUpload()
	{
		if (!Yii::app()->request->isPostRequest) {
			throw new CHttpException(400);
		}


		$newsId = (int)Yii::app()->request->getParam('newsId', 0);
		if ($newsId == 0) {
			$news = new StoreNews();
		} else {
			$news = StoreNews::model()->findByPk($newsId);
		}
		if (!$news) {
			throw new CHttpException(404);
		}

		/** @var $store Store */
		$store = Store::model()->findByPk((int)Yii::app()->request->getParam('storeId'));
		if (!$store) {
			throw new CHttpException(404);
		}
		if (!$store->isOwner(Yii::app()->user->id)) {
			throw new CHttpException(403);
		}

		$news->status = StoreNews::STATUS_NEW;
		$news->user_id = Yii::app()->user->id;
		$news->store_id = $store->id;

		$news->attributes = $_POST['StoreNews'];

		if ($news->save()) {

			// Если основные данные без ошибок, пробуем сохранить фотку
			if (isset($_FILES['StoreNews']) && !empty($_FILES['StoreNews'])) {
				$news->setImageType('image');
				$file = UploadedFile::loadImage($news, 'image', '', true, null, true, array('width' => 140, 'height' => 140));
				if ($file) {
					$file_errors = $file->getErrors();
				} else {
					die(CJSON::encode(array(
						'success' => false,
						'message' => 'Не удалось загрузить файл'
					)));
				}

				if (isset($file_errors) && isset($file_errors['file'])) {
					$error_message = $file_errors['file'][0];
					die(CJSON::encode(array(
						'success' => false,
						'message' => $error_message
					)));
				}

				$news->image_id = $file->id;
			}

			$news->status = StoreNews::STATUS_PUBLIC;

			if ($news->save()) {
				die(CJSON::encode(array(
					'success' => true,
				)));
			} else {
				die(CJSON::encode(array(
					'success' => false,
					'message' => $news->getErrors()
				)));
			}

		} else {

			die(CJSON::encode(array(
				'success' => false,
				'message' => $news->getErrors()
			)));
		}

		$this->layout = false;
	}

	/**
	 * Сохраняет данные из формы (Название, Описание, Фотографию)
	 *
	 * @throws CHttpException
	 */
	public function actionAjaxMoneyGalleryImageUpload()
	{
		if (!Yii::app()->request->isPostRequest) {
			throw new CHttpException(400);
		}

		$photoId = (int)Yii::app()->request->getParam('photoId', 0);


		if ($photoId == 0) {
			$photo = new StoreGallery();

		} else {
			$photo = StoreGallery::model()->findByPk($photoId);
		}

		if (!$photo) {
			throw new CHttpException(404);
		}

		/** @var $store Store */
		$store = Store::model()->findByPk((int)Yii::app()->request->getParam('storeId'));
		if (!$store) {
			throw new CHttpException(404);
		}
		if (!$store->isOwner(Yii::app()->user->id)) {
			throw new CHttpException(403);
		}


		$photo->user_id = Yii::app()->user->id;
		$photo->store_id = $store->id;
		$photo->status = StoreGallery::STATUS_NEW;

		$photo->attributes = $_POST['StoreGallery'];

		if ($photo->save(true, array('name', 'description'))) {

			// Если основные данные без ошибок, пробуем сохранить фотку
			if (!$photo->preview || ($photo->preview && isset($_FILES['StoreGallery']))) {
				$photo->setImageType('image');
				$file = UploadedFile::loadImage($photo, 'image', '', true, null, true, array('width' => 140, 'height' => 140));

				if ($file) {
					$file_errors = $file->getErrors();
				} else {
					die(CJSON::encode(array(
						'success' => false,
						'message' => 'Ошибка при загрузке файла'
					)));
				}

				if (isset($file_errors) && isset($file_errors['file'])) {
					$error_message = $file_errors['file'][0];
					die(CJSON::encode(array(
						'success' => false,
						'message' => $error_message
					)));
				}

				$photo->image_id = $file->id;
			}

			$photo->status = StoreGallery::STATUS_PUBLIC;

			if ($photo->save()) {
				die(CJSON::encode(array(
					'success' => true,
				)));
			} else {
				die(CJSON::encode(array(
					'success' => false,
					'message' => $photo->getErrors()
				)));
			}

		} else {
			die(CJSON::encode(array(
				'success' => false,
				'message' => $photo->getErrors()
			)));
		}

		$this->layout = false;


	}

	/**
	 * Возвращает элемент для редактирования
	 *
	 * @throws CHttpException
	 */
	public function actionAjaxMoneyGalleryEdit()
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(404);
		}

		// Ответное сообщение Ajax запроса.
		$message = '';

		$storeId = Yii::app()->request->getParam('storeId');
		$photoId = Yii::app()->request->getParam('photoId');


		/** @var $store Store */
		$store = Store::model()->findByPk($storeId);
		if (!$store) {
			throw new CHttpException(404, 'Магазин не найден');
		}
		if (!$store->isOwner(Yii::app()->user->id)) {
			throw new CHttpException(403);
		}

		$photo = StoreGallery::model()->findByPk($photoId);
		if (!$photo) {
			throw new CHttpException(404, 'Фотография не найдена');
		}

		$data['newsId'] = $photo->id;

		$html = $this->renderPartial('//catalog/store/_moneyGalleryPopup', array(
			'data' => $photo
		), true);

		exit(json_encode(array(
			'success' => true,
			'data'    => $data,
			'message' => $message,
			'html'    => $html
		)));
	}

	/**
	 * Удалает фотографию из галереи
	 *
	 * @throws CHttpException
	 */
	public function actionAjaxMoneyGalleryDelete()
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(404);
		}

		// Ответное сообщение Ajax запроса.
		$message = '';

		$photoId = Yii::app()->request->getParam('photoId');
		$photo = StoreGallery::model()->findByPk($photoId);
		if (!$photo) {
			throw new CHttpException(404, 'Фото не найдена');
		}


		/** @var $store Store */
		$store = Store::model()->findByPk($photo->store_id);
		if (!$store) {
			throw new CHttpException(404, 'Магазин не найден');
		}
		if (!$store->isOwner(Yii::app()->user->id)) {
			throw new CHttpException(403);
		}


		if ($photo->delete()) {
			$success = true;
		} else {
			$success = false;
			$message = 'Ошибка при удалении фотографии';
		}

		exit(json_encode(array(
			'success' => $success,
			'message' => $message
		)));
	}

	/**
	 * Запрос на сохранение фонового изображения
	 */
	public function actionAjaxMoneySaveBg()
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(404);
		}

		/** @var $store Store */
		$store = Cache::getInstance()->store;

		if (!$store) {
			throw new CHttpException(404);
		}

		$success = true;
		$message = '';

		$bgClass = Yii::app()->request->getParam('bgClass');

		if (!in_array($bgClass, array('none')+Store::$bgClasses)) {
			$success = 'false';
			$message = 'Недопустимое фоновое изображение';
			goto the_end;
		}

		$store->bg_class = $bgClass;
		if ($store->save(true, array('bg_class'))) {
			$success = true;
			goto the_end;
		}


		the_end:
		exit(json_encode(array(
			'success' => $success,
			'message' => $message
		)));
	}

	/**
	 * Загружает фотографию для шапки магазина "Минисайт"
	 *
	 * @throws CHttpException
	 */
	public function actionAjaxMoneyHeaderUpload()
	{
		if (!Yii::app()->request->isPostRequest) {
			throw new CHttpException(400);
		}

		/** @var $store StoreNews */
		$store = Cache::getInstance()->store;
		if (!$store) {
			throw new CHttpException(404);
		}


		$store->setImageType('headImage');
		$file = UploadedFile::loadImage($store, 'image', '', true, null, true, array('width' => 1000, 'height' => 230));
		if ($file) {
			$file_errors = $file->getErrors();
		} else {
			die(CJSON::encode(array(
				'success' => false,
				'message' => 'Не удалось загрузить файл'
			)));
		}

		if (isset($file_errors) && isset($file_errors['file'])) {
			$error_message = $file_errors['file'][0];
			die(CJSON::encode(array(
				'success' => false,
				'message' => $error_message
			)));
		}

		$store->head_image_id = $file->id;
		$store->save(false);

		$this->layout = false;

		die(CJSON::encode(array(
			'success' => true,
			'src'    => '/' . $file->getPreviewName(Store::$preview['resize_width_1000'])
		)));
	}

	/**
	 * Удаляет картинку для шапки магазина "Минисайт"
	 *
	 * @throws CHttpException
	 */
	public function actionAjaxMoneyHeaderDelete()
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(404);
		}

		$success = true;
		// Ответное сообщение Ajax запроса.
		$message = '';


		/** @var $store Store */
		$store = Cache::getInstance()->store;
		if (!$store) {
			throw new CHttpException(404, 'Магазин не найден');
		}
		if (!$store->isOwner(Yii::app()->user->id)) {
			throw new CHttpException(403);
		}

		$store->head_image_id = 0;
		if ($store->save()) {
			$success = true;
		} else {
			$success = false;
			$message = 'Ошибка при удалении фотографии';
		}

		exit(json_encode(array(
			'success' => $success,
			'message' => $message
		)));
	}

	/**
	 * Обрезает фотографию в шапке по выбранному сдвигу.
	 */
	public function actionAjaxMoneyHeaderCrop()
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(404);
		}

		$success = true;
		// Ответное сообщение Ajax запроса.
		$message = '';


		/** @var $store Store */
		$store = Cache::getInstance()->store;
		if (!$store) {
			throw new CHttpException(404, 'Магазин не найден');
		}
		if (!$store->isOwner(Yii::app()->user->id)) {
			throw new CHttpException(403);
		}

		$offsetTop = abs((int)Yii::app()->request->getParam('offsetTop'));

		// Шаг 1. Получаем оригинальные размеры Ресайзеной и Кропленной фотографии
		$imgResize = $store->headImage->getPreviewName(Store::$preview['resize_width_1000']);
		$imgCrop = $store->headImage->getPreviewName(Store::$preview['crop_1000_230']);

		/* Шаг 2. Обрезаем Ресайзеную фотку с нужным сдвигом и заменяем полученной
		 * фоткой, полученную Кропленную фотку на предыдущем шаге.
		 */

		$imageHandler = new imageHandler($imgResize, imageHandler::FORMAT_JPEG);
		$imageHandler->jCrop(1000, 230, 0, $offsetTop, 1000, 230, 1000);
		if ($imageHandler->saveImage($imgCrop)) {
			$success = false;
			$message = 'При сохранении изображения произожла ошибка!';
		}

		exit(json_encode(array(
			'success' => $success,
			'message' => $message
		)));
	}
}
