<?php
/**
 * Description of SphinxReindexMediaCommand
 *
 * @author alexsh
 */
Yii::import('application.modules.media.models.Media');
class MediaReindex
{
	// Порции данных, которые за раз выбираются из БД для индексации
	const STEP_MEDIA_KNOWLEDGE = 1000;
	const STEP_MEDIA_NEW = 1000;
	const STEP_MEDIA_EVENT = 1000;

	private $_prefix = '';

	public function init()
	{
		$this->_prefix = Yii::app()->sphinx->tablePrefix;
	}

	/**
	 * Переиндексация всех индексов
	 */
	public function run()
	{
		$this->clearIndex();

		$this->indexKnowledge();
		$this->indexNews();
		$this->indexEvent();
	}

	/**
	 * Метод предназначенный для переиндексации одного элемента
	 *
	 * @param $type Тип данных
	 * @param $id Идентификатор записи из БД типа $type
	 * @return int
	 */
	public function indexOne($type, $id)
	{
		$object = $this->getObjects($type, $id, $id + 1);
		return $this->updateSphinx($type, $object);
	}


	/**
	 * Удаляет весь общий индекс для Журнала
	 */
	private function clearIndex()
	{
		try {
			$minMax = Yii::app()->sphinx->createCommand("SELECT MIN(id) as `min`, MAX(id) as `max` FROM {{media}} GROUP BY `type`")->queryAll();

			foreach($minMax as $interval)
			{
				for ($i = $interval['min']; $i <= $interval['max']; $i += 1000) {
					$sphinxDel = 'DELETE FROM {{media}} WHERE id IN ('.implode(',', range($i, $i + 1000)).')';
					Yii::app()->sphinx->createCommand($sphinxDel)->execute();
				}
			}
		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}
	}

	/**
	 * Переиндексация Знаний из Журнала в общий индекс
	 */
	public function indexKnowledge()
	{
		echo "====> {$this->_prefix}media_knowledge adding to common Media index <===\n";
		$start = time();
		echo 'Time start: '.date('d.m.Y H:i:s')."\n";

		try {
			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM media_knowledge';
			list($min, $max, $cnt) = Yii::app()->db->createCommand($sql)->queryRow(false);

			echo "{$cnt} items to index\n";

			$total = 0;
			for ($i = $min; $i<=$max; $i+=self::STEP_MEDIA_KNOWLEDGE)
			{
				// Получаем порцию данных из БД
				$objects = $this->getObjects(Media::TYPE_KNOWLEDGE, $i, $i+self::STEP_MEDIA_KNOWLEDGE);

				// Обновляем полученную порцию в сфинксе
				$resultCount = $this->updateSphinx(Media::TYPE_KNOWLEDGE, $objects);

				$total += $resultCount;

				echo "{$resultCount} items was written \n";
			}
			echo "Total index result: {$total}\n";
		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}


	/**
	 * Переиндексация Новостей из раздела Журнал в общий индекс
	 */
	public function indexNews()
	{
		echo "====> {$this->_prefix}media_new adding to common Media index <===\n";
		$start = time();
		echo 'Time start: '.date('d.m.Y H:i:s')."\n";

		try {
			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM media_new';
			list($min, $max, $cnt) = Yii::app()->db->createCommand($sql)->queryRow(false);

			echo "{$cnt} items to index\n";

			$total = 0;
			for ($i = $min; $i<=$max; $i+=self::STEP_MEDIA_NEW)
			{
				// Получаем порцию данныз из БД
				$objects = $this->getObjects(Media::TYPE_NEWS, $i, $i+self::STEP_MEDIA_NEW);

				// Обновляем полученные данные в сфинксе
				$resultCount = $this->updateSphinx(Media::TYPE_NEWS, $objects);

				$total += $resultCount;

				echo "{$resultCount} items was written \n";
			}
			echo "Total index result: {$total}\n";
		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}

	/**
	 * Переиндексация Новостей из раздела Журнал в общий индекс
	 */
	public function indexEvent()
	{
		echo "====> {$this->_prefix}media_event adding to common Media index <===\n";
		$start = time();
		echo 'Time start: '.date('d.m.Y H:i:s')."\n";

		try {
			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM media_event';
			list($min, $max, $cnt) = Yii::app()->db->createCommand($sql)->queryRow(false);

			echo "{$cnt} items to index\n";

			$total = 0;
			for ($i = $min; $i<=$max; $i+=self::STEP_MEDIA_EVENT)
			{
				// Получаем порцию данныз из БД
				$objects = $this->getObjects(Media::TYPE_EVENT, $i, $i+self::STEP_MEDIA_EVENT);

				// Обновляем полученные данные в сфинксе
				$resultCount = $this->updateSphinx(Media::TYPE_EVENT, $objects);

				$total += $resultCount;

				echo "{$resultCount} items was written \n";
			}
			echo "Total index result: {$total}\n";
		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}

		echo 'Time stop: '.date('d-M-Y H:i:s')."\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}

	/**
	 * Возвращает ассоциативный массив с данными из БД, в следующем формате
	 * array(
	 * 	array('id' => '', 'name' => '', 'content' => '', 'create_time' => '', 'update_time' => ''),
	 * 	array('id' => '', 'name' => '', 'content' => '', 'create_time' => '', 'update_time' => ''),
	 * 	...
	 * )
	 * @param $type Идентификатор типа данных
	 * @param $startID Начальный индентификатор записей для взятия из БД
	 * @param $endID Конечный индентификатор записей для взятия из БД
	 * @return array Массив данных
	 */
	private function getObjects($type, $startID, $endID)
	{
		$startID = (int)$startID;
		$endID = (int)$endID;

		// Выбираем записи из БД, только с опубликованным статусом
		switch ($type)
		{
			case Media::TYPE_KNOWLEDGE:
				$sql = 'SELECT id, title as name, content, create_time, update_time '
					.' FROM media_knowledge '
					.' WHERE id >= '.$startID.' AND id < '.$endID
					.' AND status = 1';
				break;

			case Media::TYPE_NEWS:
				$sql = 'SELECT id, title as name, content, create_time, update_time '
					.' FROM media_new '
					.' WHERE id >= '.$startID.' AND id < '.$endID
					.' AND status = 1';
				break;

			case Media::TYPE_EVENT:
				$sql = 'SELECT id, name, content, create_time, update_time '
					.' FROM media_event'
					.' WHERE id >= '.$startID.' AND id < '.$endID
					.' AND status = 2';
				break;

			default:
				return array();
		}

		try{
			$objects = Yii::app()->db->createCommand($sql)->queryAll();
		} catch (Exception $e) {
			echo $e->getMessage();
		}

		return $objects;
	}

	/**
	 * Обновляет данные в индексе по переданной пачке данных в objects
	 *
	 * @param $type Тип данных Журнала
	 * @param $objects Массив записей на обновление в нидкесе
	 * @return integer Количество обработанных записей
	 */
	private function updateSphinx($type, $objects)
	{
                $resultCount = 0;

		if ( ! empty($objects)) {
			$sphinxQl = 'REPLACE INTO {{media}} (id, item_id, type, name, description, create_time, update_time) VALUES ';
			$cnt = 0;
			foreach ($objects as $object) {
				if ($cnt > 0)
					$sphinxQl .= ',';
				$cnt++;
				// ФОрмируем уникальный ID для Знаний в общем индексе
				$id = $object['id'] + $type * Media::FACTOR;

				$sphinxQl .= "({$id}, {$object['id']}, $type, '".addslashes($object['name'])."', '".addslashes(strip_tags($object['content']))."', {$object['create_time']}, {$object['update_time']})";
			}
                        try {
                                $resultCount = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
                        } catch (Exception $e) {
                                echo $e->getMessage();
                        }
		}
		return $resultCount;
	}
}