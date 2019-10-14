<?php
/**
 * Created by JetBrains PhpStorm.
 * User: serg
 * Date: 2/12/13
 * Time: 1:18 PM
 *
 * Класс ввелся для того чтобы прикрепить Поведение работы с поддоменами.
 * Все наши кастомные urlRule'ы наследуются от этого класса.
 */
abstract class EBaseUrlRule extends CBaseUrlRule
{
	public function __construct()
	{
		$this->attachBehavior('subdomains', new SubdomainBehavior());
	}
}
