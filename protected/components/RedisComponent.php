<?php
/**
 * Обертка для подключения phpredis
 * для get и set реализован json адаптер
 * (нет в стандартной библиотеке)
 * @see https://github.com/nicolasff/phpredis#classes-and-methods
 */
class RedisComponent extends CApplicationComponent
{
	public $host='localhost';
	public $port=6379;
	public $timeout=1;
	public $password='';

	public $maxReconnect = 500;
	public $reconnectTimeout = 3;

	private $_client;

	public function init()
	{
		parent::init();
//		$this->_client = new Redis();
//		$this->initConnection();
	}

	public function __call($name, $parameters)
	{
		return call_user_func_array(array($this->_client, $name), $parameters);
	}

	public function get($key)
	{
	    return 0;
//		return json_decode($this->_client->get($key), true);
	}

	public function set($key, $value)
	{
        return 0;
//		return $this->_client->set($key, json_encode($value, JSON_NUMERIC_CHECK));
	}

	/**
	 * Используется для долгих скриптов для удержания соединения
	 * @return RedisComponent
	 */
	public function getConnection()
	{
		try {
			$this->_client->ping();
			return $this;
		} catch (RedisException $e) {
			return $this->initConnection();
		}
	}

	private function initConnection()
	{
		$isReconnected = false;
		$reconnectCount = 0;
		while (true) {
			try {
				$this->_client->pconnect($this->host, $this->port, $this->timeout);
				if (!empty($this->password)) {
					$this->_client->auth($this->password);
				}
				return $this;
			} catch (Exception $e) {
				if ($isReconnected) {
					$reconnectCount++;
					if ($reconnectCount >= $this->maxReconnect) {
						die ("\n\nMax reconnect count\n\n");
					}
					echo "\n RECONNECT\n";
					sleep($this->reconnectTimeout);
				}
				$isReconnected = true;
			}
		}


	}
}
