<?php
/**
 * Обертка для данных из redis по оригиналу фотки
 */
class OriginImage
{
	public $id;
	public $path='';
	public $name='';
	public $ext='';
	public $user_id;
	public $size=0;
	public $create_time;
	public $width=0;
	public $height=0;

	private $imgComponent;
	/** @var FileUpload */
	private $_transport;
	private $fileLink;

	public function __construct($imgComponent, $config=array())
	{
		if (!is_array($config)) {
			throw new CException('Invalid parameter');
		}

		foreach ($config as $key=>$value) {
			if (property_exists($this, $key))
				$this->$key = $value;
		}

		$this->imgComponent = $imgComponent;
	}

	/**
	 * Возвращает размер файла в Мб
	 * @param $uf
	 * @return float|int
	 */
	public function getFileSize()
	{
		return round($this->size/1024/1024, 3);
	}

	/**
	 * Получение пути до оригинала (выкачивается на веб сервер)
	 * @return mixed
	 */
	public function getFile()
	{
		if (empty($this->fileLink)) {
			$transportClass = $this->imgComponent->transportClass;
			$this->_transport = new $transportClass();
			$this->_transport->setOptions($this->imgComponent->transportOptions);
			$this->fileLink = $this->_transport->getFile('dav/'.$this->path);
			if (empty($this->fileLink))
				$this->fileLink = null;
		}

		return $this->fileLink;
	}
}
