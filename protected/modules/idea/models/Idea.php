<?php

class Idea //extends CActiveRecord
{
	public $author_id=null;
	public $type_id=null;
	public $name=null;
	public $average_rating=null;
	public $desc=null;
        public $object=null;

	private static $_model=null;


	public static $typeCoef = 1000000; // коэффициент для sphinx document id

	public static function model($className=__CLASS__)
	{
		if (is_null(self::$_model))
			self::$_model=new self();

		return self::$_model;
	}

	public function __construct($scenario='insert')
	{
	}

	public function findByPk($pk, $condition = '', $params = array())
	{
		$typeId = intval( $pk / self::$typeCoef );
		if (empty( Config::$ideaTypes[$typeId] ))
			throw new Exception('Invalid idea type');

		$pk -= $typeId*self::$typeCoef;
		$class = Config::$ideaTypes[$typeId];

		/** @var $object Interior */
		$object = $class::model()->findByPk($pk, $condition, $params);

		if (is_null($object))
			return null;

		$model = new self();

		$model->author_id = $object->author_id;
		$model->type_id = $typeId;
		$model->name = $object->name;
		$model->desc = $object->desc;
		$model->average_rating = $object->average_rating;
                $model->object = $object;
		unset($object);
		return $model;
	}

        public function getTypeLabel()
        {
                $class = Config::$ideaTypes[$this->type_id];
                return Config::$commentType[$class];
        }

	/**
	 * Возвращает общее количество идей в проекте
	 * @return int
	 */
	static public function getIdeasQuantity()
	{
		Yii::import('application.modules.idea.models.Interior');
		Yii::import('application.modules.idea.models.Interiorpublic');
		Yii::import('application.modules.idea.models.Architecture');

		$key = 'Idea::totalQuantity';
		$value = Yii::app()->cache->get($key);
		if ( ! $value) {
			$interiorCount = Interior::model()->count('status=:s1 OR status=:s2', array(':s1'=>Interior::STATUS_ACCEPTED, ':s2'=>Interior::STATUS_CHANGED));
			$interiorpublicCount = Interiorpublic::model()->count('status=:s1 OR status=:s2', array(':s1'=>Interiorpublic::STATUS_ACCEPTED, ':s2'=>Interiorpublic::STATUS_CHANGED));
			$architectureCount = Architecture::model()->count('status=:s1 OR status=:s2', array(':s1' => Architecture::STATUS_ACCEPTED, ':s2' => Architecture::STATUS_CHANGED));

			$value = (int)$interiorCount + (int)$interiorpublicCount + (int)$architectureCount;
			Yii::app()->cache->set($key, $value, Cache::DURATION_MAIN_PAGE);
		}

                return $value;
	}


	/**
	 * Возвращает общее количество фотографий во всех идеях:
	 * общественные, жилые, архитектура
	 *
	 * @return int|mixed
	 */
	static public function getIdeasPhotoQuantity($round = false, $format = false)
	{
		$key = 'Idea::totalPhotoQuantity';
		$value = Yii::app()->cache->get($key);

		if ( ! $value) {

			$value = self::getQntPhotosInterior() + self::getQntPhotosInteriorpublic() + self::getQntPhotosArchitecture();
			Yii::app()->cache->set($key, $value, Cache::DURATION_MAIN_PAGE);
		}

		if ($round == true) {
			$value = intval($value / 1000) * 1000;
		}

		if ($format == true) {
			$value = number_format($value, 0, '.', ' ');
		}

		return $value;
	}

	/**
	 * Возвращает количество фотографий опубликованных Интерьеров.
	 *
	 * @return int
	 */
	public static function getQntPhotosInterior()
	{
		Yii::import('application.modules.idea.models.Interior');

		// ФОтки помещений
		$interiorCount = Interior::model()->countBySql("
			SELECT COUNT(*)
			FROM idea_uploaded_file iuf
			LEFT JOIN interior
				ON interior.id = iuf.item_id
			WHERE
				(interior.status = :st1 OR interior.status = :st2)
				AND
				iuf.idea_type_id = 1
		", array(':st1'=>Interior::STATUS_ACCEPTED, ':st2'=>Interior::STATUS_CHANGED));

		// Обложки
		$interiorCover = Interior::model()->count(
			'(t.status = :st1 OR t.status = :st2) AND image_id IS NOT NULL',
			array(':st1'=>Interior::STATUS_ACCEPTED, ':st2'=>Interior::STATUS_CHANGED)
		);

		return (int)$interiorCount + (int)$interiorCover;
	}

	/**
	 * Возвращает количество фотографий опубликованных Общественных интерьеров
	 *
	 * @return int
	 */
	public static function getQntPhotosInteriorpublic()
	{
		Yii::import('application.modules.idea.models.Interiorpublic');

		$interiorpublicCount = Interiorpublic::model()->countBySql("
			SELECT COUNT(*)
			FROM idea_uploaded_file iuf
			LEFT JOIN interiorpublic ip
				ON ip.id = iuf.item_id
			WHERE
				(ip.status = :st1 OR ip.status = :st2)
				AND
				iuf.idea_type_id = 2
		", array(':st1'=>Interiorpublic::STATUS_ACCEPTED, ':st2'=>Interiorpublic::STATUS_CHANGED));

		return (int)$interiorpublicCount;
	}

	/**
	 * Возвращает количество фотографий опубликованных Архитекутр
	 *
	 * @return int
	 */
	public static function getQntPhotosArchitecture()
	{
		Yii::import('application.modules.idea.models.Architecture');

		$architectureCount = Architecture::model()->countBySql("
			SELECT COUNT(*)
			FROM idea_uploaded_file iuf
			LEFT JOIN architecture a
				ON a.id = iuf.item_id
			WHERE
				(a.status = :st1 OR a.status = :st2)
				AND
				iuf.idea_type_id = 3
		", array(':st1' => Architecture::STATUS_ACCEPTED, ':st2' => Architecture::STATUS_CHANGED));

		return (int)$architectureCount;
	}

	/**
	 * Возвращает некоторое кол-во последних опубликованных идей
	 * @param int $limit ограничение на выборку
	 * @return array
	 */
	static public function getLatestIdeas($limit = 100)
	{
		$st = Interior::STATUS_ACCEPTED;


		$result = Yii::app()->db->createCommand("
                        (SELECT id, service_id, create_time, 'interior' as tname
                                FROM interior WHERE status = {$st}
                                ORDER BY create_time DESC LIMIT {$limit})
                        UNION
			(SELECT id, service_id, create_time, 'architecture' as tname
				FROM architecture
				WHERE status = {$st}
				ORDER BY create_time DESC LIMIT {$limit})
                        UNION
                	(SELECT id, service_id, create_time, 'interiorpublic' as tname
                                FROM interiorpublic
                                WHERE status = {$st}
                                ORDER BY create_time DESC LIMIT {$limit})
                        ORDER BY create_time DESC LIMIT {$limit};
                ")->queryAll();

		$projects = array();
		foreach($result as $item) {
			$tmp = null;
			if ($item['service_id'] == Interior::SERVICE_ID)
			{
				if ($item['tname'] == 'interior')
					$tmp = Interior::model()->findByPk($item['id']);
				elseif ($item['tname'] == 'interiorpublic')
					$tmp = Interiorpublic::model()->findByPk($item['id']);
			}
			elseif ($item['service_id'] == Architecture::SERVICE_ID)
			{
				$tmp = Architecture::model()->findByPk($item['id']);
			}
			else
			{
				$tmp = Portfolio::model()->findByPk($item['id']);
			}

			if ($tmp) {
				$projects[] = $tmp;
			}
		}
		return $projects;
	}


	/**
	 * Метод возвращает случайные
	 * id идей
	 * @param int   $size
	 * @param array $type
	 *
	 * @return mixed
	 */
	static public function getRandomIdeas($size=6, $type = array())
	{
		$randomIds = array();

		$sql = 'SELECT idea_id FROM {{idea}} WHERE `status` = 3 AND idea_type=1 ORDER BY RAND() LIMIT '.$size;
		$randomIds = Yii::app()->sphinx->createCommand($sql)->queryColumn();

		return $randomIds;
	}
}
