<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 16.04.13
 * Time: 10:11
 * To change this template use File | Settings | File Templates.
 */

class UsersRules extends ParseUrlAbstract
{


	/**
	 * @return mixed
	 * Правило если URL из двух слова
	 */
	public function getRouteTwoWord()
	{
		// Доп. параметры не переданы - запрошена главная страница профиля
		return 'member/profile/index';
	}


	/**
	 * @return mixed
	 * Правило если URL из трех слов
	 */
	public function getRouteThreeWord()
	{
		// Страница услуг
		if ($this->matches[3] == 'services')
			return 'member/profile/services';
		if ($this->matches[3] == 'favorite')
			return 'member/favorite/index';
		if ($this->matches[3] == 'portfolio')
			return 'member/profile/portfolio';
		if ($this->matches[3] == 'contacts')
			return 'member/profile/contacts';
		if ($this->matches[3] == 'activity')
			return 'member/profile/activity';
		if ($this->matches[3] == 'tenders') {
			return 'tenders/profile/index';
		}
		if ($this->matches[3] == 'reviews') {
			return 'member/review/list';
		}
		if($this->matches[3] == 'statistic') {
			return 'member/profile/statistic';
		}

		// Ошибка 404 если ни одно условие не верно
		return false;
	}


	/**
	 * @return mixed
	 * Правило если URL из четырех слова
	 */
	public function getRouteFourWord()
	{
		// Страница добавления проекта в портфолио
		if ($this->matches[3] == 'portfolio' && $this->matches[4] == 'draft')
			return 'member/profile/draft';

		// Страница конкретного списка избранного
		if ($this->matches[3] == 'favorite' && intval($this->matches[4]) > 0)
			return 'member/favorite/index/id/' . intval($this->matches[4]);
		// подходящие тендеры
		if ($this->matches[3] == 'tenders' && $this->matches[4] == 'suited')
			return 'tenders/profile/suited';
		// тендеры "я-исполнитель"
		if ($this->matches[3] == 'tenders' && $this->matches[4] == 'idoer')
			return 'tenders/profile/idoer';
		// тендеры "я-заказчик"
		if ($this->matches[3] == 'tenders' && $this->matches[4] == 'iclient')
			return 'tenders/profile/iclient';

		// Ошибка 404 если ни одно условие не верно
		return false;
	}


	/**
	 * @return bool|void
	 */
	public function getRouteFiveWord()
	{
		// Страница добавления проекта в портфолио
		if ($this->matches[3] == 'portfolio' && $this->matches[4] == 'create')
			return 'idea/portfolio/create/id/' . $this->matches[5];
		if ($this->matches[3] == 'portfolio' && $this->matches[4] == 'service')
			return 'member/profile/portfolio/service/' . $this->matches[5];
		if ($this->matches[3] == 'project')
			return 'member/profile/project/service/' . $this->matches[4] . '/id/' . $this->matches[5];

		// Ошибка 404 если ни одно условие не верно
		return false;
	}


	public function getRouteSixWord()
	{
		// Страница добавления проекта в портфолио
		if ($this->matches[3] == 'portfolio' && $this->matches[4] == 'create' && $this->matches[5] == 'service')
			return 'idea/portfolio/create/service/' . $this->matches[6];

		// Ошибка 404 если ни одно условие не верно
		return false;
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
		if ($route === 'users') {
			// передано два параметра после логина (Прим. /users/login/portfolio/create)
			if (isset($params['login'], $params['action'], $params['subaction']))
				return 'users' . '/' . $params['login'] . '/' . $params['action'] . '/' . $params['subaction'];

			// передан один параметр после логина (Прим. /users/login/services)
			if (isset($params['login'], $params['action']))
				return 'users' . '/' . $params['login'] . '/' . $params['action'];

			// не передано доп. параметров (Прим. /users/login)
			else if (isset($params['login']))
				return 'users' . '/' . $params['login'];
		} elseif ($route == 'member/review/list') {
			if (isset($params['login'])) {
				$login = $params['login'];
				unset ($params['login']);
				$query = Yii::app()->getUrlManager()->createPathInfo($params, '=', $ampersand);
				if (!empty($query))
					return 'users/' . $login . '/reviews?' . $query;
				else
					return 'users/' . $login . '/reviews';
			}
		} elseif ($route == 'member/profile/activity') {
			if (isset($params['login'])) {
				$login = $params['login'];
				unset ($params['login']);
				$query = Yii::app()->getUrlManager()->createPathInfo($params, '=', $ampersand);
				if (!empty($query))
					return 'users/' . $login . '/activity?' . $query;
				else
					return 'users/' . $login . '/activity';
			}
		}

		return false;
	}


	/**
	 * @return mixed
	 * Применить специфические правила обработки URL.
	 */
	function getSpecialRules()
	{
		//Если пользователь не установлен то возвращаем ошибку
		if (!isset($this->matches[2])) {
			throw new CHttpException(404);
		}
		// заплатка на случай запроса /users/login/:username:
		// нужно отловить формирование таких урл
		if ($this->matches[2] == 'login') {
			$this->matches[2] = isset($this->matches[3])
				? $this->matches[3] : '';
			unset($this->matches[3]);
		}

		// поиск пользователя по параметру - логину
		$user = User::model()->findByAttributes(array('login' => CHtml::encode($this->matches[2])));

		if (!$user || $user->status != User::STATUS_ACTIVE)
			throw new CHttpException(404);

		Cache::getInstance()->user = $user;

		return null;
	}
}