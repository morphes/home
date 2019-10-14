<?php
$static = new StaticCommand();
$static->worker();

class StaticCommand
{
	const GEARMAN_TASK = 'staticPreview';
	const GEARMAN_HOST = '127.0.0.1';
	const GEARMAN_PORT = 4730;
	const GEARMAN_RESULT_JOB = 'imageJobResponse';

	private $protectedDir='';
	private $publicDir='';

	public function worker()
	{
		require_once('imageHandler/imageHandler.php');

		// Указывает на каталог с картинками(корневой)
		$this->protectedDir = __DIR__.'/../protected';
		$this->publicDir = __DIR__.'/../public';
		$worker = new GearmanWorker();

		$worker->addServer(self::GEARMAN_HOST, self::GEARMAN_PORT);
		$worker->addFunction(self::GEARMAN_TASK, array($this, 'generatePreview'));

		while($worker->work() ){
			if (GEARMAN_SUCCESS != $worker->returnCode()) {
				echo "Worker failed: " . $worker->error() . "\n";
			}
			echo "\n";
		}
	}

	public function generatePreview(GearmanJob $job)
	{
		echo date('[Y-m-d H:i:s] ');
		$data = $job->workload();
		try {
			$imgData = ImageData::getObject($data);

			$origin = $this->protectedDir .'/'.$imgData->src;

			if (!file_exists($origin)) {
				JobResponse::sendMessage(JobResponse::STATUS_WRONG, array('id'=>$imgData->id), 'File not found ID:'.$imgData->id);

				echo 'Origin image does not exists DATA:'.$data;
				return;
			}

			$publicDir = $this->publicDir.'/'.$imgData->path;
			if (!file_exists($publicDir)) {
				@mkdir($publicDir, 0755, true);
			}

			foreach ($imgData->items as $resizeName => $config) {
				$previewName = $publicDir.'/'.$resizeName.'_'.$imgData->filename.'.jpg';

				if (!$imgData->forceGenerate && file_exists($previewName)) {
					$data = @getimagesize($previewName);
					$sendData = array(
						'id'=>$imgData->id,
						'status'=>JobResponse::STATUS_DONE,
						'configName'=>$resizeName,
						'size' => filesize($previewName),
						'attributes' => array(
							'width'=>$data[0],
							'height'=>$data[1],
						),
					);

					JobResponse::sendMessage(JobResponse::STATUS_DONE, $sendData);
					continue;
				}

				$imageHandler = new imageHandler($origin, imageHandler::FORMAT_JPEG);
				$imageHandler->updateColorspace();

				$bestFit = isset($config[4]) ? $config[4] : true;
				$border = isset($config['border']) ? $config['border'] : false;

				if ($config[2] == 'crop') {
					$imageHandler->cropImage($config[0], $config[1], $config[3]);
				} else {

					if (isset($config['decrease']) && $config['decrease'] == true) {
						// Если фотка будет меньше указанного размера, то ресайзиться не будет
						$resizeType = imageHandler::RESIZE_DECREASE;
					} else {
						// Ресайзим
						$resizeType = imageHandler::RESIZE_BOTH;
					}
					$imageHandler->resizeImage($config[0], $config[1], $config[3], $resizeType, $bestFit);

					// Нужен ли водяной знак
					if (isset($config['watermark']) && $config['watermark'] == true) {
						$imageHandler->watemark(__DIR__.'/img/logo-wm.png');
					}

					// добавление прозрачного бордера
					if ($border)
						$imageHandler->addTransparentBorder($config[0], $config[1]);
				}

				$imageHandler->saveImage($previewName);

				// Если нарезали и сохранили на диск - пишем об этом в redis

				$sendData = array(
					'id'=>$imgData->id,
					'status'=>JobResponse::STATUS_DONE,
					'configName'=>$resizeName,
					'size' => $imageHandler->getImageSize(),
					'attributes' => array(
						'width'=>$imageHandler->getImage()->getImageWidth(),
						'height'=>$imageHandler->getImage()->getImageHeight(),
					),
				);
				JobResponse::sendMessage(JobResponse::STATUS_DONE, $sendData);

			}

			echo 'Image resized from ORIGIN: /'.$imgData->src;


		} catch (Exception $e) {
			echo 'Error data: '.$data."\n";
			echo $e->getMessage();
		}
	}
}

class JobResponse
{
	const STATUS_PROGRESS = 1;
	const STATUS_DONE = 2;
	const STATUS_DELETED = 3;
	const STATUS_WRONG = 4;

	private static $client=null;

	private static function getClient()
	{
		if ( self::$client!==null )
			return self::$client;

		self::$client = new GearmanClient();
		self::$client->addServer(StaticCommand::GEARMAN_HOST, StaticCommand::GEARMAN_PORT);
		return self::$client;
	}

	public static function sendMessage($status=self::STATUS_DONE, $data=array(), $message='')
	{
		$client = self::getClient();
		$sendData = array(
			'status'=>$status,
			'data'=>$data,
			'message'=>$message,
		);
		$client->doBackground(StaticCommand::GEARMAN_RESULT_JOB, serialize($sendData));
	}
}

class ImageData
{
	public $id;
	public $src=''; // путь до оригинала
	public $path=''; // путь для сохранение ресайзов
	public $filename=''; // имя файла для сохр ресайзов
	public $items=array(); // Конфигурация для нарезки изображений
	public $forceGenerate=false; // Флаг принудительной нарезки

	/**
	 * @param $data
	 * @return ImageData
	 */
	public static function getObject($data) {
		$data = unserialize($data);
		$item = new self();
		$item->setData($data);
		return $item;
	}

	private function setData($data) {
		if (!is_array($data)) {
			throw new Exception('Invalid data format');
		}
		foreach ($data as $key => $dataItem) {
			if (property_exists($this, $key)) {
				$this->$key = $dataItem;
			}
		}
	}
}
