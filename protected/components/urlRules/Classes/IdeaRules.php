<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 15.04.13
 * Time: 10:20
 * To change this template use File | Settings | File Templates.
 */

class IdeaRules extends ParseUrlAbstract
{

	/**
	 * @var array Список для 301 редиректов по requestURI
	 */
	private $redirectRequestUriRule = array(
		'/idea/interiorpublic/catalog?filter=1&build_type=224' => 'idea/interiorpublic/frontoffice',
		'/idea/interiorpublic/catalog?filter=1&build_type=225' => 'idea/interiorpublic/tradeexhibition',
		'/idea/interiorpublic/catalog?filter=1&build_type=226' => 'idea/interiorpublic/fec',
		'/idea/interiorpublic/catalog?filter=1&build_type=227' => 'idea/interiorpublic/cinema',
		'/idea/interiorpublic/catalog?filter=1&build_type=228' => 'idea/interiorpublic/resto',
		'/idea/interiorpublic/catalog?filter=1&build_type=229' => 'idea/interiorpublic/beauty',
		'/idea/interiorpublic/catalog?filter=1&build_type=230' => 'idea/interiorpublic/sports',
		'/idea/interiorpublic/catalog?filter=1&build_type=232' => 'idea/interiorpublic/office',
	);
	private $redirectUrlRule = array( // 301 redirect for this urls
		'idea/architecture/catalog'   => 'idea/architecture',
		'idea/interiorpublic/catalog' => 'idea/interiorpublic',
		'idea/interior/catalog'       => 'idea/interior',
		'idea/index/catalog'          => 'idea',
	);

	private $urlRule = array(
		'idea/catalog/tags'                  => 'idea/catalog/tags',
		'idea/catalog/ideacounter'           => 'idea/catalog/ideacounter',
		'idea/catalog/interiorpubliccounter' => 'idea/catalog/interiorpubliccounter',
		'idea/catalog/architecturecounter'   => 'idea/catalog/architecturecounter',
		'idea/catalog/index'                 => 'idea/catalog/index',
		'idea/interior'                      => 'idea/catalog/interior',
		'idea/interiorpublic'                => 'idea/catalog/interiorpublic',
		'idea/interiorpublic/addcoauthor'    => 'idea/interiorpublic/addcoauthor',
		'idea/architecture'                  => 'idea/catalog/architecture',
		'idea/interior/popup'                => 'idea/interior/popup',
		'idea/interiorpublic/popup'          => 'idea/interiorpublic/popup',
		'idea/architecture/popup'            => 'idea/architecture/popup',
	);


	/**
	 * @return mixed
	 * Правило если URL из трех слов
	 */
	public function getRouteThreeWord()
	{
		// Страница идеи
		if (preg_match('%^idea/([\w]+)/([\d]+)(?:/(\d+))?%', $this->pathInfo, $matches)) {

			switch ($matches[1]) {
				case 'interior':
				{
					if (isset($matches[3]))
						return 'idea/interior/view/interior_id/' . $matches[2] . '/image/' . $matches[3];
					else
						return 'idea/interior/view/interior_id/' . $matches[2];
				}
				case 'interiorpublic':
				{
					if (isset($matches[3]))
						return 'idea/interiorpublic/view/id/' . $matches[2] . '/image/' . $matches[3];
					else
						return 'idea/interiorpublic/view/id/' . $matches[2];
				}
				case 'architecture':
				{
					if (isset($matches[3]))
						return 'idea/architecture/view/id/' . $matches[2] . '/image/' . $matches[3];
					else
						return 'idea/architecture/view/id/' . $matches[2];
				}
			}

			throw new CHttpException(404);
		}

		// Страница списка идей
		if (preg_match('%^idea/([\w]+)/([-\w]+)%', $this->pathInfo, $matches)) {

			switch ($matches[1]) {
				case 'interior':
				{
					$ideaType = Config::INTERIOR;
				}
					break;
				case 'interiorpublic':
				{
					$ideaType = Config::INTERIOR_PUBLIC;
				}
					break;
				case 'architecture':
				{
					if (empty($matches[2])) {
						return 'idea/catalog/architecture';
					} else {
						return false;
					}
				}
					break;
				case 'admin':
					return false;
				case 'portfolio':
					return false;
				case 'create':
					return false;
				default:
					throw new CHttpException(404);
			}

			if (in_array($matches[2], array('create', 'update', 'view', 'upload', 'delete', 'deleteimage', 'imagedelete'))) {
				return false;
			}


			$params = explode('-', $matches[2]);
			Yii::import('application.modules.idea.models.IdeaHeap');

			$secondParam = isset($params[1]) ? $params[1] : null;

			if ($ideaType == Config::INTERIOR) {

				if (isset($params[0])) { // Проверка на помещение
					$room = IdeaHeap::model()->findByAttributes(array('idea_type_id' => Config::INTERIOR, 'option_key' => 'room', 'eng_name' => $params[0])); // TODO: добавить исп-е англ названия
					if (is_null($room))
						$secondParam = $params[0];
					else {
						$_GET['room'] = $room->option_value;
					}
				}
				if (!empty($secondParam)) { // Проверка на цветаs
					$color = IdeaHeap::model()->findByAttributes(array('idea_type_id' => Config::INTERIOR, 'option_key' => 'color', 'eng_name' => $secondParam));
					if (!is_null($color)) {
						$_GET['color'] = $color->option_value;
					} else { // Проверка на стили
						if (!empty(Config::$ideaEngStyles[$secondParam])) { // группа стилей
							$styles = IdeaHeap::model()->findAllByAttributes(array('idea_type_id' => Config::INTERIOR, 'option_key' => 'style', 'param' => Config::$ideaEngStyles[$secondParam]));
							$cnt = 0;
							$styleList = '';
							foreach ($styles as $style) {
								if ($cnt > 0) {
									$styleList .= ', ';
								}
								$styleList .= $style->option_value;
								$cnt++;
							}
							$_GET['style'] = $styleList;
						} else {
							$style = IdeaHeap::model()->findByAttributes(array('idea_type_id' => Config::INTERIOR, 'option_key' => 'style', 'eng_name' => $secondParam));
							if (!is_null($style)) {
								$_GET['style'] = $style->option_value;
							}
						}
					}
				}
			} else if ($ideaType == Config::INTERIOR_PUBLIC) {
				if (isset($params[0])) { // Проверка на помещение
					$build = IdeaHeap::model()->findByAttributes(array('idea_type_id' => Config::INTERIOR_PUBLIC, 'option_key' => 'building_type', 'eng_name' => $params[0]));
					if (is_null($build))
						$secondParam = $params[0];
					else {
						$_GET['build_type'] = $build->id;
					}
				}
				if (!empty($secondParam)) { // Проверка на цветаs
					$color = IdeaHeap::model()->findByAttributes(array('idea_type_id' => Config::INTERIOR_PUBLIC, 'option_key' => 'color', 'eng_name' => $secondParam));
					if (!is_null($color)) {
						$_GET['color'] = $color->option_value;
					} else { // Проверка на стили
						$style = IdeaHeap::model()->findByAttributes(array('idea_type_id' => Config::INTERIOR_PUBLIC, 'option_key' => 'style', 'eng_name' => $secondParam));
						if (!is_null($style)) {
							$_GET['style'] = $style->id;
						}
					}
				}
			}

			return 'idea/catalog/' . $matches[1];
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


		$createUrlRule = array(
			'idea/catalog/index'          => 'idea/catalog/index',
			'idea/catalog/architecture'   => 'idea/architecture',
			'idea/catalog/interiorpublic' => 'idea/interiorpublic',
			'idea/catalog/interior'       => 'idea/interior',
		);


		$url = $createUrlRule[$route];

		if (isset($params['humanUrl'])) {
			$url .= '/' . $params['humanUrl'];
			unset($params['humanUrl']);
		}
		if (empty($params)) {
			return $url;
		} else {

			$query = Yii::app()->getUrlManager()->createPathInfo($params, '=', $ampersand);

			return $url . '?' . $query;
		}
	}


	/**
	 * @return mixed
	 * Применить специфические правила обработки URL.
	 */
	function getSpecialRules()
	{
		if (isset($this->redirectRequestUriRule[$this->request->requestUri])) {
			Yii::app()->getRequest()->redirect('/' . $this->redirectRequestUriRule[$this->request->requestUri], true, 301);
		}

		if (isset($this->redirectUrlRule[$this->pathInfo])) {
			Yii::app()->getRequest()->redirect('/' . $this->redirectUrlRule[$this->pathInfo], true, 301);
		}

		if (isset($this->urlRule[$this->pathInfo])) {
			return $this->urlRule[$this->pathInfo];
		}

		return null;
	}
}