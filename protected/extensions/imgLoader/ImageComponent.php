<?php
/**
 *
 */
Yii::import('ext.imgLoader.models.OriginImage');
class ImageComponent extends CApplicationComponent
{
	const STATUS_PROGRESS = 1;
	const STATUS_DONE = 2;
	const STATUS_DELETED = 3;
	const STATUS_WRONG = 4;

	const ORIGIN_PREFIX = 'img:';
	const PREVIEW_PREFIX = 'preview:';
	const DESC_PREFIX = 'imgdesc:';

	const IMAGE_INCREAMENT = 'img_increment';
	const DEFAULT_IMG = '/images/nophoto.svg';
	const GEARMAN_TASK = 'staticPreview';

//	const WRONG_IMG_LIST = 'wrong_img_list'; // Список ключей оригиналов, со статусом WRONG

	public $transportClass='WebDavFile';
	public $transportOptions=array('host'=>'img.myhome.local', 'port'=>8080, 'userpwd'=>'admin:12345');

	public $staticDomain = 'http://img.myhome.local:8080';
	public $redisId='predis';
	public $gearmanId='gearman';
	// Проверка соединения с redis
	public $pingRedis=false;
	// защитный интервал перед повторной постановкой в очередь на ресайз
	public $taskDelay = 600;

	/** @var array Список всех превью проекта (уникальные ключи используются для хранения и доступа */
	private static $preview = array(
		'crop_45' => array(45, 45, 'crop', 80),
		'crop_60' => array(60, 60, 'crop', 80),
		'crop_80' => array(80, 80, 'crop', 80),
		'crop_150' => array(150, 150, 'crop', 80),
		'crop_180' => array(180, 180, 'crop', 80),
		'crop_200' => array(200, 200, 'crop', 80),
		'crop_210' => array(210, 210, 'crop', 80),
		'crop_230' => array(230, 230, 'crop', 80),
		'crop_380' => array(380, 380, 'crop', 80), // New user profile (for projects)
		'crop_360x305' => array(380, 380, 'crop', 80), // New spec list
		'resize_710x475' => array(710, 475, 'resize', 90),
		'resize_1920x1080' => array(1920, 1080, 'resize', 90, 'watermark' => true, 'decrease' => true),
		'width_520' => array(520, 0, 'resize', 90, false),
	);

	/** @var RedisComponent */
	private $_redis;
	private $_gearman;

	private $_previewCache=array();
	private $_descCache=array();

	/**
	 *
	 * @param $path полный путь до файла
	 * @param $name Название файла (с расширением)
	 * @throws CException
	 */
	public function putImage($path, $name, $userId=null, $desc='')
	{
		if ( !is_file($path) )
			throw new CException('No file to upload');

		$data = pathinfo($name);
		$name = isset($data['filename']) ? $data['filename'] : '';
		$ext = isset($data['extension']) ? $data['extension'] : 'JPEG';

		$redis = $this->getRedis();
		$id = $redis->incr(self::IMAGE_INCREAMENT);

		$fileSize = filesize($path);
		list($filePath, $fileName) = $this->generatePath($id);
		$imgSizes = @getimagesize($path);

		$filePath = $filePath.'/'.$fileName.'.'.$ext;


		// данные оригинала для redis
		$imgData = array(
			'path'=>$filePath,
			'user_id'=>$userId,
			'name'=>$name,
			'ext'=>$ext,
			'size'=>$fileSize,
			'status'=>self::STATUS_DONE,
			'create_time'=>time(),
			'width'=>isset($imgSizes['width']) ? $imgSizes['width'] : '',
			'height'=>isset($imgSizes['height']) ? $imgSizes['heigth'] : '',
		);

		$transport = new $this->transportClass();
		$transport->setOptions($this->transportOptions);
		$result = $transport->putFile($path, 'dav/'.$filePath);
		if (!$result)
			return null;

		$key = self::ORIGIN_PREFIX.$id;
		if ( $redis->set($key, $imgData) ) {
			if (!empty($desc)) {
				$descKey = self::DESC_PREFIX.$id;
				$redis->set($descKey, (string)$desc);
			}
			return $id;
		}

		return null;
	}

	/**
	 * Возвращает строку оригинального размера изображения (например 100x100)
	 */
	public function getOriginSize($id)
	{
		$origin = $this->getOrigin($id);
		if ($origin === null) {
			return '';
		}
		return $origin->width.'x'.$origin->height;
	}

	public function generatePreview($id, array $config, $background=true, $forceGenerate=false)
	{
		$funcName = $background ? 'doLow' : 'doNormal';
		return $this->makePreview($id, $config, true, $funcName, $forceGenerate);
	}

	/**
	 * Отдает данные превью, осуществляет кеширование в скрипте
	 * @param $id
	 * @param $configName
	 * @return mixed
	 */
	private function getPreviewData($id, $configName, $useCache=true)
	{
		if ( !isset(self::$preview[$configName]) )
			throw new CException('Invalid config key');

		if ( isset($this->_previewCache[$id][$configName]) && $useCache ) {
			return $this->_previewCache[$id][$configName];
		}
		$redis = $this->getRedis();
		$key = self::PREVIEW_PREFIX.$id.':'.$configName;

		// берем данные о превью в redis
		$data = $redis->get($key);
		$this->_previewCache[$id][$configName] = $data;
		return $data;
	}

	public function getPreview($id, $configName)
	{
		// если пустой ключ - дефолтна пикча
		if (empty($id)) { return self::DEFAULT_IMG; }

		$data = $this->getPreviewData($id, $configName);
		if (!empty($data)) {
			// Некорректный конфиг
			if (!isset($data['status']) || empty($data['url']) ) {
				return self::DEFAULT_IMG;
			}
			// все ок, отдаем урл
			if ($data['status'] == self::STATUS_DONE) {
				return $this->staticDomain.$data['url'];
			}
			// В обработке
			if ($data['status'] == self::STATUS_PROGRESS) {
				$createTime = empty($data['create_time']) ? 0 : $data['create_time'];
				// если укладывается в интервал - отдаем урл, иначе - ставим повторно на нарезку
				if ( time() > ($createTime + $this->taskDelay) ) {
					$this->makePreview($id, array($configName), true, 'doHight');
				}
				return $this->staticDomain.$data['url'];
			}

			// если удаленные или бракованные данные - заглушка
			return self::DEFAULT_IMG;
		}
		// нет данных, ищем оригинал
		$result = $this->makePreview($id, array($configName), true, 'doHight');
		if (!$result) {
			return self::DEFAULT_IMG;
		}

		$data = $this->getPreviewData($id, $configName, false);
		return $this->staticDomain.$data['url'];
	}


	/**
	 * Получение объекта оригинала изображения
	 * @param $id
	 * @return null|OriginImage
	 */
	public function getOrigin($id)
	{
		$redis = $this->getRedis();
		$key = self::ORIGIN_PREFIX.$id;
		$data = $redis->get($key);

		if (empty($data)) {
			return null;
		}

		return new OriginImage($this, $data);
	}

	/**
	 * Удаление не используемого превью
	 * @param $id
	 * @param array $configName
	 * @return bool
	 * @throws CException
	 */
	public function deletePreview($id, $configName)
	{
		if ( !isset(self::$preview[$configName]) )
			throw new CException('Invalid config key');

		$redis = $this->getRedis();
		$key = self::PREVIEW_PREFIX.$id.':'.$configName;
		// берем данные о превью в redis
		$data = $redis->get($key);
		// удаление файла и в случае успеха - из базы
		if ( !empty($data) && !empty($data['url'])) {
			$transport = new $this->transportClass();
			$transport->setOptions($this->transportOptions);
			$result = $transport->deleteFile('delete/'.$data['url']);
			if ($result) {
				$result = $redis->delete($key);
			}
			return $result;
		}

		// очистка БД, при некорректных данных
		$result = $redis->delete($key);
		return (bool)$result;
	}
//
//	public function deleteOrigin($id)
//	{
//
//	}

	/**
	 * Генерирует обобщенный путь для protected и public
	 * а также имя для сохранения
	 * @param $id
	 * @return array
	 */
	protected function generatePath($id)
	{
		$src = intval($id);
		$name = str_pad($src%1000, 3, '0', STR_PAD_LEFT);
		$src = intval($src/1000);

		$result = '';
		while ($src > 1000) {
			$tmp = $src%1000;
			$src = intval($src/1000);
			$result = '/'.$tmp.$result;
		}
		$result = $src.$result;

		$path = str_pad( $result, 11, '000/', STR_PAD_LEFT);

		return array($path, $name);
	}

	/**
	 * @return Gearman
	 * @throws CException
	 */
	protected function getGearman()
	{
		if ($this->_gearman !== null)
			return $this->_gearman;

		if ( ($this->_gearman=Yii::app()->getComponent($this->gearmanId)) instanceof CApplicationComponent ) {
			return $this->_gearman;
		}

		throw new CException('Invalid file connector');
	}

	/**
	 * @return RedisComponent
	 * @throws CException
	 */
	protected function getRedis()
	{
		if ($this->_redis !== null) {
			if ($this->pingRedis) {
				return $this->_redis->getConnection();
			}
			return $this->_redis;
		}

		if ( ($this->_redis=Yii::app()->getComponent($this->redisId)) instanceof CApplicationComponent ) {
			return $this->_redis;
		}

		throw new CException('Invalid redis connector');
	}

	/**
	 * Обработчик создания превью.
	 * Выполняет подготовку данных, постановку в очередь и записи в Redis
	 * @param $id
	 * @param array $config
	 * @param bool $background
	 * @param string $function
	 * @param bool $forceGenerate
	 * @return bool
	 * @throws CException
	 */
	private function makePreview($id, array $config, $background=true, $function='doNormal', $forceGenerate=false)
	{
		$redis = $this->getRedis();
		$key = self::ORIGIN_PREFIX.$id;
		$data = $redis->get($key);

		// если в redis нет данных об оригинале, то стоп
		if (empty($data['status']) || $data['status']!=self::STATUS_DONE) {
			return false;
		}

		list($filePath, $fileName) = $this->generatePath($id);

		$job = array(
			'id'=>$id,
			'src'=>ltrim($data['path'], '/'),
			'path'=>$filePath, // каталоги до места хранения
			'filename'=>$fileName, // имя оригинала в ФС статика
		);

		if ($forceGenerate) {
			$job['forceGenerate'] = true;
		}

		// Предварительные данные для превью в Redis
		$imgData = array(
			'name'=> isset($data['name']) ? $data['name'] : '',
			'ext'=>'jpg',
			'size'=>0,
			'status'=>self::STATUS_PROGRESS,
			'create_time'=>time(),
		);

		// формирование списка для нарезки фоток
		$items = array();
		foreach ($config as $item) {
			if (!isset(self::$preview[$item]))
				throw new CException('Invalid image size');
			$items[$item] = self::$preview[$item];

			$fullName = $filePath.'/'.$item.'_'.$fileName.'.jpg';
			$previewKey = self::PREVIEW_PREFIX.$id.':'.$item;
			$imgData['url'] = '/'.$fullName;
			if (!$redis->exists($previewKey)) {
				// запись демо данных в redis, если ключа не было
				$redis->set($previewKey, $imgData);
			}
		}

		$job['items'] = $items;
		$this->getGearman()->$function(self::GEARMAN_TASK, $job, $background);

		return true;
	}

	/**
	 * Получение описания для картинки
	 * @param $id
	 * @return string
	 */
	public function getDesc($id, $useCache=true)
	{
		if ( isset($this->_descCache[$id]) && $useCache ) {
			return $this->_descCache;
		}

		$redis = $this->getRedis();
		$descKey = self::DESC_PREFIX.$id;
		$desc = $redis->get($descKey);
		return empty($desc) ? '' : $desc;
	}

	/**
	 * Установка описания картинки
	 * @param $id
	 * @param $desc
	 * @return bool
	 */
	public function setDesc($id, $desc)
	{
		$redis = $this->getRedis();
		$originKey = self::ORIGIN_PREFIX.$id;
		$data = $redis->get($originKey);
		if (empty($data)) { return false; }

		$this->_descCache[$id] = (string)$desc;

		$descKey = self::DESC_PREFIX.$id;
		return $redis->set($descKey, (string)$desc);
	}

	/**
	 * Получение ширины превью
	 * @param $id
	 * @param $configName
	 * @return null
	 * @throws CException
	 */
	public function getPreviewWidth($id, $configName, $default=null)
	{
		if (empty($id)) { return $default; }

		$data = $this->getPreviewData($id, $configName);
		if (empty($data['attributes']['width']))
			return $default;

		return $data['attributes']['width'];
	}

	/**
	 * Получение высоты превью
	 * @param $id
	 * @param $configName
	 * @return null
	 * @throws CException
	 */
	public function getPreviewHeight($id, $configName, $default=null)
	{
		if (empty($id)) { return $default; }

		$data = $this->getPreviewData($id, $configName);
		if (empty($data['attributes']['height']))
			return $default;

		return $data['attributes']['height'];
	}

}
