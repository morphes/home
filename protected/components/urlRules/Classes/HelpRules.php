<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 15.04.13
 * Time: 16:55
 * To change this template use File | Settings | File Templates.
 */

class HelpRules extends ParseUrlAbstract
{

	protected $staticRouteArray = array(
		'help/users'       => 'help/help/index/base/1',
		'help/specialists' => 'help/help/index/base/2',
		'help/stores' => 'help/help/index/base/3',
	);


	/**
	 * @return mixed
	 * Правило если URL из одного слова
	 */
	public function getRouteOneWord()
	{
		if ($this->pathInfo == 'help') {
			$role = Yii::app()->getUser()->getRole();
			/** guest */
			if (is_null($role))
				Yii::app()->getRequest()->redirect('/help/users', true, 301);

			if (in_array($role, array(User::ROLE_STORES_ADMIN, User::ROLE_STORES_MODERATOR)))
				Yii::app()->getRequest()->redirect('/help/stores', true, 302);

			if (in_array($role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR)))
				Yii::app()->getRequest()->redirect('/help/specialists', true, 302);
			else
				Yii::app()->getRequest()->redirect('/help/users', true, 302);
		}
	}


	/**
	 * @return mixed
	 * Правило если URL из трех слов
	 */
	public function getRouteThreeWord()
	{
		// Страница статьи помощи
		if (preg_match('%^help/([\w]+)/([\d]+)%', $this->pathInfo, $matches)) {

			Yii::import('help.models.Help');
			switch ($matches[1]) {
				case 'users':
				{
					Cache::getInstance()->baseId = Help::BASE_USER;

					return 'help/help/article/article_id/' . $matches[2];
				}
					break;
				case 'specialists':
				{
					Cache::getInstance()->baseId = Help::BASE_SPECIALIST;

					return 'help/help/article/article_id/' . $matches[2];
				}
					break;
				case 'stores':
				{
					Cache::getInstance()->baseId = Help::BASE_STORE;

					return 'help/help/article/article_id/' . $matches[2];
				}
					break;
				default:
					throw new CHttpException(404);
			}
		}
		if (preg_match('%^help/(specialists|users|stores)/search%', $this->pathInfo, $matches)) {
			Yii::import('help.models.Help');
			switch ($matches[1]) {
				case 'users':
				{
					Cache::getInstance()->baseId = Help::BASE_USER;
				}
					break;
				case 'specialists':
				{
					Cache::getInstance()->baseId = Help::BASE_SPECIALIST;
				}
					break;
				case 'stores':
				{
					Cache::getInstance()->baseId = Help::BASE_STORE;
				}
					break;
				default:
					throw new CHttpException(404);
			}

			return 'help/search/index';
		}

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
		// Ссылки на фильтр спецов по услугам
		if ($route == 'help/help/index') {
			Yii::import('help.models.Help');
			/**
			 * В случае указания ID, генерируется полный адрес
			 * иначе - /help и редирект по роли пользователя
			 */
			if (isset($params['baseId'])) {
				$baseId = $params['baseId'];

				return 'help/' . Help::$baseUrlName[$baseId];
			}

			return 'help';
		} elseif ($route == 'help/help/article') {
			Yii::import('help.models.Help');
			$baseId = Help::BASE_USER;
			if (isset($params['baseId'])) {
				$baseId = $params['baseId'];
				unset ($params['baseId']);
			}
			if (!isset($params['article_id']))
				throw new CException(500);

			$url = 'help/' . Help::$baseUrlName[$baseId] . '/' . $params['article_id'];
			if (isset($params['anchor']))
				$url .= '#' . $params['anchor'];

			return $url;
		} elseif ($route == 'help/search/index') { // Поиск
			Yii::import('help.models.Help');
			$baseId = Help::BASE_USER;
			if (isset($params['baseId'])) {
				$baseId = $params['baseId'];
				unset ($params['baseId']);
			}

			return 'help/' . Help::$baseUrlName[$baseId] . '/search';
		}

		return false;
	}
}