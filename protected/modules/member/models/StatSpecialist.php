<?php

/**
 * This is the model class for table "stat_specialist".
 *
 * The followings are the available columns in table 'stat_specialist':
 * @property integer $id
 * @property integer $specialist_id
 * @property integer $view
 * @property integer $type
 * @property integer $time
 */
class StatSpecialist extends CActiveRecord
{
	/**
	 * Просмотр профиля
	 */
	const TYPE_HIT_PROFILE = 1;

	/**
	 * Просмотр контактов
	 */
	const TYPE_HIT_CONTACTS = 2;

	/**
	 * Показов профиля в списке
	 * специалистов`
	 */
	const TYPE_SHOW_PROFILE_IN_LIST = 3;

	/**
	 * Кликов по профилю
	 * в списках специалистов
	 */
	const TYPE_CLICK_PROFILE_IN_LIST = 4;

	/**
	 * Кликов на кнопку связаться со мной
	 */
	const TYPE_CLICK_CONTACT_ME = 5;

	/**
	 * Отправил сообщение специалисту
	 */
	const TYPE_SEND_MESSAGE_TO_SPECIALIST =6;


	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return StatSpecialist the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'stat_specialist';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('specialist_id, view, type, time', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, specialist_id, view, type, time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	public static $typeLabels = array(
		self::TYPE_HIT_PROFILE       	 => 'Просмотров профиля',
		self::TYPE_HIT_CONTACTS      	 => 'Просмотр контактов',
		self::TYPE_SHOW_PROFILE_IN_LIST  => 'Показ профиля в списках',
		self::TYPE_CLICK_PROFILE_IN_LIST => 'Клик по профилю в списках',
		self::TYPE_CLICK_CONTACT_ME	 => 'Клик связаться со мной',
		self::TYPE_SEND_MESSAGE_TO_SPECIALIST => 'Отправлено сообщений специалисту',
	);

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'            => 'ID',
			'specialist_id' => 'Specialist',
			'view'          => 'View',
			'type'          => 'Type',
			'time'          => 'Time',
		);
	}


	/**
	 * Увеличивает количество просмотров на 1 для специалиста $specialist_id
	 * по типу $type
	 *
	 * @param $store_id Идентификатор магазина
	 * @param $type     Тип накапливаемой статистики
	 */
	static public function hit($specialist_id, $type)
	{
		Yii::app()->redis->incr(self::getRedisKeyStore($specialist_id, $type));
	}


	/**
	 * Возвращает ключ для Redis'а
	 *
	 * @param $specialist_id Идентификатор специалиста
	 * @param $type          Тип накапливаемой статистики
	 *
	 * @return string Строка-ключ для Redis
	 */
	static public function getRedisKeyStore($specialist_id, $type)
	{
		$time = date('d.m.Y');

		return 'STAT:SPECIALIST:' . $specialist_id . ':TYPE:' . $type . ':' . $time;
	}


	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('specialist_id', $this->specialist_id);
		$criteria->compare('view', $this->view);
		$criteria->compare('type', $this->type);
		$criteria->compare('time', $this->time);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}


	/**
	 * Метод переносит данные из Redis
	 * В mysql
	 * Паттерн для поиска ключе задается в переменной $pattern
	 *
	 * @param $pattern
	 */
	static public function updateStatSpecialistMySql($pattern)
	{
		$keys = Yii::app()->redis->keys($pattern);
		$sql = 'INSERT INTO stat_specialist (`specialist_id`, `type`, `view`, `time`) VALUES (:specialist_id, :type, :view , :time)';
		$sqlSelect = 'SELECT * FROM stat_specialist WHERE time = :time AND specialist_id = :specialist_id AND type = :type';
		$sqlUpdate = 'UPDATE stat_specialist SET `view`= `view` + :view WHERE id = :id';
		$sqlValues = '';


		// Ключи вида STAT:SPECIALIST:3024:TYPE:3:19.04.2013
		$transaction = Yii::app()->db
			->beginTransaction();
		try {
			foreach ($keys as $key) {
				preg_match('#^STAT:SPECIALIST:([\d]+):TYPE:([\d]+):([\d]{1,2}\.[\d]{1,2}\.[\d]{4})#', $key, $matches);

				$specialist_id = (int)$matches[1];
				$type = (int)$matches[2];
				$time = strtotime($matches[3]);
				$view = Yii::app()->redis->get($key);

				$selectResult = Yii::app()->db->createCommand($sqlSelect)
					->bindParam(':time', $time)
					->bindParam(':specialist_id', $specialist_id)
					->bindParam(':type', $type)
					->queryRow();
				if (!$selectResult) {
					Yii::app()->db->createCommand($sql)
						->bindParam(':specialist_id', $specialist_id)
						->bindParam(':type', $type)
						->bindParam(':view', $view)
						->bindParam(':time', $time)
						->execute();
				} else {
					$id = (int)$selectResult['id'];
					Yii::app()->db->createCommand($sqlUpdate)
						->bindParam(':id', $id)
						->bindParam(':view', $view)
						->execute();
				}
			}

			$transaction->commit();

			//Удаляем ключи из редиса
			foreach ($keys as $key) {
				Yii::app()->redis->delete($key);
			}
		} catch (Exception $e) {
			$transaction->rollBack();
		}
	}


	/**
	 * Вернуть статистику за период
	 * @param     $specialistId
	 * @param int $dateFrom
	 * @param int $dateTo
	 *
	 * @return array
	 */
	public function getStat($specialistId, $dateFrom = 0, $dateTo = 0)
	{
		$criteria = new CDbCriteria(array(
			'select'    => 'specialist_id, time, type, SUM(view) as view',
			'condition' => 'specialist_id = :sid',
			'group'     => 'type',
			'params'    => array(':sid' => $specialistId)
		));

		if ($dateFrom > 0) {
			$criteria->compare('time', '>=' . $dateFrom);
		}


		if ($dateTo > 0) {
			$criteria->compare('time', '<=' . $dateTo);
		}

		// Формируем из Критерии обычный DAO запрос
		$builder = new CDbCommandBuilder(Yii::app()->db->getSchema());
		$command = $builder->createFindCommand('stat_specialist', $criteria);
		$stat = $command->queryAll();

		$result = array(
			self::TYPE_HIT_PROFILE           => 0,
			self::TYPE_HIT_CONTACTS          => 0,
			self::TYPE_SHOW_PROFILE_IN_LIST  => 0,
			self::TYPE_CLICK_PROFILE_IN_LIST => 0,
			self::TYPE_CLICK_CONTACT_ME      => 0,
			self::TYPE_SEND_MESSAGE_TO_SPECIALIST =>0,
		);

		if ($stat) {
			foreach ($stat as $s) {
				$result[$s['type']] = $s['view'];
			}
		}

		return $result;
	}
}