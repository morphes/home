<?php

class Gearman extends CApplicationComponent
{

	public $servers;

	// default timeout for client
	public $timeout = 3000;

	protected $client;
	protected $worker;

	public function init()
	{
		parent::init();
	}

	protected function setServers($instance)
	{
		foreach ($this->servers as $s) {
			$instance->addServer($s['host'], $s['port']);
		}

		return $instance;
	}

	public function client()
	{
		if (!$this->client) {
			$this->client = $this->setServers(new GearmanClient());
		}

		return $this->client;
	}

	public function worker()
	{
		if (!$this->worker) {
			$this->worker = $this->setServers(new GearmanWorker());
		}

		return $this->worker;
	}

	/**
	 * Запуск задачи в нормальном приоритете
	 * @param $funcName
	 * @param $workload
	 * @param bool $background
	 */
	public function doNormal($funcName, $workload, $background=true)
	{
		if ($background) {
			$priority = 'doBackground';
		} else {
			$priority = 'doNormal';
		}
		$this->sendJob($priority, $funcName, $workload, false);
	}

	public function doHight($funcName, $workload, $background=true)
	{
		$this->sendJob('doHigh', $funcName, $workload, $background);
	}

	public function doLow($funcName, $workload, $background=true)
	{
		$this->sendJob('doLow', $funcName, $workload, $background);
	}

	protected function sendJob($gearmanFunc, $funcName, $workload, $background)
	{
		$client = $this->client();
		$client->setTimeout($this->timeout);
		$functionName = $funcName;

		$data = serialize($workload);
		$func = $gearmanFunc;
		if ($background)
			$func .= 'Background';
		$client->$func($functionName, $data);
	}

	/**
	 * Добавление задачи в фоновую обработку (упрощеный интерфейс), возможно добавить приоритеты
	 */
	public function appendJob($functionName, $workload)
	{
		$client = $this->client();
		$client->setTimeout($this->timeout);
		$functionName = $functionName;

		$data = serialize($workload);
		$client->doBackground($functionName, $data);
	}

}
