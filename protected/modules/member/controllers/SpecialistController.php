<?php

class SpecialistController extends FrontController
{
	public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
                    array('allow',
                        'actions' => array('list', 'servicelist', 'quickSearch', 'priority', 'GetPayFormAjax', 'GetRate'),
                        'users' => array('*'),
                    ),
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }
	
	/**
	 * Главная страница раздела "Специалисты"
	 */
//	public function actionIndex()
//	{
//                Yii::import('application.modules.idea.models.*');
//
//		$this->useEmptyMenu = true;
//		$this->menuActiveKey = 'specialists';
//
//		$city = Cache::getInstance()->city;
//
//		$this->render('index', array(
//                    'city' => $city,
//                ));
//	}

	/**
	 * Загружаемый аяксом список услуг 
	 */
	public function actionServicelist()
	{
		$this->layout = false;

		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);

		$serviceId = (int)Yii::app()->request->getParam('service_id');
		$cityId = isset($_COOKIE[Geoip::COOKIE_GEO_SELECTED]) ? intval($_COOKIE[Geoip::COOKIE_GEO_SELECTED]) : 0;

		// Получаем кол-во спецов по услугам
		$servicesQt = ServiceUser::getUserQtByCity($cityId);

		// Общее кол-во услуг
		$totalQt = 0;
		$services = array();
		foreach ($servicesQt as $sid=>$qt) {
			$model = Service::model()->findByPk($sid);
			if ($model && $model->parent_id > 0) {
				$services[$model->parent_id][] = $model;
				$totalQt++;
			}
		}

		$html = $this->renderPartial('_serviceList', array(
			'services'    => $services,
			'servicesQt'  => $servicesQt,
			'totalQt'     => $totalQt,
			'serviceId'   => $serviceId,
			'cityId'      => $cityId
		), true);

		// TODO: cache output data
		die(CJSON::encode(array('success' => true, 'html' => $html)) );
	}
	
	/**
	 * Перечень специалистов с фильтром 
	 */
	public function actionList()
	{
		$this->menuActiveKey = 'specialists';
		$this->menuIsActiveLink = true;

		$this->bodyClass = array('specialists', 'specialists-list');
		$this->layout = '//layouts/grid_main';

		$sphinxClient = Yii::app()->search;

		Yii::import('application.modules.idea.models.*');
		$sortType = Yii::app()->request->getParam('sorttype');
		//		$sortType = isset(Config::$specSortNames[$sortType]) ? $sortType : Config::SPEC_SORT_DEFAULT;
		$sortType = isset(Config::$specSortNames[$sortType]) ? $sortType
			: Config::SPEC_SORT_RATING;

		$pageSize = 10;

		$specProviderPaid = array();

		$name = Yii::app()->request->getParam('company', '');


		$service = Cache::getInstance()->serviceUser;
		if (!$service instanceof Service) {
			$service = null;
		}

		$city = Cache::getInstance()->city;
		if (is_null($city)) {
			$cityId = 0;
		} else {
			$cityId = $city->id;
		}

		// build query
		$query = '';
		if (!empty($name))
			$query .= '@name "' . $sphinxClient->escapeString($name) . '*" ';

		// build filters
		$filters = array('status' => array(User::STATUS_ACTIVE));


		$sortString = '';
		if ($service !== null) {
			$filters['service_id'] = $service->id;
			//$filters['paid'] = 0;
			$additionalAttr = array(
				'rating'         => 'rating',
				'experience'     => 'experience',
				'project_qt'     => 'count_interior',
				'service_expert' => 'service_expert',
			);

			// build sort string
			switch ($sortType) {
				//			case Config::SPEC_SORT_DEFAULT:
				//				$sortString = 'rating DESC, create_time DESC';
				//				break;
				case Config::SPEC_SORT_RATING:
					$sortString = 'rating DESC, create_time DESC';
					break;
				case Config::SPEC_SORT_PROJECTS:
					$sortString = 'project_qt DESC, create_time DESC';
					break;
				default:
					$sortString = 'rating DESC, create_time DESC';
					break;
			}
		} else {

			$additionalAttr = array(
				'total_rating'   => 'rating',
				'experience'     => 'experience',
				'total_qt'       => 'count_interior',
				'service_expert' => 'service_expert',
			);

			// build sort string
			switch ($sortType) {
				//			case Config::SPEC_SORT_DEFAULT:
				//				$sortString = 'rating DESC, create_time DESC';
				//				break;
				case Config::SPEC_SORT_RATING:
					$sortString = 'total_rating DESC, create_time DESC';
					break;
				case Config::SPEC_SORT_PROJECTS:
					$sortString = 'total_qt DESC, create_time DESC';
					break;
				default:
					$sortString = 'total_rating DESC, create_time DESC';
					break;
			}
		}

		if ($cityId) {
			$filters['city_id'] = array($cityId);
			//$filters['in_main'] = 0;
		}

		$sortString .= ' @group ASC';
		$specProvider = new CSphinxDataProvider($sphinxClient, array(
			'index'          => 'user_service',
			'modelClass'     => 'User',
			'query'          => $query,
			'matchMode'      => SPH_MATCH_EXTENDED,
			'pagination'     => array('pageSize' => $pageSize),
			'additionalAttr' => $additionalAttr,
			'group'          => array('field' => 'user_id', 'mode' => SPH_GROUPBY_ATTR, 'order' => $sortString),
			'filters'        => $filters,
		));

		$paidSpecialists = array();
		if ($cityId && $specProvider->pagination->currentPage == 0) {

			if (is_null($service)) {
				$filtersPaid['in_main']=1;
			}
			else {
				$filtersPaid['service_id'] = $service->id;
			}

				$filtersPaid['paid'] = 1;
				$filtersPaid['city_id'] = $cityId;

				$sortStringPaid = 'id DESC';

				$specProviderPaid = new CSphinxDataProvider($sphinxClient, array(
					'index'          => 'user_service',
					'modelClass'     => 'User',
					'query'          => '',
					'matchMode'      => SPH_MATCH_EXTENDED,
					'additionalAttr' => $additionalAttr,
					'pagination'     => array('pageSize' => 100),
					'filters'        => $filtersPaid,
					'group'          => array('field' => 'user_id', 'mode' => SPH_GROUPBY_ATTR, 'order' => $sortStringPaid),
				));

		}

		/** Список всех услуг */
		$services = Service::model()->findAll(array('order' => 'case when parent_id = 0 then id else parent_id end,parent_id', 'limit' => 200));

		$this->render('list', array(
			'sortType'         => $sortType,
			'pageSize'         => $pageSize,
			'specProvider'     => $specProvider,
			'service'          => $service,
			'cityId'           => $cityId,
			'city'             => $city,
			'name'             => $name,
			'services'         => $services,
			'specProviderPaid' => $specProviderPaid
		));
	}
	

        /**
         * Быстрый полнотекстовый поиск специалистов и услуг
         * @throws CHttpException
         */
        public function actionQuickSearch($term)
        {
                if(!Yii::app()->request->isAjaxRequest || !$term)
                        throw new CHttpException(400);

                $this->layout = false;
                $sphinxClient = Yii::app()->search;
                $html = '';
                $foundedText = '';

                // Поиск услуг
                if(Yii::app()->request->getParam('with_services')) {

                        $servProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'service_synonym',
                                'modelClass'	=> 'Service',
                                'query'	=> '@synonym "'.$term.'*" ',
                                'matchMode'	=> SPH_MATCH_EXTENDED,
                                'pagination'	=> array('pageSize' => 100),
                                'additionalAttr'=> array(
                                        'original_key' => 'synonym_id',
                                        'is_servicename' => 'founded_by_name',
                                ),
                                'group' => array('field'=>'service_id','mode'=>SPH_GROUPBY_ATTR, 'order'=>'@weight DESC', 'save_original_key'=>true),
                        ));
                        $html.= $this->renderPartial('_quickSearchServ', array('servProvider'=>$servProvider, 'term'=>$term), true);
                }

                // Поиск специалистов
                $specProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'user_service',
                        'modelClass'	=> 'User',
                        'query'	=> '@name "'.$term.'*" ',
                        'matchMode'	=> SPH_MATCH_EXTENDED,
                        'pagination'	=> array('pageSize' => 10),
                        'additionalAttr'=> array(
                                'project_qt' => 'count_interior',
                        ),
                        'group' => array('field'=>'user_id','mode'=>SPH_GROUPBY_ATTR, 'order'=>'@weight DESC'),
                ));

                // если запрос общий (не пагинация поспецам), то формируется текст о кол-ве найденных элементов
                if(Yii::app()->request->getParam('with_services')) {
                        $specTotalItemCount = CFormatterEx::formatNumeral($specProvider->getTotalItemCount(), array('специалист', 'специалиста', 'специалистов'));
                        $servTotalItemCount = CFormatterEx::formatNumeral($servProvider->getTotalItemCount(), array('услуга', 'услуги', 'услуг'));
                        $foundedText = 'Найдено ' . $servTotalItemCount . ' и ' . $specTotalItemCount;
                }


                $html.= $this->renderPartial('_quickSearchSpec', array('specProvider'=>$specProvider, 'term'=>$term), true);
                die(CJSON::encode(array('html'=>$html, 'founded_text'=>$foundedText)));
        }


	/**
	 * Страница с описанием приоритезации спецов
	 */
	public function actionPriority()
	{
throw new CHttpException(404);
return;
		$this->layout = '//layouts/new_main';
		$this->bodyClass = 'adv-style adv-account-style';

		$this->render('priority', array());
	}


	public function actionGetPayFormAjax()
	{
		$serviceList = array();
		$serviceModel = null;

		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(404);
		}

		$post = Yii::app()->request->getPost('item');

		$userModel = User::model()->findByPk((int)$post['id']);

		if (!$userModel) {
			throw new CHttpException(404);
		}

		$serviceList = $userModel->getServiceList();

		if (isset($post['serviceId']) && !empty($post['serviceId'])) {
			$serviceModel = Service::model()->findByPk((int)$post['serviceId']);

			if (!$serviceModel) {
				throw new CHttpException(404);
			}

			$serviceId = (int)$post['serviceId'];
		} else {
			$serviceTmp = reset($serviceList);

			$serviceId = (int)$serviceTmp['service_id'];

			$serviceModel = Service::model()->findByPk($serviceId);
		}

		$userCity = Yii::app()->user->getSelectedCity();
		if (!$userCity) {
			$userCity = Yii::app()->user->getDetectedCity();
		}


		if (isset($post['cityId']) && !empty($post['cityId'])) {
			$userCity = City::model()->findByPk($post['cityId']);
		} else {
			$userCity = City::model()->findByPk($userModel->city_id);
		}

		if (!$userCity) {
			throw new CHttpException(404);
		}

		$rate = SpecialistRateCity::model()->findByAttributes(array('city_id' => $userCity->id, 'service_id' => $serviceId));

		//Если не найден по городу услуге то ищем только по услуге
		if (!$rate) {
			$rate = SpecialistRateCity::model()->findByAttributes(array('city_id' => null, 'service_id' => $serviceId));
		}

		//Если не найден по услуге то ищем по городу
		if (!$rate) {
			$rate = SpecialistRateCity::model()->findByAttributes(array('city_id' => $userCity->id, 'service_id' => null));
		}

		//Если не найден по услуге то ищем дефолтный
		if (!$rate) {
			$rate = SpecialistRateCity::model()->findByAttributes(array('city_id' => null, 'service_id' => null));
		}

		$html = $this->renderPartial('//member/specialist/_pay-form', array(
			'user'        => $userModel,
			'service'     => $serviceModel,
			'serviceList' => $serviceList,
			'city'        => $userCity,
			'rate'        => $rate
		), true);

		die (json_encode(array('success' => true, 'html' => $html)));
	}

	public function actionGetRate()
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(404);
		}


		$post = Yii::app()->request->getPost('item');

		$serviceModel = Service::model()->findByPk((int)$post['serviceId']);

		if (!$serviceModel) {
			throw new CHttpException(404);
		}

		$userCity = City::model()->findByPk($post['cityId']);
		if (!$userCity) {
			throw new CHttpException(404);
		}

		$rate = SpecialistRateCity::model()->findByAttributes(array('city_id'=>$userCity->id, 'service_id'=>(int)$post['serviceId']));

		//Если не найден по городу услуге то ищем только по услуге
		if(!$rate) {
			$rate = SpecialistRateCity::model()->findByAttributes(array('city_id'=>NULL, 'service_id'=>(int)$post['serviceId']));
		}

		//Если не найден по услуге то ищем по городу
		if(!$rate) {
			$rate = SpecialistRateCity::model()->findByAttributes(array('city_id'=>$userCity->id, 'service_id'=>NULL));
		}

		//Если не найден по услуге то ищем дефолтный
		if(!$rate) {
			$rate = SpecialistRateCity::model()->findByAttributes(array('city_id'=>NULL, 'service_id'=>NULL));
		}

		$html = $this->renderPartial('//member/specialist/_rate', array(
			'service' => $serviceModel,
			'city' => $userCity,
			'rate' => $rate
		), true);

		die (json_encode(array('success' => true, 'html' => $html)));

	}
}
