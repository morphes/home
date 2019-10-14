<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Vitaliy Sekretenko
 * Date: 08.04.13
 * Time: 16:08
 * To change this template use File | Settings | File Templates.
 * Интерфейс для классов по составлению UrlRules catalog,idea,specialis
 */


abstract class ParseUrlAbstract
{

	protected $manager;

	protected $request;

	protected $pathInfo;

	protected $rawPathInfo;

	/**
	 * @var
	 * Массив со словами из URL
	 */
	protected $matches;

	protected $subdomainBehavior;

	/**
	 * @var bool
	 * Состояние запроса. Ajax или нет
	 */
	protected $isAjax;

	/**
	 * @var array
	 * Массив статичных роутов ключ =>значение
	 */
	protected $staticRouteArray = array();


	public function __construct($manager, $request, $pathInfo, $rawPathInfo, $pathArray)
	{
		$this->manager = $manager;
		$this->request = $request;
		$this->pathInfo = $pathInfo;
		$this->rawPathInfo;
		$this->matches = $pathArray;
		$this->subdomainBehavior = new SubdomainBehavior();
		//Проверка Ajax или нет
		$this->isAjax = $request->getIsAjaxRequest();
	}


	/**
	 * @return mixed
	 * Правило если URL из одного слова
	 */
	public function getRouteOneWord()
	{
		return false;
	}


	/**
	 * @return mixed
	 * Правило если URL из двух слова
	 */
	public function getRouteTwoWord()
	{
		return false;
	}


	/**
	 * @return mixed
	 * Правило если URL из трех слов
	 */
	public function getRouteThreeWord()
	{
		return false;
	}


	/**
	 * @return mixed
	 * Правило если URL из четырех слова
	 */
	public function getRouteFourWord()
	{
		return false;
	}


	/**
	 * @return bool
	 * Правило если URL из пяти слов
	 */
	public function getRouteFiveWord()
	{
		return false;
	}


	/**
	 * @return bool
	 * Правило если URL из шести слов
	 */
	public function getRouteSixWord()
	{
		return false;
	}


	/**
	 * @return mixed
	 * Метод проверяет совпадение URL c массивом статичных роутов staticRouteArray и если находит совпадение то
	 *возвращает роут
	 */
	public function getStaticRoute()
	{
		if (isset($this->staticRouteArray[$this->pathInfo])) {
			return $this->staticRouteArray[$this->pathInfo];
		} else {
			return false;
		}
	}


	/**
	 * @param $manager
	 * @param $route
	 * @param $params
	 * @param $ampersand
	 *
	 * @return mixed
	 * Метод реализует построение кастомных URL
	 */
	 public static function createUrl($manager, $route, $params, $ampersand)
	 {
		 return false;
	 }


	/**
	 * @return mixed
	 * Применить специфические правила обработки URL.
	 */
	public function getSpecialRules()
	{
		return null;
	}


	/**
	 * @return bool
	 * Перенаправление на основной домен
	 */
	public function redirectToMainDomain()
	{
		if (!$this->request->getIsAjaxRequest()) {
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: ' . Yii::app()->homeUrl . $this->request->requestUri);
			die();
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 * статичный метод для перенаправления на основной домен
	 */
	public static  function staticRedirectToMainDomain($request)
	{
		if (!$request->getIsAjaxRequest()) {
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: ' . Yii::app()->homeUrl . $request->requestUri);
			die();
		} else {
			return false;
		}
	}

}