<?php

class ChainController extends FrontController
{
        /**
         * Список магазинов
         */
        public function actionIndex($id)
        {
                $model = $this->loadModel($id);
                $cities = $this->getStoreCities($model);

                $this->render('//catalog2/chain/index', array(
                        'model'=>$model,
                        'cities'=>$cities,
                ));
        }

        public function actionStores($id)
        {
                $model = $this->loadModel($id);
                $cities = $this->getStoreCities($model, 500);

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

                $this->render('//catalog2/chain/stores', array(
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

                $ids = $model->getStores(false, true);
                $stores = Store::model()->getStoreInCity($ids, $city->id);
                $cities = array();

                /**
                 * Формирование выпадающего списка городов, в которых продают данный товар
                 */
                foreach($this->getStoreCities($model, 300) as $ct)
                        $cities[$ct['id']] = $ct['name'];

                /**
                 * Формирование координат для карты
                 */
                $mapData = array();
                foreach($stores as $store) {
                        if($store->geocode) {
                                $geocode = unserialize($store->geocode);
                                if($geocode && is_array($geocode) && isset($geocode[0]) && isset($geocode[1]))
                                        $mapData[] = array(
                                                'coord'=>array($geocode[0], $geocode[1]),
                                                'baloonContent'=>$store->getBaloonContent(),
                                        );
                        }
                }

                $this->render('//catalog2/chain/storesInCity', array(
                        'model'=>$model,
                        'stores'=>$stores,
                        'cities'=>$cities,
                        'city'=>$city,
                        'mapData'=>$mapData,
                ));
        }

        public function loadModel($id)
        {
                $model=Chain::model()->findByPk((int) $id);
                if($model===null)
                        throw new CHttpException(404);
                return $model;
        }

        /**
         * Возвращает города, в которых есть магазины, входящие в состав сети
         * @param $model - объект товара
         * @return array
         */
        private function getStoreCities($model, $limit = 5)
        {
                $store_ids = Yii::app()->dbcatalog2->createCommand()->from('cat_chain_store')
                        ->select('store_id')->where('chain_id=:cid', array(':cid'=>$model->id))->queryAll();
                $ids = array();

                foreach($store_ids as $id)
                        $ids[] = $id['store_id'];

                $ids = implode(',', $ids);

                if($ids)
                        $cities = Yii::app()->dbcatalog2->createCommand()->select('c.id, c.name, count(s.store_id) as qt')->from(City::model()->tableName().' c')
				->join('cat_store_city s', 's.city_id=c.id and s.store_id in ('.$ids.')')
                                ->order('c.name')->limit($limit)->group('c.id')->where('c.id is not null')->queryAll();
                else
                        $cities = array();

                return $cities;
        }
}