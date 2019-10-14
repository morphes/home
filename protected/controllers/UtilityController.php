<?php
/**
 * Контроллер общих методов (без проверки доступа)
 * например автокомплиты
 */
class UtilityController extends CController
{
	/**
	 * @brief Автокомплит для Городов
	 */
	public function actionAutocompletecity($term)
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(404);

		if (empty($term))
			die( json_encode(array()) );

		$sphinxClient = Yii::app()->search;
		$term = $sphinxClient->EscapeString($term);

		$cityProvider = new CSphinxDataProvider($sphinxClient, array(
                     'index' => 'city_name',
                     'modelClass' => 'City',
                     'query' => $term . '*',
                     'sortMode' => SPH_SORT_EXTENDED,
                     'sortExpr' => 'rating asc',
                     'matchMode' => SPH_MATCH_ANY,
                     'pagination' => array('pageSize' => 10),
		));
		$cities = $cityProvider->getData();
		$arr = array();

		/** @var $city City */
		foreach ($cities as $city) {
			$value = $city->name . ' (' . $city->region->name . ', ' . $city->country->name . ')';
			$arr[] = array(
				'label' => $value,
				'value' => $value,
				'path' => $city->eng_name,
				'id' => $city->id, // return value from autocomplete
				'country_id'=>$city->country_id,
			);
		}

		die ( json_encode($arr, JSON_NUMERIC_CHECK) );
	}

	/**
	 * @return array массив имен пользователей и их id.
	 * Функцию использует форма с автокомплитом имен пользователей
	 * @author Alexey Shvedov
	 * @param string $term
	 * @see CSphinxDataProvider
	 */
	function actionAutocompleteuser($term)
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(404);

		if (empty($term))
			die( json_encode(array()) );

		$sphinxClient = Yii::app()->search;
		$term = $sphinxClient->EscapeString($term);

		$userProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'user_login',
									     'modelClass' => 'User',
									     'query' => $term . '*',
									     'matchMode' => SPH_MATCH_ANY,
									     'filters' => array('status' => User::STATUS_ACTIVE),
									     'pagination' => array('pageSize' => Config::$ACompletePageSize),
		));
		$users = $userProvider->getData();
		$arr = array();

		foreach ($users as $user) {
			$value = $user->name . " ({$user->login})";
			$arr[] = array(
				'label' => $value, // label for dropdown list
				'value' => $value, // value for input field
				'id' => $user->id, // return value from autocomplete
				'profileLink' => Yii::app()->homeUrl.$user->getLinkProfile(),
			);
		}
		die ( json_encode($arr, JSON_NUMERIC_CHECK) );
	}

        /**
         * Автокомплит по странам
         * @param $term
         * @throws CHttpException
         */
        public function actionAutocompleteCountry($term)
        {
                if (!Yii::app()->getRequest()->getIsAjaxRequest())
                        throw new CHttpException(404);

                if (empty($term))
                        die( json_encode(array()) );

                $criteria = new CDbCriteria();
                $criteria->compare('name', $term, true);
                $countries = Country::model()->findAll($criteria);
		$arr = array();
                foreach ($countries as $c) {
                        $arr[] = array(
                                'label' => $c->name, // label for dropdown list
                                'value' => $c->name, // value for input field
                                'id' => $c->id, // return value from autocomplete
                        );
                }

                die ( json_encode($arr, JSON_NUMERIC_CHECK) );
        }

	/**
	 * Автокомплит по регионам
	 * @param $term
	 * @throws CHttpException
	 */
	public function actionAutocompleteRegion($term)
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(404);

		if (empty($term))
			die( json_encode(array()) );

		$criteria = new CDbCriteria();
		$criteria->compare('name', $term, true);
		$countries = Region::model()->findAll($criteria);
		$arr = array();
		foreach ($countries as $c) {
			$arr[] = array(
				'label' => $c->name, // label for dropdown list
				'value' => $c->name, // value for input field
				'id' => $c->id, // return value from autocomplete
			);
		}

		die ( json_encode($arr, JSON_NUMERIC_CHECK) );
	}

	/**
	 * Автокомплит по услугам
	 * @param $term
	 * @throws CHttpException
	 */
	public function actionAutocompleteService($term)
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(404);

		if (empty($term))
			die( json_encode(array()) );

		$criteria = new CDbCriteria();
		$criteria->compare('name', $term, true);
		$services = Service::model()->findAll($criteria);
		$arr = array();
		foreach ($services as $s) {
			$arr[] = array(
				'label' => $s->name, // label for dropdown list
				'value' => $s->name, // value for input field
				'id' => $s->id, // return value from autocomplete
			);
		}

		die ( json_encode($arr, JSON_NUMERIC_CHECK) );
	}

	/**
	 * Автокомплит по городу - услуге
	 * @param $term
	 * @throws CHttpException
	 */
	public function actionAutocompleteUserCity($userId, $term)
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(404);

		if (empty($term))
			die( json_encode(array()) );

		if (empty($userId))
			die( json_encode(array()) );

		$userId = (int)$userId;

		$sql='SELECT city_id FROM myhome_user_service WHERE user_id=:userId GROUP BY city_id LIMIT 100000';
		$userCityIds = Yii::app()->sphinx->createCommand($sql)
			->bindParam(":userId", $userId)
			->queryColumn();

		$criteria = new CDbCriteria();
		$criteria->compare('name', $term, true);
		$services = City::model()->findAll($criteria);
		$arr = array();
		foreach ($services as $s) {
			//Надо переделать на более быстрый вариант
			if(in_array($s->id, $userCityIds)) {
				$arr[] = array(
					'label' => $s->name, // label for dropdown list
					'value' => $s->name, // value for input field
					'id' => $s->id, // return value from autocomplete
				);
			}
		}

		die ( json_encode($arr, JSON_NUMERIC_CHECK) );
	}


	/**
	 * Скрытие блока создания магазина
	 * @throws CHttpException
	 */
	public function actionCloseStoreBlock()
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(400);

		Yii::app()->getUser()->setState('showCreteStoreBlock', false);

		Yii::app()->end( json_encode( array('success'=>true), JSON_NUMERIC_CHECK ) );
	}

        /**
         * Очистка кешей APC
         */
        public function actionApcClear()
        {
                if ( Yii::app()->request->userHostAddress == '127.0.0.1' ) {
                        if ( apc_clear_cache() && apc_clear_cache('user') )
                                echo 'APC was cleared';
                } else {
                        throw new CHttpException(404);
                }
        }

        /**
         * Автокомплит товаров
         * @param $term
         */
        public function actionAcProduct($term)
        {
                Yii::import('catalog.models.*');

                $filters = array();

                $sphinxClient = Yii::app()->search;
                $store_id = Yii::app()->request->getParam('store_id');
                $query = $sphinxClient->EscapeString($term);
                $query_split=preg_split('/[\s,-]+/', $query, 5);
                $query_arr = array();
                if ($query_split) {
                        foreach($query_split as $k=>$q) {
                                if(mb_strlen($q, 'utf-8') < 1) continue;
                                $query_arr[$k] = '('.$q.' | *'.$q.'*)';
                        }
                }
                $query_str = implode(' | ', $query_arr);

                $pageSize = 10;

                if ($store_id)
                        $filters['store_ids'] = (int) $store_id;

                $dataProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'product',
                        'modelClass' => 'Product',
                        'query' => $query_str,
                        'filters' => $filters,
                        'matchMode' => SPH_MATCH_EXTENDED,
                        'pagination' => array('pageSize' => $pageSize),
                ));

                $arr = array();

                // вывод товаров магазина
                if ($store_id) {

                        $connection = Yii::app()->db;
                        $counter = 0;
                        $iterator=new CDataProviderIterator($dataProvider, $pageSize);

                        foreach($iterator as $data) {

                                if ($counter > $pageSize - 1)
                                        break;

                                $by_vendor = $connection->createCommand()->select('by_vendor')->from('cat_store_price')
                                        ->where('product_id=:pid and store_id=:sid', array(':pid'=>$data->id, ':sid'=>(int) $store_id))->queryScalar();

                                if ($by_vendor) continue;

                                $value = $data->name . ' ' . ($data->vendor ? '(' . $data->vendor->name . ')' : '');
                                $arr[] = array(
                                        'label' => $value,
                                        'value' => $value,
                                        'id' => $data->id,
                                );
                                $counter++;
                        }
                // вывод всех товаров МХ
                } else {
                        foreach($dataProvider->getData() as $data) {
                                $value = $data->name . ' ' . ($data->vendor ? '(' . $data->vendor->name . ')' : '');
                                $arr[] = array(
                                        'label' => $value,
                                        'value' => $value,
                                        'id' => $data->id,
                                );
                        }
                }

                die ( json_encode($arr, JSON_NUMERIC_CHECK) );
        }


	/**
	 * Счетчик кликов по баннерам
	 * @param $bid
	 *
	 * @throws CHttpException
	 */
	public function actionBannerClick($bid)
	{
		Yii::import('admin.models.BannerItem');

		$banner = BannerItem::model()->findByPk((int) $bid);

		if ( !$banner || $banner->status != BannerItem::STATUS_ACTIVE )
			throw new CHttpException(404);

		Yii::app()->redis->incr(BannerItem::REDIS_STAT_CLICKS_VAR . $banner->id);

		return $this->redirect(Amputate::absoluteUrl($banner->url), true, 301);
	}

	public function actionAcSpecialist($term)
	{
		if (!Yii::app()->getRequest()->getIsAjaxRequest())
			throw new CHttpException(404);

		if (empty($term))
			die( json_encode(array()) );

		$sphinxClient = Yii::app()->search;
		$term = $sphinxClient->EscapeString($term);

		$dataProvider = new CSphinxDataProvider($sphinxClient, array(
			'index' => 'user_login',
			'modelClass' => 'User',
			'query' => $term . '*',
			'matchMode' => SPH_MATCH_ANY,
			'filters' => array('status' => User::STATUS_ACTIVE, 'role'=>array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR)),
			'pagination' => array('pageSize' => Config::$ACompletePageSize)
		));
		$users = $dataProvider->getData();
		$arr = array();



		/** @var $city City */
		foreach ($users as $user) {
			$arr[] = array(
				'label' => $user->name,
				'itemId' => $user->id,
				'itemImg' => '/' . $user->getPreview(User::$preview['crop_30']),
			);
		}

		die ( json_encode($arr, JSON_NUMERIC_CHECK) );
	}

}