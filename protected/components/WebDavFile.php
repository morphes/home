<?php
class WebDavFile implements FileInterface
{
	protected $host = 'localhost';
	protected $port = 80;
	protected $userpwd = '';

	public static $CURL_OPTS = array(
		CURLOPT_CONNECTTIMEOUT => 2,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 2,
		CURLOPT_USERAGENT      => 'web-dav-1.0',
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_SSL_VERIFYHOST => 0,
	);

	private function getUrl($path)
	{
		return 'http://'.$this->host .'/'.trim($path, ' /');
	}

	/**
	 * Положить файл по указанному пути
	 * @return bool
	 */
	public function putFile($src, $dest)
	{
		if (!is_file($src))
			throw new CException('Invalid file path');
		$option = self::$CURL_OPTS;
		$option[CURLOPT_UPLOAD] = true; // установка загрузки
		$option[CURLOPT_INFILESIZE] = filesize($src);
		$option[CURLOPT_PUT] = true;
		$file = fopen($src, 'r');
		$option[CURLOPT_INFILE] = $file;
		$option[CURLOPT_URL] = $this->getUrl($dest);
		$option[CURLOPT_PORT] = $this->port;
		if (!empty($this->userpwd)) {
			$option[CURLOPT_USERPWD] = $this->userpwd;
		}

		$curl = curl_init();
		curl_setopt_array($curl, $option);
		curl_exec($curl);

		$httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);
		if (in_array($httpStatus, array(200, 201, 204,207)))
			return true;
		return false;
	}

	/**
	 * Получает файл по указанному пути, сохраняет во временное
	 * хранилище и возвращает путь до него
	 * @param $path
	 * @return mixed
	 */
	public function getFile($path)
	{
		$option = self::$CURL_OPTS;
		$option[CURLOPT_URL] = $this->getUrl($path);
		$option[CURLOPT_RETURNTRANSFER] = 1;
		$option[CURLOPT_PORT] = $this->port;
		if (!empty($this->userpwd)) {
			$option[CURLOPT_USERPWD] = $this->userpwd;
		}

		$curl = curl_init();
		curl_setopt_array($curl, $option);
		$file = curl_exec($curl);

		$httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if ($httpStatus != 200)
			return false;

		$filePath = '/tmp/webfiles';
		if (!file_exists($filePath))
			mkdir($filePath, 0700);

		$fileName = md5($file).rand(0, 99);
		$fullName = $filePath.'/'.$fileName;

		$fp = fopen($fullName, 'w');
		fwrite($fp, $file);
		fclose($fp);

		register_shutdown_function(array($this,'onEndRequest'), $fullName);

		return $fullName;
	}

	/**
	 * Получение свойств файла
	 * @param $path
	 * @return mixed
	 */
	public function getProperties($path)
	{
		$option = self::$CURL_OPTS;
		$option[CURLOPT_URL] = $this->getUrl($path);
		$option[CURLOPT_RETURNTRANSFER] = 1;
		$option[CURLOPT_CUSTOMREQUEST] = 'PROPFIND';
		$option[CURLOPT_PORT] = $this->port;
		if (!empty($this->userpwd)) {
			$option[CURLOPT_USERPWD] = $this->userpwd;
		}

		$curl = curl_init();
		curl_setopt_array($curl, $option);
		$result = curl_exec($curl);

		curl_close($curl);
		if (!$result)
			throw new CException('Invalid connection');

		$xml = new SimpleXMLElement($result);
		$responseSection = $xml->xpath('//D:multistatus/D:response');
		$responseSection = $responseSection[0];

		$result = array();

		$this->parseXml($responseSection, $result);

		return $result;
	}

	/**
	 * Удаление файла
	 * @param $path
	 * @return mixed
	 */
	public function deleteFile($path)
	{
		$option = self::$CURL_OPTS;
		$option[CURLOPT_URL] = $this->getUrl($path);
		$option[CURLOPT_RETURNTRANSFER] = 1;
		$option[CURLOPT_CUSTOMREQUEST] = 'DELETE';
		$option[CURLOPT_PORT] = $this->port;
		if (!empty($this->userpwd)) {
			$option[CURLOPT_USERPWD] = $this->userpwd;
		}

		$curl = curl_init();
		curl_setopt_array($curl, $option);
		$result = curl_exec($curl);

		$httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);
		if (!$result)
			throw new CException('Invalid connection');

		if ($httpStatus==404)
			return false;

		return true;
	}

	/**
	 * Перемещение файла
	 * @param $path
	 * @param $dest
	 * @return mixed
	 */
	public function moveFile($src, $dest)
	{
		$option = self::$CURL_OPTS;
		$option[CURLOPT_URL] = $this->getUrl($src);
		$option[CURLOPT_RETURNTRANSFER] = 1;
		$option[CURLOPT_CUSTOMREQUEST] = 'MOVE';
		$option[CURLOPT_PORT] = $this->port;
		$option[CURLOPT_HTTPHEADER] = array('Destination: '.$this->getUrl($dest));
		if (!empty($this->userpwd)) {
			$option[CURLOPT_USERPWD] = $this->userpwd;
		}

		$curl = curl_init();
		curl_setopt_array($curl, $option);
		curl_exec($curl);

		$httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);
		if ($httpStatus==204)
			return true;

		return false;
	}

	/**
	 * Копирование файла
	 * @param $src
	 * @param $dest
	 * @return mixed
	 */
	public function copyFile($src, $dest, $recursive=false)
	{
		$option = self::$CURL_OPTS;
		$option[CURLOPT_URL] = $this->getUrl($src);
		$option[CURLOPT_RETURNTRANSFER] = 1;
		$option[CURLOPT_CUSTOMREQUEST] = 'COPY';
		$option[CURLOPT_PORT] = $this->port;
		$option[CURLOPT_HTTPHEADER] = array('Destination: '.$this->getUrl($dest));
		if (!empty($this->userpwd)) {
			$option[CURLOPT_USERPWD] = $this->userpwd;
		}

		$curl = curl_init();
		curl_setopt_array($curl, $option);
		curl_exec($curl);

		$httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);
		if ($httpStatus==204)
			return true;

		return false;
	}

	/**
	 * Чтение директории
	 * @param $path
	 * @return mixed
	 */
	public function listDir($path)
	{
		$option = self::$CURL_OPTS;
		$option[CURLOPT_URL] = $this->getUrl($path);
		$option[CURLOPT_RETURNTRANSFER] = 1;
		$option[CURLOPT_PORT] = $this->port;
		$option[CURLOPT_CUSTOMREQUEST] = 'PROPFIND';
		if (!empty($this->userpwd)) {
			$option[CURLOPT_USERPWD] = $this->userpwd;
		}

		$curl = curl_init();
		curl_setopt_array($curl, $option);
		$result = curl_exec($curl);

		curl_close($curl);
		if (!$result)
			throw new CException('Invalid connection');

		$xml = new SimpleXMLElement($result);
		$responseSection = $xml->xpath('//D:multistatus/D:response');

		$dirs = array();
		foreach ($responseSection as $item) {
			$tmp = array();
			$this->parseXml($item, $tmp);
			$dirs[] = $tmp;
		}

		return $dirs;
	}

	/**
	 * Создание директории
	 * @param $path
	 * @return mixed
	 */
	public function mkDir($path)
	{
		$option = self::$CURL_OPTS;
		$option[CURLOPT_UPLOAD] = true; // установка загрузки
		$option[CURLOPT_PUT] = true;
		$option[CURLOPT_PORT] = $this->port;
		$option[CURLOPT_URL] = $this->getUrl($path).'/.empty';
		if (!empty($this->userpwd)) {
			$option[CURLOPT_USERPWD] = $this->userpwd;
		}

		$curl = curl_init();
		curl_setopt_array($curl, $option);
		curl_exec($curl);

		$httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if (in_array($httpStatus, array(200, 204, 207)))
			return true;

		return false;
	}

	private function parseXml($item, &$result)
	{
		$inner = $item->children('D', true);
		if (empty($inner)) {
			$result[$item->getName()] = (string)$item;
			return;
		}

		foreach ($inner as $item) {
			$this->parseXml($item, $result);
		}
	}

	public function onEndRequest($file)
	{
		if (file_exists($file))
			unlink($file);
	}

	/**
	 * Установка настроек транспорта файлов
	 * @param $config array
	 * @return bool
	 */
	public function setOptions($config)
	{
		if (!is_array($config))
			return false;

		foreach ($config as $key=>$value) {
			if (property_exists($this, $key))
				$this->$key = $value;
		}

		return true;
	}
}
