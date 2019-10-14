<?php

/**
 * @brief Подключение к Twitter и получение данных
 * @author Alexey Shvedov <alexsh@yandex.ru>
 */
class Twitter
{
	const SESSION_INFIX = '_tw_access_token';
	
	// keys for twitter application
	private $_consumer = 'jJkGSNLtb8dNX17F54Hjw';
	private $_consumer_secret = 'zhMAkOLH3Jq8VPy3WnOZ8hSBL6vIS0EowDuvhUNmw8';
	private $_oauth_callback = '';
	
	//private $_accessToken = null;
	
	private $_connection = null;

	private $_hasAccess = false;
	
	public function __construct($returnPath = '/oauth/twitter')
	{
		$this->initConnection($returnPath);
	}
	
	/**
	 * Init connection to server
	 * @return boolean
	 */
	protected function initConnection($returnPath = '/oauth/twitter')
	{
		$prefix = Yii::app()->user->getStateKeyPrefix();
		$this->_oauth_callback = Yii::app()->getBaseUrl(true) . $returnPath;
		
		$session = Yii::app()->session;
		$sessionData = $session->toArray();
		
		$accessToken = $session->itemAt($prefix.self::SESSION_INFIX);
		
		// correct access
		if (!empty($accessToken['access_token']['oauth_token']) && !empty($accessToken['access_token']['oauth_token_secret']) ) {
			$this->_connection = new TwitterOAuth($this->_consumer, $this->_consumer_secret, $accessToken['access_token']['oauth_token'], $accessToken['access_token']['oauth_token_secret']);
			$this->_hasAccess = true;
			return true;
		}
		return false;
	}

	/**
	 * Test access to server
	 * @return boolean
	 */
	public function checkAccess()
	{
		return $this->_hasAccess;
	}
	
	/**
	 * Init connection, set oauth_token to session and return url for request
	 * access_token
	 * @return string url
	 */
	public function getRequestTokenUrl()
	{
		$prefix = Yii::app()->user->getStateKeyPrefix();
		
		$connection = new TwitterOAuth($this->_consumer, $this->_consumer_secret);
		$requestToken = $connection->getRequestToken($this->_oauth_callback);
		
		$token = $requestToken['oauth_token'];
		
		Yii::app()->session->add($prefix.self::SESSION_INFIX, $requestToken);
		
		$url = $connection->getAuthorizeURL($token);
		return $url;
	}
	
	/**
	 * Test session content(redirect for invalid data),
	 * init connection and receive access_token(used oauth_verifier from input data)
	 * @param array $data ($_GET) 
	 */
	public function setAccessToken($data)
	{
		$prefix = Yii::app()->user->getStateKeyPrefix();
		$session = Yii::app()->session;
		$sessionItem = $session->itemAt($prefix.self::SESSION_INFIX);
		
		if ( empty($sessionItem['oauth_token']) || empty ($sessionItem['oauth_token_secret']) ) {
			Yii::app ()->controller->redirect($this->getRequestTokenUrl());
		}

		$connection = new TwitterOAuth($this->_consumer, $this->_consumer_secret, $sessionItem['oauth_token'], $sessionItem['oauth_token_secret']);
		
		if (!isset ($data['oauth_verifier'])) {
			Yii::app ()->controller->redirect($this->getRequestTokenUrl());
		}
		$accessToken = $connection->getAccessToken($data['oauth_verifier']);
		
		$sessionItem['access_token'] = $accessToken;
		Yii::app()->session->add($prefix.self::SESSION_INFIX, $sessionItem);
		
		$this->initConnection();
		// set access to current object
		$this->_hasAccess = true;
	}
	
	/**
	 * Exec methods twitter api
	 * @param type $method
	 * @return array()
	 */
	public function execMethod($method)
	{
		return $this->_connection->get($method);
	}

}