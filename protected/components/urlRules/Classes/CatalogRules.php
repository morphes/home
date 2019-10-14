<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Vitaliy Sekretenko
 * Date: 08.04.13
 * Time: 15:46
 * To change this template use File | Settings | File Templates.
 */

class CatalogRules extends ParseUrlAbstract
{

	/**
	 * @var mixed
	 * Текущий домен
	 * например myhome.ru
	 */
	private $domain;

	/**
	 * @var
	 * id города из Cookie
	 */
	private $cityId;


	/**
	 * @return bool|mixed|string
	 * Применяем специальные правила, характерные
	 * только для каталога товаров.
	 * В данном случае проверяем наличие поддомена
	 * и выставляем исключения для админки
	 */
	public function getSpecialRules()
	{
		$this->domain = Config::getCookieDomain();


		// Проверка города в cookie
		if (!Cache::getInstance()->mallBuild instanceof MallBuild) {

			/*
			 * Если мы не находимся на конкретной странице ТЦ, то
			 * только в этом случае пытаемся уставновить город из куки.
			 */
			$this->cityId = isset($_COOKIE[Geoip::COOKIE_GEO_SELECTED])
				? $_COOKIE[Geoip::COOKIE_GEO_SELECTED]
				: null;
		} else {
			$this->cityId = null;
		}


		// Исключение для админки
		if (isset($this->matches[2]) && $this->matches[2] == 'admin') {
			return false;
		}

		return null;
	}


	/**
	 * @return string
	 * Метод возвращает route при условии
	 * что в pathArray только одно значение
	 * В данном случае возвращаем путь до
	 * action который обрабатывает главную страницу
	 * каталога товаров
	 *
	 */
	public function getRouteOneWord()
	{
		if ($this->cityId === null) {
			// главная страница каталога товаров
			return 'catalog/category/list/id/0';
		}

		$cityName = $this->getCityName($this->cityId);

		if (!empty($cityName) && !$this->isAjax) {
			$tail = '';
			if (!empty($_REQUEST))
				$tail = '?' . http_build_query($_REQUEST);
			$this->request->redirect('/catalog/' . $cityName . $tail);
		} else {
			return 'catalog/category/list/id/0';
		}
	}


	/**
	 * @return bool|string
	 * Метод возвращает route при условии
	 * что в pathArray два значения
	 * В данном случае строит маршрут до
	 * action категории товаров
	 *
	 */
	public function getRouteTwoWord()
	{
		/* Обработка списка магазинов
		 */
		if ($this->matches[2] == 'stores') {

			// Проверяем город, и редиректим на соответсвующий Url
			$cityName = $this->getCityName($this->cityId);

			if (!empty($cityName) && !$this->isAjax) {
				$tail = '';
				if (!empty($_REQUEST)) {
					$tail = '?' . http_build_query($_REQUEST);
				}
				$this->request->redirect('/catalog/stores/' . $cityName . $tail);
			}
		}


		$categoryId = $this->getCategoryId($this->matches[2]);
		if ($categoryId) {
			// Проверка города в cookie
			if (!Cache::getInstance()->mallBuild instanceof MallBuild) {

				/*
				 * Если мы не находимся на конкретной странице ТЦ, то
				 * только в этом случае пытаемся уставновить город из куки.
				 */
				$this->cityId = isset($_COOKIE[Geoip::COOKIE_GEO_SELECTED])
					? $_COOKIE[Geoip::COOKIE_GEO_SELECTED]
					: null;
			} else {
				$this->cityId = null;
			}


			if ($this->cityId === null) {
				// главная страница категории товаров
				return 'catalog/category/list/id/' . $categoryId;
			}

			$cityName = $this->getCityName($this->cityId);
			if (!empty($cityName) && !$this->isAjax) {
				$tail = '';
				if (!empty($_REQUEST))
					$tail = '?' . http_build_query($_REQUEST);
				$this->request->redirect('/catalog/' . $cityName . '/' . $this->matches[2] . $tail);
			} else {
				return 'catalog/category/list/id/' . $categoryId;
			}
		}

		if ($this->matches[2] == 'stores') {

			if (isset($_REQUEST['map']) && $_REQUEST['map'] == 'show'){
				return 'catalog/store/listmap';
			} else {
				return 'catalog/store/list';
			}

		}
		
		// /catalog/novosibirsk
		$city = City::model()->findByAttributes(array('eng_name' => CHtml::encode($this->matches[2])));
		if ($city !== null) {
			Cache::getInstance()->city = $city;
			if (!$this->isAjax) {
				$this->request->cookies[Geoip::COOKIE_GEO_SELECTED] = new CHttpCookie(
					Geoip::COOKIE_GEO_SELECTED, $city->id,
					array('expire' => time() + 2592000, 'domain' => $this->domain)
				);
			}

			// Открываем список товаров из всех категорий
			return 'catalog/category/list/id/0';
		}

		return false;
	}


	/**
	 * @return bool|string
	 * Метод возвращает route при условии
	 * что в pathArray три значения
	 */
	public function getRouteThreeWord()
	{
		/* -------------------------------------------------------------
		 *  Списки магазинов:
		 * /catalog/stores/novosibirsk
		 * -------------------------------------------------------------
		 */
		if ($this->matches[2] == 'stores') {

			$city = City::model()->findByAttributes(array(
				'eng_name' => CHtml::encode($this->matches[3])
			));
			if ($city !== null) {
				Cache::getInstance()->city = $city;
				if (!$this->isAjax) {
					$this->request->cookies[Geoip::COOKIE_GEO_SELECTED] = new CHttpCookie(
						Geoip::COOKIE_GEO_SELECTED, $city->id,
						array('expire' => time() + 2592000, 'domain' => $this->domain)
					);
				}

				if (isset($_REQUEST['map']) && $_REQUEST['map'] == 'show') {
					return 'catalog/store/listmap';
				} else {
					return 'catalog/store/list';
				}
			}
		}


		// обработка url производителей
		if($this->matches[2] == 'vendor' && isset($this->matches[3]) && preg_match("/^\d+$/", $this->matches[3]) == 1) {

			// главная страница производителя
			if(count($this->matches) == 4)
				return 'catalog/vendor/index/id/' . $this->matches[3];


		}


		/* -------------------------------------------------------------
		 *  Обработка url'ов категорий товаров с производителем:
		 *  /catalog/sofas/8-marta
		 * -------------------------------------------------------------
		 */

		$categoryId = $this->getCategoryId($this->matches[2]);
		if ($categoryId && preg_match('/^[\w0-9-]+$/', $this->matches[3])) {

			Yii::import('application.modules.catalog.models.Vendor');

			$vendor = Vendor::model()->findByAttributes(array(
				'name_translit' => $this->matches[3]
			));

			if ($vendor) {
				return 'catalog/category/list/id/'. $categoryId . '/vendors[]/' . $vendor->id;
			}
		}



		if (preg_match("/^\d+$/", $this->matches[3]) == 1) {
			/* Если в третьем сегменту URL лежит число — карточка товара
			 * Вид: /catalog/sofas/34234
			 */
			return 'catalog/product/index/id/' . $this->matches[3];

		} elseif (
			preg_match("/^[\w\d-]+$/", $this->matches[2]) == 1
			&&
			preg_match("/^[\w\.-]+$/", $this->matches[3]) == 1
		) {

			if (($route = $this->_cityCategory()) !== null) {
				return $route;

			} elseif (($route = $this->_categoryCountry()) !== null) {
				return $route;

			} elseif (($route = $this->_categoryVendor()) !== null) {
				return $route;

			}

		}

		return false;
	}


	/**
	 * @return string
	 * Метод возвращает route
	 * при условии в pathArray четыре значения
	 * возвращает путь до action который обрабатывает
	 * запросы на внутренние карточки товаров
	 */
	public function getRouteFourWord()
	{

		if ($categoryId = $this->getCategoryId($this->matches[2])) {
			// запросы для внутренних страниц карточки товара
			return 'catalog/product/' . $this->matches[4] . '/id/' . $this->matches[3];
		}

		if ($this->matches[2] == 'vendor') {
			// внутренние страницы карточки производителя
			return 'catalog/vendor/' . $this->matches[4] . '/id/' . $this->matches[3];
		}


		return false;
	}


	/**
	 * @param $manager
	 * @param $route
	 * @param $params
	 * @param $ampersand
	 *
	 * @return bool|string
	 * Построение адреса
	 */
	static public function createUrl($manager, $route, $params, $ampersand)
	{
		
		// страница категории товаров
		if ($route === 'catalog/category/list') {
			
			if (isset($params['eng_name'])) { // существует название категории

				$engName = $params['eng_name'];
				unset($params['eng_name']);

			} elseif (isset($params['id'])) { // существует id

				if ($params['id'] == 0) {
					// Если идентификатор нулево,
					unset($params['id']);
					return 'catalog?' . Yii::app()->getUrlManager()->createPathInfo($params, '=', $ampersand);
				}

				// поиск запрошенной категории в базе
				$engName = Category::getCategoryName( intval($params['id']) );

				// Если категории нет, возращает сразу сылку "#"
				if ( $engName === null ) {
					return '#';
				}
				// удаление id категории из параметров (вместо него будет имя категории)
				unset($params['id']);

			} else {
				return false;
			}

			// деление по городам
			if (isset($params['city_name'])) {
				$cityName = $params['city_name'];
				unset($params['city_name']);
			}

			// Страна производителя
			if (isset($params['vendor_country'])) {
				$vendorCountry = $params['vendor_country'];
				unset($params['vendor_country']);
			}

			// подготовка строки get параметров
			$query = Yii::app()->getUrlManager()->createPathInfo($params, '=', $ampersand);
			// возврат url со строкой get параметров (если они есть)
			if (isset($cityName)) {

				if ($query)
					return 'catalog/' . urlencode($cityName) . '/' . urlencode($engName) . '?' . $query;
				else
					return 'catalog/' . urlencode($cityName) . '/' . urlencode($engName);

			} elseif (isset($vendorCountry) && !empty($vendorCountry)) {

				$country = Country::model()->findByPk($vendorCountry);
				if ($vendorCountry) {
					$vendorCountryName = mb_strtolower($country->eng_name);
					if ($query) {
						return 'catalog/' . urlencode($engName) . '/' . urlencode($vendorCountryName) . '?' . $query;
					} else {
						return 'catalog/' . urlencode($engName) . '/' . urlencode($vendorCountryName);
					}
				}

			} else {
				if ($query)
					return 'catalog/' . urlencode($engName) . '?' . $query;
				else
					return 'catalog/' . urlencode($engName);
			}
		}
		// страница товара
		if ($route === 'product' && isset($params['id'])) {

			// поиск названия категории товара для формирования url к карточке товара
			if ( isset($params['category_id'])) {
				$category = Category::getCategoryName( intval($params['category_id']) );
				unset($params['category_id']);
			} else {
				$category = Yii::app()->db->createCommand()->select('c.eng_name')->from('cat_product p')
					->leftJoin('cat_category c', 'c.id=p.category_id')
					->where('p.id=:id', array(':id' => CHtml::encode($params['id'])))->queryScalar();
			}

			if (!$category)
				return '#';

			$category = urlencode($category);

			// карточка товара (в action указана страница карточки - index, description, stores, feedback...)
			if (isset($params['action'])) {

				$delimiter = '';
				$postfix = '';

				// если указан id магазина, то все url карточки товара должны содержать
				// его в строке get параметров для отображения контента магазина
				if (isset($params['store_id']) && $params['store_id']) {
					$postfix = 'store_id=' . $params['store_id'];
					$delimiter = '?';
				}

				// главная страница товара
				if ($params['action'] === 'index' && isset($params['cid']))
					return 'catalog/' . $category . '/' . $params['id'] . '/' . $params['action'] . '?cid=' . $params['cid'] . '&' . $postfix;
				elseif ($params['action'] === 'index')
					return 'catalog/' . $category . '/' . $params['id'] . $delimiter . $postfix;
				// страница "где купить" товара в определенном городе (cid)
				elseif ($params['action'] === 'index' && isset($params['cid']))
					return 'catalog/' . $category . '/' . $params['id'] . '/' . $params['action'] . '?cid=' . $params['cid'] . '&' . $postfix;
				elseif ($params['action'] === 'storesInCity' && isset($params['cid']))
					return 'catalog/' . $category . '/' . $params['id'] . '/' . $params['action'] . '?cid=' . $params['cid'] . '&' . $postfix; // любая другая страница карточки товара
				else
					return 'catalog/' . $category . '/' . $params['id'] . '/' . $params['action'] . $delimiter . $postfix;
			}
		}
		if ($route === 'catalog/vendor' && isset($params['id'])) {

			// карточка товара (в action указана страница карточки - index, description, stores, feedback...)
			if (isset($params['action'])) {

				// сохранение основных параметров запроса
				$id = $params['id'];
				unset($params['id']);
				$action = $params['action'];
				unset($params['action']);

				// подготовка строки get параметров
				$query = Yii::app()->getUrlManager()->createPathInfo($params, '=', $ampersand);

				// главная страница производителя
				if ($action === 'index') {
					return 'catalog/vendor/' . $id;
				} elseif ($query) {

					// внутренние страницы производителя со строкой get параметров (если есть)
					return 'catalog/vendor/' . $id . '/' . $action . '?' . $query;
				} else {

					return 'catalog/vendor/' . $id . '/' . $action;
				}
			}
		}

		// Генерация ссылок на страницы списка магазинов
		if ($route === 'catalog/stores') {

			if (isset($params['city']) && $params['city'] != '') {
				$cityName = '/' . $params['city'];
			} else {
				$cityName = '';
			}
			unset($params['city']);


			// подготовка строки get параметров
			$query = Yii::app()->getUrlManager()->createPathInfo($params, '=', $ampersand);

			if ($query) {
				$query = '?' . $query;
			}


			if (isset($params['map']) && $params['map'] == 'show') {
				return 'catalog/stores' . $cityName . $query;
			} else {
				return 'catalog/stores' . $cityName . $query;
			}
		}

		return false;
	}


	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	private function getCategoryId($name)
	{
		// поиск запрошенной категории в базе
		return Yii::app()->db->createCommand()->select('id')->from('cat_category')
			->where('eng_name=:name', array(':name' => CHtml::encode($name)))->queryScalar();
	}


	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	private function getCityName($id)
	{
		// поиск запрошенной категории в базе
		return Yii::app()->db->createCommand()->select('eng_name')->from('city')
			->where('id=:id', array(':id' => $id))->queryScalar();
	}


	/**
	 * Проверяет URL на соответсвие такому виду: catalog/novosibirsk/sofas
	 * Вторым сегментов обязательно идет название города, третим название
	 * категории.
	 * При удачном совпадении возвращает строку для urlManagera вида
	 * catalog/category/list/id/<category_id>
	 *
	 * @return null|string Если совпадений нет возвращает null, если есть
	 * — строку для urlManager'а
	 */
	private function _cityCategory()
	{
		// проверяем второй сегмент URL в городах
		$city = City::model()->findByAttributes(array(
			'eng_name' => CHtml::encode($this->matches[2])
		));
		if ($city === null) {
			return null;

		} else {

			Cache::getInstance()->city = $city;

			if (!$this->isAjax) {
				$this->request->cookies[Geoip::COOKIE_GEO_SELECTED] = new CHttpCookie(
					Geoip::COOKIE_GEO_SELECTED,
					$city->id,
					array(
						'expire' => time() + 2592000,
						'domain' => $this->domain
					)
				);
			}
		}

		/*
		 * Если город нашелся и не вышли из метода, то
		 * проверяем третий сегмент URL в категорях
		 */
		$categoryId = $this->getCategoryId($this->matches[3]);
		if ($categoryId === null) {
			return null;
		} else {
			// главная страница категории товаров по городам
			return 'catalog/category/list/id/' . $categoryId;
		}

	}


	/**
	 * Проверят URL на соответсвие такому виду: /catalog/sofas/russia
	 * Вторым сегментов идет назваине категории, третьим — название страны.
	 * При удачном совпадении возвращает строку для urlManagera вида
	 * 'catalog/category/list/id/<category_id>/vendor_country/<country_id>
	 *
	 * @return null|string Если совпадений нет возвращает null, если есть
	 * — строку для urlManager'а
	 */
	private function _categoryCountry()
	{

		// Проверяем второй сегмент URL в категорях
		$categoryId = $this->getCategoryId($this->matches[2]);
		if ($categoryId === null) {
			return null;
		}

		Yii::import('application.modules.catalog.models.Vendor');
		Yii::import('application.modules.catalog.models.Product');

		/*
		 * Если нашлась категория товара и не вышли из метода,
		 * проверяем третий сегмент в странах производителя по
		 * найденной категории
		 */
		$countries = Vendor::getCountries($categoryId);
		/* Флаг, хранящий отметку о том,
		 * что было найдено совпадение по городу
		 */
		$foundCountryId = 0;
		foreach ($countries as $c) {
			if (mb_strtolower($c->eng_name) == $this->matches[3]) {
				$foundCountryId = $c->id;
			}
		}

		if ($foundCountryId == 0) {
			return null;
		} else {
			return 'catalog/category/list/id/' . $categoryId . '/vendor_country/' . $foundCountryId;
		}
	}

	private function _categoryVendor()
	{

		return null;
	}
}