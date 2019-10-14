<?php
/**
 * @brief Подключение к Vkontakte и получение данных
 * @author Alexey Shvedov <alexsh@yandex.ru>
 */
class Vkontakte
{
	const SESSION_INFIX = '_vk_access_token';
	// request for find code
	private $_codeOptions = array(
		'v'             => '5.0',
		'client_id'     => '2478844', // application id
		'scope'         => 'notify',
		'response_type' => 'code',
		'display'       => 'popup',
	);
	private $_codeUrl = 'https://oauth.vk.com/authorize';
	private $_code = null;
	
	// for get access token
	private $_accessOptions = array(
		'client_id'     => '2478844', // application id
		'client_secret' => '9y30QLNebBL14nXms4j6',
	);
	private $_accessUrl = 'https://api.vkontakte.ru/oauth/access_token';
	private $_accessToken = null;
	
	// url for execute api methods after authorise
	private $_apiUri = 'https://api.vkontakte.ru/method/';
	
	private $_hasAccess = false;
	private $_uid = null;
	/**
	 * Default options for curl.
	 */
	public static $CURL_OPTS = array(
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 20,
		CURLOPT_USERAGENT      => 'vk-php-1.0',
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_SSL_VERIFYHOST => 0,
	);
	
	public function __construct($returnPath = '/oauth/vkontakte')
	{
		$this->_codeOptions['redirect_uri'] = $this->_accessOptions['redirect_uri'] = Yii::app()->getBaseUrl(true) . $returnPath;
		
		$prefix = Yii::app()->user->getStateKeyPrefix();
		
		$session = Yii::app()->session;

		$accessToken = $session->itemAt($prefix.self::SESSION_INFIX);
		// correct access
		if (!is_null($accessToken) && $accessToken['expires_at'] > time() ) {
			$this->_accessToken = $accessToken['access_token'];
			$this->_uid = $accessToken['user_id'];
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
			if (is_null($code)) {
				Yii::app()->getController()->redirect($this->getCodeUrl());
			}
			$this->setCode($code);
			$access = $this->accessRequest();

			if (isset($access['error']) && $access['error'] == 'invalid_grant') { // Истекший code
				Yii::app()->getController()->redirect($this->getCodeUrl());
			}

			if (isset($access['error'])) {
				throw new CHttpException(500);
			}

			$this->setAccessToken($access);
		}
	}
	
	public function checkAccess()
	{
		return $this->_hasAccess;
	}
	
	public function getUserId()
	{
		return $this->_uid;
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
	
	public function getCode()
	{
		$curl = curl_init();
		
		$options = self::$CURL_OPTS;
		
		$url = $this->getCodeUrl();
		
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_FOLLOWLOCATION] = 1;
		
		curl_setopt_array($curl, $options);
		$result = curl_exec($curl);

		curl_close($curl);
		$result = CJSON::decode($result);
		return $result;
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
		$this->_accessToken = $response['access_token'];
		// set to session
		$sessionData = array(
			'access_token' => $response['access_token'],
			'user_id' => $response['user_id'],
			'expires_at' => $response['expires_in'] + time(),
		);
		
		$prefix = Yii::app()->user->getStateKeyPrefix();
		Yii::app()->session->add($prefix.self::SESSION_INFIX, $sessionData);
		// set access to current object
		$this->_hasAccess = true;
		$this->_uid = $response['user_id'];
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
		
		curl_close($curl);
		$result = CJSON::decode($result);
		return $result;
	}
	
	/**
	 * Exec methods vk api
	 * @param type $method
	 * @param type $access_token
	 * @param array $params
	 * @return type 
	 */
	public function execMethod($method, $params = array())
	{
		if (is_null($this->_accessToken)) {
			throw new CException(500, 'Incorrect access token');
		}
		$params['access_token'] = $this->_accessToken;
		
		$curl = curl_init();
		$options = self::$CURL_OPTS;
		
		$url = $this->_apiUri . $method . '?' . http_build_query($params);
		
		$options[CURLOPT_URL] = $url;
		
		curl_setopt_array($curl, $options);
		$result = curl_exec($curl);
		
		curl_close($curl);
		return CJSON::decode($result);
	}

}