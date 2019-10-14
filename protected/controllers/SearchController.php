<?php

/**
 * @brief Различные типы поиска по сайту с использованием Sphinx
 * @author Kuzakov Roman <roman.kuzakov@gmail.com>
 */
class SearchController extends FrontController
{

        private $splitedQuerys = array();

        public function filters()
        {
                return array('accessControl');
        }

        /**
         * @brief Разрешает доступ всем пользователям
         * @return array
         */
        public function accessRules()
        {

                return array(
                    array('allow',
                        'users' => array('*'),
                    ),
                );
        }

        public function beforeAction($action)
        {
		if (parent::beforeAction($action)) {
			Yii::import('application.modules.idea.models.*');
			Yii::import('application.modules.catalog.models.*');
			Yii::import('application.modules.content.models.SearchMeans');
			Yii::import('application.modules.social.models.*');
			Yii::import('application.modules.media.models.*');
			Yii::import('application.modules.tenders.models.Tender');
			return true;
		}
        }

        /**
         * Глобальный поиск по сайту (с указанием раздела для поиска)
         * @param string $q
         * @throws CHttpException
         */
        public function actionIndex($q='')
        {
                $totalCount = 0;

                if(!$q)
                        throw new CHttpException(400, 'Не указана поисковая фраза');

                $sphinxClient = Yii::app()->search;

                $ideaProvider = $this->_ideaSearch($q, $sphinxClient);
                $specProvider = $this->_specialistSearch($q, $sphinxClient);
                $prodProvider = $this->_productSearch($q, $sphinxClient);
                $forumProvider = $this->_forumSearch($q, $sphinxClient);
                $mediaProvider = $this->_mediaSearch($q, $sphinxClient);
                $tenderProvider = $this->_tenderSearch($q, $sphinxClient);
                $meansProvider = $this->_meansSearch($q, $sphinxClient);
                $storeProvider = $this->_storeSearch($q, $sphinxClient);

                // общее количество нахождений
		if (!is_null($ideaProvider))
			$totalCount += $ideaProvider->getTotalItemCount();
		if (!is_null($specProvider))
			$totalCount += $specProvider->getTotalItemCount();
		if (!is_null($prodProvider))
			$totalCount += $prodProvider->getTotalItemCount();
		if (!is_null($forumProvider))
			$totalCount += $forumProvider->getTotalItemCount();
		if (!is_null($mediaProvider))
			$totalCount += $mediaProvider->getTotalItemCount();
		if (!is_null($tenderProvider))
			$totalCount += $tenderProvider->getTotalItemCount();
		if (!is_null($storeProvider))
			$totalCount += $storeProvider->getTotalItemCount();


                $this->render('//search/index', array(
			'ideaProvider'   => $ideaProvider,
			'specProvider'   => $specProvider,
			'prodProvider'   => $prodProvider,
			'forumProvider'  => $forumProvider,
			'mediaProvider'  => $mediaProvider,
			'tenderProvider' => $tenderProvider,
			'meansProvider'  => $meansProvider,
			'storeProvider'  => $storeProvider,
			'query'          => $q,
			'totalCount'     => $totalCount,
                ));
        }

        public function actionAjaxAutocomplete($term)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $q = $term;

                $sphinxClient = Yii::app()->search;
                $ideaProvider = $this->_ideaSearch($q, $sphinxClient, 3);
                $specProvider = $this->_specialistSearch($q, $sphinxClient, 3);
                $prodProvider = $this->_productSearch($q, $sphinxClient, 3);
                $forumProvider = $this->_forumSearch($q, $sphinxClient, 3);
                $mediaProvider = $this->_mediaSearch($q, $sphinxClient, 3);
                $tenderProvider = $this->_tenderSearch($q, $sphinxClient, 3);
		$storeProvider = $this->_storeSearch($q, $sphinxClient, 3);

                $result = array();

		foreach ($prodProvider->getData() as $data) {
			$result[] = array(
				'label'        => $data->name,
				'type'         => 1,
				'category'     => 'Товары (' . $prodProvider->totalItemCount . ')',
				'categoryLink' => $this->createUrl('details', array('t' => 'product', 'q' => $q)),
				'itemLink'     => Product::getLink($data->id, null, $data->category_id)
			);
		}
		foreach ($ideaProvider->getData() as $data) {
			$result[] = array(
				'label' => $data->name,
				'type' => 2,
				'category' => 'Идеи (' . $ideaProvider->totalItemCount . ')',
				'categoryLink' => $this->createUrl('details', array('t' => 'idea', 'q' => $q)),
				'itemLink' => $data->object->getIdeaLink()
			);
		}
		foreach ($specProvider->getData() as $data) {
			$result[] = array(
				'label' => $data->name,
				'type' => 2,
				'category' => 'Специалисты (' . $specProvider->totalItemCount . ')',
				'categoryLink' => $this->createUrl('details', array('t' => 'specialist', 'q' => $q)),
				'itemLink' => $data->getLinkProfile()
			);
		}
		foreach ($mediaProvider->getData() as $data) {
			$result[] = array(
				'label'        => $data->name,
				'type'         => 3,
				'category'     => 'Журнал (' . $mediaProvider->totalItemCount . ')',
				'categoryLink' => $this->createUrl('details', array('t' => 'media', 'q' => $q)),
				'itemLink'     => $data->object->getElementLink()
			);
		}
		foreach ($storeProvider->getData() as $data) {
			$result[] = array(
				'label'        => $data->name,
				'type'         => 3,
				'category'     => 'Магазины (' . $tenderProvider->totalItemCount . ')',
				'categoryLink' => $this->createUrl('details', array('t' => 'store', 'q' => $q)),
				'itemLink'     => $data->getLink($data->id)
			);
		}
		foreach ($forumProvider->getData() as $data) {
			$result[] = array(
				'label'        => $data->name,
				'type'         => 3,
				'category'     => 'Форум (' . $forumProvider->totalItemCount . ')',
				'categoryLink' => $this->createUrl('details', array('t' => 'forum', 'q' => $q)),
				'itemLink'     => $data->getElementLink()
			);
		}
		foreach ($tenderProvider->getData() as $data) {
			$result[] = array(
				'label'        => $data->name,
				'type'         => 3,
				'category'     => 'Заказы (' . $tenderProvider->totalItemCount . ')',
				'categoryLink' => $this->createUrl('details', array('t' => 'tender', 'q' => $q)),
				'itemLink'     => $data->getLink()
			);
		}

                die(json_encode($result));
        }

        /**
         * Расширенные результаты поиска по указанному разделу
         * @param $t - раздел поиска (specialist, product, forum, tender, idea, media)
         * @param $q - ключевая фраза для поиска
         * @throws CHttpException
         */
        public function actionDetails($t, $q)
        {
                $t = strtolower($t);
                $searchMethodName = '_' . $t . 'Search';

                if(!method_exists($this, $searchMethodName))
                        throw new CHttpException(404);

                if (Yii::app()->request->getParam('pagesize'))
                        $pagesize = Yii::app()->request->getParam('pagesize');
                else
                        $pagesize = Yii::app()->session->get('search_pagesize');

                $pagesize = empty(Config::$searchPageSizes[(int)$pagesize]) ? key(Config::$searchPageSizes) : (int) $pagesize;
                Yii::app()->session->add('search_pagesize', $pagesize);

                $sphinxClient = Yii::app()->search;;
                $dataProvider = $this->$searchMethodName($q, $sphinxClient, $pagesize);
                $dataProvider->getData();

                $this->render($t, array(
                        'dataProvider'=>$dataProvider,
                        'query'=>$q,
                        'pagesize'=>$pagesize,
                ));
        }

        /**
         * Поиск по идеям
         * @param string $query Поисковый запрос
         * @param $sphinxClient - объект клиента сфинкса
         * @param integer $pagesize
         * @return CSphinxDataProvider 
         */
        private function _ideaSearch($query, $sphinxClient, $pagesize = 4)
        {
                $query_str = $this->getSplitedSphinxQuery($query, $sphinxClient);

		$dataProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'idea',
				'modelClass' => Idea::model(),
				'filters' => array('status' => array(Interior::STATUS_ACCEPTED, Interior::STATUS_CHANGED)),
				'query' => $query_str,
                                'weight' => array(
                                        'heap'=>70,
                                        'room_name'=>70,
                                        'color'=>40,
                                        'style'=>30,
                                        'name'=>150,
                                ),
				'pagination' => array('pageSize' => $pagesize),
			));

                return $dataProvider;
        }

        /**
         * Поиск по тендерам
         * @param $query
         * @param $sphinxClient
         * @param int $pagesize
         * @return CSphinxDataProvider
         */
        private function _tenderSearch($query, $sphinxClient, $pagesize = 4)
        {
                $query_str = $this->getSplitedSphinxQuery($query, $sphinxClient);

                $dataProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'tender',
                        'modelClass' => 'Tender',
                        'filters' => array('status' => array(Tender::STATUS_OPEN, Tender::STATUS_CHANGED, Tender::STATUS_CLOSED)),
                        'query' => $query_str,
                        'weight' => array(
                                'name'=>10,
                                'section_name'=>20,
                                'description'=>50,
                        ),
                        'pagination' => array('pageSize' => $pagesize),
                ));

                return $dataProvider;
        }

	/**
	 * Поиск по магазинам
	 * @param $query
	 * @param $sphinxClient
	 * @param int $pagesize
	 * @return CSphinxDataProvider
	 */
	private function _storeSearch($query, $sphinxClient, $pagesize = 4)
	{
		$query_str = $this->getSplitedSphinxQuery($query, $sphinxClient);

		$dataProvider = new CSphinxDataProvider($sphinxClient, array(
			'index'      => 'store',
			'modelClass' => 'Store',
			'query'      => $query_str,
			'weight'     => array(
				'name'    => 50,
				'address' => 10,
			),
			'pagination' => array('pageSize' => $pagesize),
		));

		return $dataProvider;
	}

        /**
         * Поиск по журналу
         * @param string $query Поисковый запрос
         * @param $sphinxClient - объект клиента сфинкса
         * @param integer $pagesize
         * @return CSphinxDataProvider
         */
        private function _mediaSearch($query, $sphinxClient, $pagesize = 4)
        {
                $query_str = $this->getSplitedSphinxQuery($query, $sphinxClient);

                $dataProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'media',
                        'modelClass' => Media::model(),
                        'query' => $query_str,
                        'weight' => array(
                                'name'=>70,
                                'description'=>5,
                        ),
                        'pagination' => array('pageSize' => $pagesize),
                ));

                return $dataProvider;
        }

        /**
         * Поиск по специалистам
         * @param string $query Поисковый запрос
         * @param $sphinxClient - объект клиента сфинкса
         * @param integer $pagesize
         * @return CSphinxDataProvider 
         */
        private function _specialistSearch($query, $sphinxClient, $pagesize = 4)
        {
                $query_str = $this->getSplitedSphinxQuery($query, $sphinxClient);

                $dataProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'user_service',
                        'modelClass'	=> 'User',
                        'query'		=> $query_str,
                        'matchMode'	=> SPH_MATCH_EXTENDED,
                        'group' => array('field'=>'user_id','mode'=>SPH_GROUPBY_ATTR, 'order'=>'@weight desc'),
                        'filters' => array('role' => array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR)),
                        'weight' => array(
                                'name'=>10,
                                'login'=>10,
                                'city_name'=>9,
                                'services_name'=>50,
                        ),
                        'pagination' => array('pageSize' => $pagesize),
                ));
                $dataProvider->getData();

		return $dataProvider;
        }

        /**
         * Поиск по товарам
         * @param string $query
         * @param $sphinxClient - объект клиента сфинкса
         * @param integer $pagesize
         * @return CSphinxDataProvider
         */
        private function _productSearch($query, $sphinxClient, $pagesize = 4)
        {
                $query_str = $this->getSplitedSphinxQuery($query, $sphinxClient);
		$filters = array();

		$mallBuild = Cache::getInstance()->mallBuild;
		if ( $mallBuild !== null && $mallBuild instanceof MallBuild ) {
			$filters['mall_ids'] = $mallBuild->id;
		}

                $dataProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'product',
                        'modelClass' => 'Product',
                        'query' => $query_str,
                        'matchMode' => SPH_MATCH_EXTENDED,
			'filters'          => $filters,
                        'weight' => array(
                                'name'=>100,
                                'colors'=>30,
                                'desc'=>0,
                                'vendor_name'=>5,
                                'country_name'=>5,
                                'styles'=>15,
                                'rooms'=>7,
                        ),
                        'pagination' => array('pageSize' => $pagesize),
                ));
                return $dataProvider;
        }

        /**
         * Поиск по топикам форума
         * @param $query
         * @param $sphinxClient
         * @param int $pagesize
         * @return CSphinxDataProvider
         */
        private function _forumSearch($query, $sphinxClient, $pagesize = 4)
        {
                $query_str = $this->getSplitedSphinxQuery($query, $sphinxClient);

                $dataProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'forum_topic',
                        'modelClass' => 'ForumTopic',
                        'query' => $query_str,
                        'matchMode' => SPH_MATCH_EXTENDED,
                        'weight' => array(
                                'name'=>10,
                                'section_name'=>20,
                                'description'=>50,
                        ),
                        'pagination' => array('pageSize' => $pagesize),
                ));

                return $dataProvider;
        }

        private function _meansSearch($query, $sphinxClient, $pagesize = 4)
        {
                $query_str = $this->getSplitedSphinxQuery($query, $sphinxClient);

                $dataProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'search_means',
                        'modelClass' => 'SearchMeans',
                        'query' => $query_str,
                        'matchMode' => SPH_MATCH_EXTENDED,
                        'pagination' => array('pageSize' => $pagesize),
                ));

                return $dataProvider;
        }

        /**
         * Формирует строку запроса в сфинкс
         * @param $query - ключевая фраза
         * @param $sphinxClient - объект клиента сфинкса
         * @return string
         */
        private function getSplitedSphinxQuery($query, $sphinxClient)
        {
                $key = crc32($query);
                if(!isset($this->splitedQuerys[$key])) {

                        $query = $sphinxClient->EscapeString($query);
                        $query_split=preg_split('/[\s,-]+/', $query, 5);
                        $query_arr = array();
                        if ($query_split) {
                                foreach($query_split as $k=>$q) {
                                        if(mb_strlen($q, 'utf-8') < 1) continue;
                                        $query_arr[$k] = '('.$q.' | *'.$q.'*)';
                                }
                        }
                        $query_str = implode(' | ', $query_arr);
                        $this->splitedQuerys[$key] = $query_str;
                }
                return $this->splitedQuerys[$key];
        }


}