<?php

class SubdomainBehavior extends CBehavior
{
	private $subdomain = null;
	private $defaultSubdomains = array('www', 'test', 'vps');

	/**
	 * Возвращает поддомен третьго уровня
	 * @return string
	 */
	public function getSubdomain($host=null)
	{
		if ($host===null)
			$host = $_SERVER['HTTP_HOST'];

		$arr = explode('.', $host);
		if (count($arr) == '3')
			$this->subdomain = $arr[0];

		if ( ! in_array($this->subdomain, $this->defaultSubdomains))
			return $this->subdomain;
		else
			return null;
	}

	/**
	 * Проверяет соответсвует ли текущий домен третьего уровня
	 * списку разрешенных доменов.
	 *
	 * @param array $list_subdomains Массив доменов на разрешение
	 * @return bool Флаг совпадения текущего домена со списком разрешенных.
	 * @throws CHttpException
	 */
	public function validateSubdomain($list_subdomains = array(''))
	{
		// Если пришла строка, то превращаем ее в массив
		if (is_string($list_subdomains))
			$list_subdomains = array($list_subdomains);

		if ( ! is_array($list_subdomains))
			throw new CHttpException(400, 'Параметр $list_subdomains может быть только массивом');


		$arrSubs = array_merge($this->defaultSubdomains, $list_subdomains);

		// Возвращаем флаг о том, принадлежит ли текущий поддомен списку разрешенных.
		return in_array($this->subdomain, $arrSubs);
	}

}
