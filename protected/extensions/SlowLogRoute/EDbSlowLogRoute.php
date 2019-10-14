<?php
/**
 * Логгирование запросов, превышающих пределы по времени и памяти
 */
class EDbSlowLogRoute extends CLogRoute
{
	public $connectionID;
	public $logTableName='slow_log';
	public $autoCreateLogTable=true;
	/** @var float Память в Мб */
	public $maxMemory = 20;
	/** @var float Время в секундах */
	public $maxExecTime = 0.2;
	/**
	 * @var CDbConnection the DB connection instance
	 */
	private $_db;

	/**
	 * Initializes the route.
	 * This method is invoked after the route is created by the route manager.
	 */
	public function init()
	{
		parent::init();

		if($this->autoCreateLogTable)
		{
			$db=$this->getDbConnection();
			try
			{
				$db->createCommand()->delete($this->logTableName,'0=1');
			}
			catch(Exception $e)
			{
				$this->createLogTable($db,$this->logTableName);
			}
		}

		if ($this->enabled && (Yii::app() instanceof CWebApplication))
		{
			Yii::app()->attachEventHandler('onEndRequest', array($this, 'onEndRequest'));
		}
	}

	public function onEndRequest()
	{
		$logger = Yii::getLogger();
		$memory = $logger->getMemoryUsage();
		$time = $logger->getExecutionTime();

		// logging
		if ($memory > $this->maxMemory*1024*1024 || $time > $this->maxExecTime) {
			//$memory = round($memory/1024/1024, 3).' Mb';
			//$time .= ' s';

			$command=$this->getDbConnection()->createCommand();
			$command->insert($this->logTableName,array(
				'memory'=>$memory,
				'exec_time'=>$time,
				'format_memory'=>round($memory/1024/1024, 3).' Mb',
				'logtime'=>date("Y-m-d H:i:s"),
				'url'=>Yii::app()->getRequest()->getHostInfo().Yii::app()->getRequest()->getRequestUri(),
			));
		}

	}

	/**
	 * Creates the DB table for storing log messages.
	 * @param CDbConnection $db the database connection
	 * @param string $tableName the name of the table to be created
	 */
	protected function createLogTable($db,$tableName)
	{
		$db->createCommand()->createTable($tableName, array(
			'id'=>'pk',
			'memory' => 'integer',
			'exec_time' => 'float',
			'format_memory'=>'VARCHAR(128)',
			'logtime'=>'VARCHAR(128)',
			'url'=>'VARCHAR(255)',
		));
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
				throw new CException(Yii::t('yii','CDbLogRoute.connectionID "{id}" does not point to a valid CDbConnection application component.',
					array('{id}'=>$id)));
		}
		else
		{
			$dbFile=Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'log-'.Yii::getVersion().'.db';
			return $this->_db=new CDbConnection('sqlite:'.$dbFile);
		}
	}

	/**
	 * Stores log messages into database.
	 * @param array $logs list of log messages
	 */
	protected function processLogs($logs)
	{
	}

}
