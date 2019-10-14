<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Vitaliy Sekretenko
 * Date: 08.04.13
 * Time: 15:46
 * To change this template use File | Settings | File Templates.
 */

class Store2Rules extends ParseUrlAbstract
{

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
		// страница списка товаров магазина
		if ($route === 'catalog2/store/moneyProducts') {

			// удаление id магазина из параметров (вместо него поддомен)
			unset($params['id']);

			unset($params['sub']);

			// подготовка строки get параметров
			$query = Yii::app()->getUrlManager()->createPathInfo($params, '=', $ampersand);

			if ($query) {
				return 'products' . '?' . $query;
			} else {
				return 'products';
			}
		}

		// страница со списком новостей магазина
		if ($route === 'catalog2/store/moneyNews') {

			// удаление id магазина из параметров (вместо него поддомен)
			unset($params['id']);

			unset($params['sub']);

			// подготовка строки get параметров
			$query = Yii::app()->getUrlManager()->createPathInfo($params, '=', $ampersand);

			if ($query) {
				return 'news' . '?' . $query;
			} else {
				return 'news';
			}
		}

		return false;
	}


	/**
	 * Список основных страниц для магазинов с поддоменом.
	 *
	 * @return string
	 */
	public function getRouteOneWord()
	{
		switch ($this->matches[1]) {
			case 'about':
				return 'products/store/moneyIndex';
				break;
			case 'fotos':
				return 'products/store/moneyGallery';
				break;
			case 'products':
				return 'products/store/moneyProducts';
				break;
			case 'feedback':
				return 'products/store/moneyFeedback';
				break;
			case 'news':
				return 'products/store/moneyNews';
				break;
		}

		return false;
	}

	
	/**
	 * Детальная страница новостей для магазина с поддоменом.
	 *
	 * @return bool|string
	 */
	public function getRouteTwoWord()
	{
		Yii::import('application.modules.catalog.models.StoreNews');

		$storeNews = StoreNews::model()->findByPk((int)$this->matches[2]);
		if ($storeNews) {
			return 'products/store/moneyNewsDetail/id/' . $storeNews->id;
		}

		return false;
	}

}