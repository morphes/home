<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Vitaliy Sekretenko
 * Date: 11.04.13
 * Time: 12:24
 * To change this template use File | Settings | File Templates.
 */

class SpecialistRules extends ParseUrlAbstract
{

	/**
	 * @return mixed
	 * Правило если URL из одного слова
	 */
	public function getRouteOneWord()
	{
		// Проверка города в cookie
		$cityId = isset($_COOKIE[Geoip::COOKIE_GEO_SELECTED])
			? intval($_COOKIE[Geoip::COOKIE_GEO_SELECTED]) : null;
		if ($cityId === null) {
			// главная страница каталога товаров
			return 'member/specialist/list';
		}

		$cityName = $this->getCityName($cityId);
		if (!empty($cityName) && !$this->isAjax) {
			$tail = '';
			if (!empty($_REQUEST))
				$tail = '?' . http_build_query($_REQUEST);
			$this->request->redirect('/specialist/' . $cityName . $tail);
		} else {
			return 'member/specialist/list';
		}
	}


	/**
	 * @return mixed
	 * Правило если URL из двух слова
	 */
	public function getRouteTwoWord()
	{

		Yii::import('application.modules.member.models.Service');
		// Проверка на услугу
		$service = Service::model()->findByAttributes(array(), 'url=:url AND parent_id>0', array('url' => CHtml::encode($this->matches[2])));

		if (is_null($service)) {
			// Проверка на город
			$city = City::model()->findByAttributes(array('eng_name' => CHtml::encode($this->matches[2])));
			if ($city === null)
				throw new CHttpException(404);
			Cache::getInstance()->city = $city;
			if (!$this->isAjax)
				$this->setSpecialistCookie($city->id, $this->request);

			return 'member/specialist/list';
		}
		Cache::getInstance()->serviceUser = $service;
		Yii::app()->getUser()->setState('user:service', $service->id);

		return 'member/specialist/list';
	}


	/**
	 * @return mixed
	 * Правило если URL из трех слов
	 */
	public function getRouteThreeWord()
	{

		// city options
		$city = null;

		//service option
		$service=null;

		Yii::import('application.modules.member.models.Service');

		// если УРЛ услуги состоит из двух слов, значит города в текущем УРЛ нет
		$serviceName = $this->matches[2] . '/' . $this->matches[3];

		// Проверка на услугу
		$service = Service::model()->findByAttributes(array(), 'url=:url AND parent_id>0', array('url' => CHtml::encode($serviceName)));

		if ( !$service ) {
			// Проверка на услугу
			$service = Service::model()->findByAttributes(array(), 'url=:url AND parent_id>0', array('url' => CHtml::encode($this->matches[2])));
			// Проверка на город
			$city = City::model()->findByAttributes(array('eng_name'=>CHtml::encode($this->matches[3])));

			if(is_null($service) or is_null($city))
				return false;

		} else {
			if( is_null($service) )
				return false;
		}

		Cache::getInstance()->serviceUser = $service;

		if ( $city )
			Cache::getInstance()->city = $city;

		Yii::app()->getUser()->setState('user:service', $service->id);

		if (!$this->isAjax && $city )
			$this->setSpecialistCookie($city->id, $this->request);

		return 'member/specialist/list';
	}

	/**
	 * @return mixed
	 * Правило если URL из четырех слов
	 */
	public function getRouteFourWord()
	{
		// city options
		$city = null;

		//service option
		$service=null;

		Yii::import('application.modules.member.models.Service');

		$serviceName = $this->matches[2] . '/' . $this->matches[3];

		// Проверка на услугу
		$service = Service::model()->findByAttributes(array(), 'url=:url AND parent_id>0', array('url' => CHtml::encode($serviceName)));
		$city = City::model()->findByAttributes(array('eng_name'=>CHtml::encode($this->matches[4])));

		if(is_null($service) or is_null($city))
			return false;

		Cache::getInstance()->serviceUser = $service;
		Cache::getInstance()->city = $city;

		Yii::app()->getUser()->setState('user:service', $service->id);

		if ( !$this->isAjax )
			$this->setSpecialistCookie($city->id, $this->request);

		return 'member/specialist/list';
	}


	/**
	 * @param $manager
	 * @param $route
	 * @param $params
	 * @param $ampersand
	 *
	 * @return mixed
	 * Метод строит URL в зависимости от роута
	 */
	static public function createUrl($manager, $route, $params, $ampersand)
	{
		// Ссылки на фильтр спецов по услугам
		if ($route == 'member/specialist/list') {
			if (isset($params['service'])) {
				if (isset($params['city']))
					return 'specialist/' . $params['service'] . '/' . $params['city'];
				else
					return 'specialist/' . $params['service'];
			} else { // for pager
				$service = Cache::getInstance()->serviceUser;
//				if (is_null($service)) {
//					$serviceId = Interior::SERVICE_ID;
//					$service = Service::model()->findByPk($serviceId);
//					Cache::getInstance()->serviceUser = $service;
//				}
				$query = Yii::app()->getUrlManager()->createPathInfo($params, '=', $ampersand);
				$city = Cache::getInstance()->city;
				if (!is_null($city)) {
					if ($service===null)
						return 'specialist/' . $city->eng_name . '?' . $query;
					else
						return 'specialist/' . $service->url . '/' . $city->eng_name . '?' . $query;
				} else {
					if ($service===null)
						return 'specialist?' . $query;
					else
						return 'specialist/' . $service->url . '?' . $query;
				}

			}
		}

		return false;
	}


	/**
	 * @return mixed
	 * Применить специфические правила обработки URL.
	 */
	public function getSpecialRules()
	{
		if (isset($this->redirectUrlRule[$this->pathInfo])) {

			Yii::app()->getRequest()->redirect('/' . $this->redirectUrlRule[$this->pathInfo], true, 301);
		}

		return null;
	}


	private function setSpecialistCookie($cityId, $request)
	{
		$domain = Config::getCookieDomain();
		$request->cookies[Geoip::COOKIE_GEO_SELECTED] = new CHttpCookie(
			Geoip::COOKIE_GEO_SELECTED,
			$cityId,
			array('expire' => 0, 'domain' => $domain)
		);
	}


	private function getCityName($id)
	{
		// поиск запрошенной категории в базе
		return Yii::app()->db->createCommand()->select('eng_name')->from('city')
			->where('id=:id', array(':id' => $id))->queryScalar();
	}


	private $redirectUrlRule = array( // 301 redirect for this urls
		'specialist/list'     => 'specialist',
		'specialist/designer' => 'specialist',
	);
}


