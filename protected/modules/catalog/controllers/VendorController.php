<?php

class VendorController extends FrontController
{
        public function actionIndex($id)
        {
                $model = $this->loadModel($id);
                $cities = $this->getStoreCities($model, true);
                $products = Product::model()->findAll('vendor_id=:id and status=:stat limit 4', array(':id'=>$model->id, ':stat'=>Product::STATUS_ACTIVE));

		$mall = Cache::getInstance()->mallBuild;
		if ($mall instanceof MallBuild) {
			$this->layout = '//layouts/layoutBm';
			$this->bodyClass = 'bm-promo goods-item';
			$viewName = '//catalog/vendor/bm/index';
		} else {
			$viewName = '//catalog/vendor/index';
		}


		$this->render($viewName, array(
                        'model'=>$model,
                        'cities'=>$cities,
                        'products'=>$products,
                ));
        }

        public function actionStores($id)
        {
                $model = $this->loadModel($id);
                $cities = $this->getStoreCities($model);

                /**
                 * Сортировка результатов по алфавиту
                 */
                $sorted = array();
                $count = 0;
                foreach($cities as $item) {
                        $key = mb_substr($item['name'], 0, 1, 'UTF-8');

                        if(!array_key_exists($key, $sorted))
                                $count=0;

                        $sorted[$key]['data'][] = $item;
                        $sorted[$key]['count'] = ++$count;
                }

		ksort($sorted);

		$mall = Cache::getInstance()->mallBuild;
		if ($mall instanceof MallBuild) {
			$this->layout = '//layouts/layoutBm';
			$this->bodyClass = 'bm-promo goods-item';
			$viewName = '//catalog/vendor/bm/stores';
		} else {
			$viewName = '//catalog/vendor/stores';
		}

                $this->render($viewName, array(
                        'model'=>$model,
                        'sorted'=>$sorted,
                ));
        }

        public function actionStoresInCity($id, $cid)
        {
                $model = $this->loadModel($id);

                $city = City::model()->findByPk((int) $cid);
                if(!$city)
                        throw new CHttpException(404);

                $ids = $model->getStores();


		$criteria = new CDbCriteria(array(
			'join' => 'INNER JOIN cat_store_city as c ON c.store_id=t.id AND c.city_id=:cid AND store_id IN ('.$ids.')',
			'select' => 'DISTINCT t.*',
			'params' => array(':cid'=>$city->id),
		));
                $stores = Store::model()->findAll($criteria);
                $cities = array();

                /**
                 * Формирование выпадающего списка городов, в которых продают данный товар
                 */
                foreach($this->getStoreCities($model) as $ct)
                        $cities[$ct['id']] = $ct['name'];

                /**
                 * Формирование координат для карты
                 */
                $mapData = array();
                foreach($stores as $store) {
                        if($store->geocode) {
                                $geocode = unserialize($store->geocode);
                                if($geocode && is_array($geocode))
                                        $mapData[] = array(
                                                'coord'=>array($geocode[0], $geocode[1]),
                                                'baloonContent'=>$store->getBaloonContent(),
                                        );
                        }
                }

		$mall = Cache::getInstance()->mallBuild;
		if ($mall instanceof MallBuild) {
			$this->layout = '//layouts/layoutBm';
			$this->bodyClass = 'bm-promo goods-item';
			$viewName = '//catalog/vendor/bm/storesInCity';
		} else {
			$viewName = '//catalog/vendor/storesInCity';
		}

                $this->render($viewName, array(
                        'model'=>$model,
                        'stores'=>$stores,
                        'cities'=>$cities,
                        'city'=>$city,
                        'mapData'=>$mapData,
                ));
        }

        /**
         * Список товаров производителя
         * @param $id
         */
        public function actionProducts($id)
        {
		// SEO
		$uri = Yii::app()->getRequest()->getPathInfo();
		if ($uri == 'catalog/vendor/products/id/'.$id) {
			Yii::app()->getRequest()->redirect(
				$this->createUrl('/catalog/vendor', array_merge($_GET, array('id'=>$id, 'action'=>'products')) ),
				true,
				301
			);
		}
                $model = $this->loadModel($id);

                /**
                 * Инициализация текущего pagesize
                 * если pagesize нет в куках, то записывается стандартное значение pagesize в куки.
                 * если pagesize есть в куках, то проверяется ее корректность. в случае некорректного значения
                 * в куки записывается дефолтный pagesize в куки
                 */
                $cook_pagesize = Yii::app()->request->cookies['vendor_product_filter_pagesize'];
                if(!$cook_pagesize) {
                        $pagesize = Product::DEFAULT_PAGESIZE;
                } elseif($cook_pagesize && !isset(Config::$productFilterPageSizes[$cook_pagesize->value])) {
                        $pagesize = Product::DEFAULT_PAGESIZE;
                } else {
                        $pagesize = $cook_pagesize->value;
                }

                $cook_sort = Yii::app()->request->cookies['product_vendor_sort'];
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

                $condition = array("vendor_id={$model->id}");

                /**
                 * Формирование условия для выборки товаров по коллекциям
                 */
                $collection_id = (int) Yii::app()->request->getParam('collection_id');
                if($collection_id)
                        $condition[] = "t.collection_id = {$collection_id}";

                /**
                 * Формирование условия для выборки товаров по категориям
                 */
                $category_id = (int) Yii::app()->request->getParam('category_id');
                if($category_id)
                        $condition[] = "t.category_id = {$category_id}";

                if($collection_id) {
                        $nav_list = $this->getCollectionList($model, $collection_id);
                        $nav_type = 'collections';
                } else {
                        $nav_list = $this->getCategoryList($model, $category_id);
                        $nav_type = 'category';
                }

                /**
                 * DataProvider товаров
                 */
                $dataProvider = new CActiveDataProvider('Product', array(
                        'criteria'=>array(
                                'condition'=>implode(' AND ', $condition) . ' AND status=:stat',
                                'params'=>array(':stat'=>Product::STATUS_ACTIVE),
                                'order'=>$sortString,
                        ),
                        'pagination' 	=> array('pageSize' => intval($pagesize)),
                ));
                $dataProvider->getData();

		$mall = Cache::getInstance()->mallBuild;
		if ($mall instanceof MallBuild) {
			$this->layout = '//layouts/layoutBm';
			$this->bodyClass = 'bm-promo goods-item';
			$viewName = '//catalog/vendor/bm/products';
		} else {
			$viewName = '//catalog/vendor/products';
		}

                /**
                 * Рендер каталога
                 */
                $this->render($viewName, array(
                        'model'=>$model,
                        'pagesize'=>$pagesize,
                        'sort'=>$sort,
                        'dataProvider'=>$dataProvider,
                        'nav_type'=>$nav_type,
                        'nav_list'=>$nav_list,
                ));
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

                $collection_id = Yii::app()->request->getParam('collection_id');
                $category_id = Yii::app()->request->getParam('category_id');

                switch($type) {
                        case 'categories' :
                                $html = $this->getCategoryList($model, $category_id);
                                break;
                        case 'collections' :
                                $html = $this->getCollectionList($model, $collection_id);
                                break;
                        default :
                                $html = $this->getCategoryList($model, $category_id);
                                break;
                }

                die(CJSON::encode(array('html'=>$html)));
        }

        public function loadModel($id)
        {
                $model=Vendor::model()->findByPk((int) $id);
                if($model===null)
                        throw new CHttpException(404);
                return $model;
        }

        /**
         * Возвращает города, в которых есть магазины, продающие товар указанного производителя
         * @param $model - объект товара
         * @return array
         */
        private function getStoreCities($model, $orderByQt = false)
        {
                $store_ids = Yii::app()->db->createCommand()->from('cat_store_vendor')
                        ->select('store_id')->where('vendor_id=:vid', array(':vid'=>$model->id))->queryAll();
                $ids = array();

                foreach($store_ids as $id)
                        $ids[] = $id['store_id'];

                $ids = implode(',', $ids);

                if($ids) {
                        $query = Yii::app()->db->createCommand()->selectDistinct('c.id, c.name, count(s.store_id) as qt')->from(City::model()->tableName().' c')
				->join('cat_store_city s', 's.city_id=c.id and s.store_id in ('.$ids.')')
				->where('c.id is not null')
                                ->group('c.id');

                        if($orderByQt)
                                $query->order('qt desc');

                        $cities = $query->queryAll();
                }
                else
                        $cities = array();

                return $cities;
        }

        /**
         * Возвращает список li категорий, в которых есть товары указанного магазина
         * @param $model - магазин
         * @return string - список <li>
         */
        private function getCategoryList($model, $selected_id)
        {
                $categories = Yii::app()->db->createCommand()
                        ->select('c.id, c.name, count(p.id) as qt')->from('cat_category c')
                        ->rightJoin('cat_product p', 'c.id=p.category_id and p.vendor_id=:vid and p.status=:stat', array(':stat'=>Product::STATUS_ACTIVE, ':vid'=>$model->id))
                        ->where('(c.rgt-c.lft)=1')->group('c.id')->queryAll();

                $html = '';
                foreach($categories as $cat) {
                        if($selected_id == $cat['id'])
                                $class = 'current';
                        else
                                $class = '';

                        $html.=CHtml::openTag('li', array('class'=>$class))
                                . CHtml::link($cat['name'], $this->createUrl('/catalog/vendor', array('id'=>$model->id, 'action'=>'products', 'category_id'=>$cat['id'])))
                                . CHtml::tag('span', array(), $cat['qt'])
                                . CHtml::closeTag('li');
                }

                return $html;
        }

        /**
         * Возвращает список li коллекций производителя
         * @param $model Vendor - магазин
         * @return string - список <li>
         */
        private function getCollectionList($model, $selected_id)
        {
                $collections = $model->getCollectionsArray();

                $html = '';
                foreach($collections as $collection)
                {
                        if($selected_id == $collection['id'])
                                $class = 'current';
                        else
                                $class = '';

                        $product_qt = Product::model()->count('collection_id=:cid and status=:stat', array(':cid'=>$collection['id'], ':stat'=>Product::STATUS_ACTIVE));

                        $html.=CHtml::openTag('li', array('class'=>$class))
                                . CHtml::link($collection['name'], $this->createUrl('/catalog/vendor', array('id'=>$model->id, 'action'=>'products', 'collection_id'=>$collection['id'])))
                                . CHtml::tag('span', array(), $product_qt)
                                . CHtml::closeTag('li');
                }

                if(!$html)
                        $html = 'Нет коллекций';

                return $html;
        }
}