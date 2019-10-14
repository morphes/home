<?php
/**
 * @brief Подключение к Vkontakte и получение данных
 * @author Alexey Shvedov <alexsh@yandex.ru>
 */
class Odnoklassniki
{
	const SESSION_KEY = '_odkl_access_token';

	private $accessTimeout = 1800;
	private $_hasAccess = false;
	private $_accessToken = null;
	private $_code = null;
	private $_codeUrl = 'http://api.odnoklassniki.ru/oauth/token.do';
	private $_apiUrl = 'http://api.odnoklassniki.ru/fb.do';

	private $_clientId = '92750080';
	private $_clientSecret = '31FA21E13206BD69415B88E6';
	private $_clientPublic = 'CBACEIIGABABABABA';

	private $_initOptions = array();
	private $_initUrl = 'http://www.odnoklassniki.ru/oauth/authorize';

	private $_returnUrl = '';


	/**
	 * Default options for curl.
	 */
	public static $CURL_OPTS = array(
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 20,
		CURLOPT_USERAGENT      => 'odkl-php-1.0',
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_SSL_VERIFYHOST => 0,
	);

	public function __construct($returnUri = '/oauth/odnoklassniki')
	{
		//Yii::app()->getUser()->setState(self::SESSION_KEY, array());
		$this->_initOptions = array(
			'client_id' => $this->_clientId,
			'scope' => '',
			'response_type' => 'code',
			'redirect_uri' => Yii::app()->getBaseUrl(true).$returnUri,
		);

		$this->_returnUrl = $returnUri;

		$accessToken = Yii::app()->getUser()->getState(self::SESSION_KEY);
		if ( !empty($accessToken) && $accessToken['expires_at'] > time() ) {
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
		if ( !$this->checkAccess() ) {
			$code = Yii::app()->getRequest()->getParam('code');
			if (is_null($code))
				Yii::app()->getController()->redirect($this->getInitUrl());
			$this->setCode($code);
			$access = $this->accessRequest();
			if (isset($access['error'])) {
				throw new CHttpException(500);
			}
			if (empty($access))
				throw new CHttpException(500);
			$this->setAccessToken($access);
		}
	}

	public function getInitUrl()
	{
		return $this->_initUrl . '?' . http_build_query($this->_initOptions);
	}

	public function checkAccess()
	{
		return $this->_hasAccess;
	}

	public function setCode($code)
	{
		$this->_code = $code;
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

		$redirectUri = Yii::app()->getBaseUrl(true).$this->_returnUrl;
		$postOptions = array(
			'code' => $this->_code,
			'redirect_uri' => $redirectUri,
			'grant_type' => 'authorization_code',
			'client_id' => $this->_clientId,
			'client_secret' => $this->_clientSecret,
		);

		$options[CURLOPT_URL] = $this->_codeUrl;
		$options[CURLOPT_POST] = 1;
		$options[CURLOPT_POSTFIELDS] = http_build_query($postOptions);
		$options[CURLOPT_RETURNTRANSFER] = 1;

		curl_setopt_array($curl, $options);
		$result = curl_exec($curl);

		curl_close($curl);
		$result = CJSON::decode($result);
		return $result;
	}

	public function setAccessToken($response)
	{
		$this->_accessToken = $response['access_token'];
		// set to session
		$sessionData = array(
			'access_token' => $response['access_token'],
			'expires_at' => $this->accessTimeout + time(),
		);

		Yii::app()->getUser()->setState(self::SESSION_KEY, $sessionData);

		// set access to current object
		$this->_hasAccess = true;
	}

	/**
	 * Get user info from odkl
	 */
	public function getUserInfo()
	{
		$options = array(
			'access_token' => $this->_accessToken,
			'client_id' => $this->_clientId,
			'application_key' => $this->_clientPublic,
			'format' => 'JSON',
			'method' => 'users.getCurrentUser',
			'sig' => md5('application_key=' . $this->_clientPublic .'client_id='.$this->_clientId. 'format=JSONmethod=users.getCurrentUser' . md5( $this->_accessToken . $this->_clientSecret)),
		);
		$url = $this->_apiUrl . '?' . http_build_query( $options );
		$curl = curl_init($url);

		$curlOpt = array(
			CURLOPT_RETURNTRANSFER => 1,
		);
		curl_setopt_array($curl, $curlOpt);

		$result = curl_exec($curl);
		curl_close($curl);
		$user = json_decode($result, true);
		return $user;
	}
}