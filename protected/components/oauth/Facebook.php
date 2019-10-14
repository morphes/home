<?php

/**
 * @brief Подключение к Facebook и получение данных
 * @author Alexey Shvedov <alexsh@yandex.ru>
 */
class Facebook
{
	const SESSION_INFIX = '_fb_access_token';
	// request for find code
	private $_codeOptions = array(
		'client_id' => '280648101946177', // application id
		'scope' => 'email',
		'display' => 'popup'
	);
	private $_codeUrl = 'https://www.facebook.com/dialog/oauth/';
	private $_code = null;
	
	// for get access token
	private $_accessOptions = array(
		'client_id' => '280648101946177', // application id
		'client_secret' => '0db3d94714f92a968f5d21df4a0edd49',
	);
	private $_accessUrl = 'https://graph.facebook.com/oauth/access_token';
	private $_accessToken = null;
	
	// url for execute api methods after authorise
	private $_apiUri = 'https://graph.facebook.com/';
	
	private $_hasAccess = false;
	/**
	 * Default options for curl.
	 */
	public static $CURL_OPTS = array(
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 20,
		CURLOPT_USERAGENT      => 'fb-php-1.0',
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_SSL_VERIFYHOST => 0,
	);
	
	public function __construct($returnPath = '/oauth/facebook')
	{
		$this->_codeOptions['redirect_uri'] = Yii::app()->getBaseUrl(true) . $returnPath;
		$this->_accessOptions['redirect_uri'] = Yii::app()->getBaseUrl(true) . $returnPath;
		
		$prefix = Yii::app()->user->getStateKeyPrefix();
		
		$session = Yii::app()->session;
		$sessionData = $session->toArray();
		
		$accessToken = $session->itemAt($prefix.self::SESSION_INFIX);
		
		// correct access
		if (!is_null($accessToken) && $accessToken['expires_at'] > time()) {
			$this->_accessToken = $accessToken['access_token'];
			$this->_hasAccess = true;
			return true;
		}
	}

	/**
	 * Получение доступа к соц. сети для получения данных
	 */
	public function dataAccess()
	{
		if (!$this->checkAccess()) {
			//set to session info
			$code = Yii::app()->request->getParam('code');

			if (is_null($code))
				Yii::app()->getController()->redirect($this->getCodeUrl());

			$this->setCode($code);
			$access = $this->accessRequest();

			if (isset($access['error'])) {
				Yii::app()->getController()->redirect($this->getCodeUrl());
			}
			$this->setAccessToken($access);
		}
	}
	
	public function checkAccess()
	{
		return $this->_hasAccess;
	}
	
	/**
	 * Get url for code request
	 * @return string
	 */
	public function getCodeUrl()
	{
		return $this->_codeUrl . '?' . http_build_query($this->_codeOptions);
	}
	
	/**
	 * Get url for access token request
	 * @return string
	 */
	private function getAccessUrl()
	{
		return $this->_accessUrl . '?' . http_build_query($this->_accessOptions);
	}

	/**
	 * Modified code options
	 * @param array $options
	 * @return array result options
	 */
	public function extendCodeOptions($options)
	{
		if (!is_array($options))
			throw new CException(500, 'Incorrect options type');
		foreach ($options as $key => $value) {
			$this->_codeOptions[$key] = $value;
		}
		return $this->_codeOptions;
	}
	
	public function setCode($code)
	{
		$this->_code = $code;
	}
	
	public function setAccessToken($response)
	{
		if (isset($response['access_token']))
			$this->_accessToken = $response['access_token'];
		else
			throw new CHttpException(500, 'Incorrect access token');

		// set to session
		$sessionData = array(
			'access_token' => $response['access_token'],
			'expires_at' => $response['expires'] + time(),
		);
		
		$prefix = Yii::app()->user->getStateKeyPrefix();
		Yii::app()->session->add($prefix.self::SESSION_INFIX, $sessionData);
		// set access to current object
		$this->_hasAccess = true;
	}

	/**
	 * send request to server for get access token 
	 * @return array() answer from server
	 */
	public function accessRequest()
	{
		if (is_null($this->_code)) {
			throw new CException(500, 'Incorrect code');
		}
		$curl = curl_init();
		
		$options = self::$CURL_OPTS;
		
		$this->_accessOptions['code'] = $this->_code;
		$url = $this->getAccessUrl();
		
		$options[CURLOPT_URL] = $url;
		
		curl_setopt_array($curl, $options);
		$result = curl_exec($curl);
		parse_str($result, $params);
		
		curl_close($curl);
		return $params;
	}
	
	/**
	 * Exec methods fb api
	 * @param type $method
	 * @param type $access_token
	 * @param array $params
	 * @param boolean $redirect for use redirect and have not parse result
	 * @return type 
	 */
	public function execMethod($method, $params = array(), $redirect = false)
	{
		if (is_null($this->_accessToken)) {
			throw new CException(500, 'Incorrect access token');
		}
		$params['access_token'] = $this->_accessToken;
		
		$curl = curl_init();
		$options = self::$CURL_OPTS;
		
		if ($redirect)
			$options[CURLOPT_FOLLOWLOCATION] = 1;
		
		$url = $this->_apiUri . $method . '?' . http_build_query($params);
		
		$options[CURLOPT_URL] = $url;
		
		curl_setopt_array($curl, $options);
		$result = curl_exec($curl);
		curl_close($curl);
		
		if ($redirect)
			return $result;
		
		parse_str($result, $params);
		return $params;
	}

}