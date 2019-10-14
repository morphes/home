<?php

class StaticresultCommand extends CConsoleCommand
{
	/**
	 * Константы дублированы из JobResponce в StaticCommand
	 */
	const STATUS_PROGRESS = 1;
	const STATUS_DONE = 2;
	const STATUS_DELETED = 3;
	const STATUS_WRONG = 4;

	/**
	 * Константы дублированы из ImageComponent
	 */
	const ORIGIN_PREFIX = 'img:';
	const PREVIEW_PREFIX = 'preview:';
	const WRONG_IMG_LIST = 'wrong_img_list'; // Список ключей оригиналов, со статусом WRONG

	public function actionWorker()
	{
		Yii::import('application.components.imageHandler');

		$worker = Yii::app()->gearman->worker();
		$worker->addFunction('imageJobResponse', array($this, 'imageResponse'));

		while($worker->work() ){
			if (GEARMAN_SUCCESS != $worker->returnCode()) {
				echo "Worker failed: " . $worker->error() . "\n";
			}
			echo "\n";
		}
	}

	public function imageResponse(GearmanJob $job)
	{
		$data = $job->workload();
		try {
			$fullData = unserialize($data);
			$status = $fullData['status'];
			$data = $fullData['data'];
			$message = $fullData['message'];

			switch ($status) {
				case self::STATUS_WRONG: {
					$this->wrongData($data, $message);
				} break;
				case self::STATUS_DONE: {
					$this->successData($data, $message);
				} break;
				default:
					throw new CException('Unsupported status code');
			}

		} catch (Exception $e) {
			echo 'Error data: '.$data."\n";
			echo $e->getMessage();
		}
	}

	private function successData($data, $message)
	{
		// TODO: ввести валидацию входных данных
		if (empty($data['id']) || empty($data['configName']))
			throw new CException('Invalid data format');

		$id = intval($data['id']);
		$configName = $data['configName'];
		$redisKey = self::PREVIEW_PREFIX.$id.':'.$configName;

		unset($data['resizeName']);

		$redis = $this->getRedis();
		$previewData = $redis->get($redisKey);

		if (empty($previewData)) { return false; }

		foreach ($data as $key => $value) {
			$previewData[$key] = $value;
		}
		$redis->set($redisKey, $previewData);

		echo date('[Y-m-d H:i:s] ').' SUCCESS KEY:'.$redisKey;

		if (!empty($message)) {
			echo $message ."\n";
		}
	}

	private function wrongData($data, $message)
	{
		if (empty($data['id']))
			throw new CException('Invalid data format');

		$id = intval($data['id']);

		echo date('[Y-m-d H:i:s] ').' WRONG DATA'."\n";
		$redis = $this->getRedis();

		$key = self::ORIGIN_PREFIX.$id;
		$data = $redis->get($key);

		if (empty($data)) {
			return false;
		}

		$data['status'] = self::STATUS_WRONG;
		$redis->set($key, $data);

		$redis->lPush(self::WRONG_IMG_LIST, $key);

		if (!empty($message)) {
			echo $message ."\n";
		}

		return true;
	}

	private function getRedis()
	{
		return Yii::app()->predis->getConnection();
	}

}
