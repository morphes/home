<?php
/**
 * User: alexsh
 * Date: 23.11.12
 * Time: 15:18
 */
class CCookieStorage extends CApplicationComponent
{
	public $connectionID='db';
	public $tableName='cookie_storage';
	public $cookieVar='storage';
	public $duration = 2592000; // 30 days
	/** @var bool Флаг автообновления cookie */
	public $autoRenew = true;

	/**
	 * @var CDbConnection the DB connection instance
	 */
	private $_db;
	/** @var array cookieStorage data */
	private $_data=array();
	/** @var id in cookieStorage */
	private $_cid=null;
	/** @var string Токен,считанный из cookie */
	private $_token='';
	/** @var string обновляемый токен из cookie */
	private $_renewToken='';

	public function init()
	{
		parent::init();
		//if (Yii::app()->getRequest()->getIsAjaxRequest())
		//	return;

		if (!isset($_SERVER['HTTP_COOKIE']))
			return;

		if ( isset($_COOKIE[$this->cookieVar]) ) {
			$cookieData = $_COOKIE[$this->cookieVar];
			$cookieData = explode('.', $cookieData, 2);

			/** Valid cookie format */
			if (count($cookieData)===2) {
				$this->_cid = intval($cookieData[0]);
				$this->_token = $cookieData[1];

				/** при некорректных токенах - обновление COOKIE */
				if (!$this->accessToData()) {
					$this->createStorage();
				}
			} else {
				$this->createStorage();
			}


		} else {
			$this->createStorage();
		}
		register_shutdown_function(array($this,'onEndRequest'));
	}

	/**
	 * Создание новой записи с токенами
	 */
	protected function createStorage()
	{
		$this->_token = md5(uniqid(rand(), true));
		$this->_renewToken = md5(uniqid(rand(), true));

		$data = serialize(array());

		$builder=$this->getDbConnection()->getCommandBuilder();
		$command=$builder->createInsertCommand($this->tableName, array(
			'token'=>$this->_token,
			'expire' => time()+$this->duration,
			'data'=>$data,
		));

		if ($command->execute()) {
			// NOTE: mysql only!
			$this->_cid=$this->getDbConnection()->getLastInsertID();
			if ($this->autoRenew)
				$this->updateCookie();
			return true;
		}

		throw new CHttpException(500);
	}

	/**
	 * Проверка доступа к данным и чтение данных
	 * @return bool
	 */
	protected function accessToData()
	{
		$sql = 'SELECT * FROM '.$this->tableName.' WHERE id='.$this->_cid;
		$data = $this->getDbConnection()->createCommand($sql)->queryRow();
		if ($data) {
			$token = $data['token'];

			if ( $token !== $this->_token )
				return false;

			$cookieData = unserialize($data['data']);

			$this->_data = $cookieData ? $cookieData : array();
			if ($this->autoRenew)
				$this->updateCookie();
			return true;
		}

		return false;
	}

	/**
	 * Обновление cookie
	 */
	protected function updateCookie()
	{
		$cookieVar = $this->_cid .'.'.$this->_token;
		$cookie = new CHttpCookie($this->cookieVar, $cookieVar);
		$params = Yii::app()->session->getCookieParams();
		$cookie->domain = isset($params['domain']) ? $params['domain'] : '';
		$cookie->expire = time()+$this->duration;
		$cookie->httpOnly = true;

		Yii::app()->getRequest()->getCookies()->add($cookie->name, $cookie);
	}

	public function onEndRequest()
	{
		$data = serialize($this->_data);
		$builder=$this->getDbConnection()->getCommandBuilder();
		$criteria = new CDbCriteria( array('condition'=>'id=:id', 'params'=>array(':id'=>$this->_cid) ) );
		$command = $builder->createUpdateCommand($this->tableName, array('expire' => (time()+$this->duration) , 'data'=>$data), $criteria );
		$command->execute();
	}

	/**
	 * @return CDbConnection the DB connection instance
	 * @throws CException if {@link connectionID} does not point to a valid application component.
	 */
	protected function getDbConnection()
	{
		if($this->_db!==null)
			return $this->_db;
		else if(($id=$this->connectionID)!==null)
		{
			if(($this->_db=Yii::app()->getComponent($id)) instanceof CDbConnection)
				return $this->_db;
			else
				throw new CException('Invalid connection type');
		}
		throw new CException('Invalid connection ID');
	}

	public function getCookieId()
	{
		return $this->_cid;
	}

	public function getValue($name, $default=null)
	{
		if ( isset($this->_data[$name]) )
			return $this->_data[$name];
		else
			return $default;
	}

	public function setValue($name, $value)
	{
		$this->_data[$name] = $value;
		return true;
	}

	public function unsetValue($name)
	{
		unset ($this->_data[$name]);
		return true;
	}

}
