<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 15.04.13
 * Time: 14:39
 * To change this template use File | Settings | File Templates.
 */

class ForumRules extends ParseUrlAbstract
{

	protected $staticRouteArray = array(
		'forum'           => '/social/forum',
		'forum/mytopics'  => '/social/forum/mytopics',
		'forum/myanswers' => '/social/forum/myanswers',
		'forum/search'    => '/social/forum/search',
		'forum/create'    => 'social/forum/create'
	);


	/**
	 * @return mixed
	 * Правило если URL из двух слова
	 */
	public function getRouteTwoWord()
	{
		if (preg_match('%^forum/([-_a-zA-Z0-9]{1,})$%', $this->pathInfo, $matches)) {
			Yii::import('application.modules.social.models.ForumSection');
			$model = ForumSection::model()->findByAttributes(array('key' => $this->matches[2]));

			if ($model)
				return '/social/forum/category/key/' . $this->matches[2];
			else
				throw new CHttpException(404);

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
		if (isset($params['key'])) {
			$r = 'forum/' . $params['key'];
			unset($params['key']);

			$url = $r;
			if (!empty($params))
				$url .= '?' . $manager->createPathInfo($params, '=', '&');

			return $url;
		}

		return false;
	}
}