<?php

/**
 * Change the following URL based on your server configuration
 * Make sure the URL ends with a slash so that we can use relative URLs in test cases
 */
define('TEST_BASE_URL', 'http://testmyhome.ru/');

/**
 * The base class for functional test cases.
 * In this class, we set the base URL for the test application.
 * We also provide some common methods to be used by concrete test classes.
 */
class WebTestCase extends CWebTestCase
{
	/**
	 * Sets up before each test method runs.
	 * This mainly sets the base URL for the test application.
	 */

	public $data_path = '/home/gmv/tmp/normal/';

	protected function setUp()
	{
		parent::setUp();
		$this->setBrowserUrl(TEST_BASE_URL);
		$this->setBrowser('*firefox');
	}

	public function startAction($url, $user = array(), $speed = 200)
	{
		$this->setSpeed($speed);
		$this->open($url);
		if (array_key_exists('login', $user) && array_key_exists('password', $user)) {
			$this->authorize($user['login'], $user['password']);
		}
	}

	public function authorize($login, $password)
	{
		if ($this->isElementPresent($this->getElement('authorize'))) {
			$this->click($this->getElement('authorize'));
			$this->waitForElementPresent($this->getElement('popup_login'));
			$this->type($this->getElement('popup_login_name'), $login);
			$this->type($this->getElement('popup_login_pass'), $password);
			$this->click($this->getElement('popup_login_submit'));
			$this->waitForPageToLoad('2000');
			$this->verifyTextPresent('Мой профиль');
		}
	}

	public function logout()
	{
		if ($this->isElementPresent($this->getElement('logout'))) {
			$this->clickAndWait($this->getElement('logout'));
		}
	}

	public function setElements()
	{
		return array(
			'register' => 'link=Зарегистрироваться',
			'authorize' => 'link=Войти',
			'logout' => 'link=Выйти',
			'profile' => 'link=Мой профиль',
			'popup_login' => 'css=div.popup-login',
			'popup_login_name' => 'id=p-login-name',
			'popup_login_pass' => 'id=p-login-pass',
			'popup_login_submit' => 'css=p.p-login-submit > button.btn_grey',
			'portfolio' => 'link=Портфолио',
			'confirm' => 'id=popup-confirm',
			'confirm_accept' => 'link=Да',
		);
	}

	public function getElement($key, $args = false)
	{
		$element = false;
		if (array_key_exists($key, $arr = $this->setElements())) {
			if (is_array($args)) {
				$element = vsprintf($arr[$key], $args);
			} else {
				if ($args != '') {
					$element = sprintf($arr[$key], $args);
				} else {
					$element = $arr[$key];
				}
			}
		}
		return $element;
	}

	public function setContent($str, $args = false)
	{
		$element = false;
		if (is_array($args)) {
			$element = vsprintf($str, $args);
		} else {
			if ($args != '') {
				$element = sprintf($str, $args);
			} else {
				$element = $str;
			}
		}
		return $element;
	}

	public function timestamp()
	{
		$timestamp = explode(' ', microtime());
		return $timestamp[1];
	}
}
