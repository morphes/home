<?php

/**
 * This is the model class for table "cat_csv".
 *
 * The followings are the available columns in table 'cat_csv':
 * @property integer $id
 * @property integer $user_id
 * @property integer $status
 * @property string $action
 * @property string $type
 * @property integer $item_id
 * @property string $file
 * @property string $progress
 * @property string $data
 * @property integer $create_time
 * @property integer $update_time
 */
class CatCsv extends EActiveRecord
{
	// Тип задания "Экспортирование товаров для нескольких производителей"
	const TYPE_FOR_VENDORS = 'ForVendors';
	const TYPE_STORE = 'Store';
	const TYPE_CONTRACTOR = 'Contractor';

	const STATUS_NEW = 1;
	const STATUS_IN_PROGRESS = 2;
	const STATUS_FINISHED = 3;
	const STATUS_FAILED = 4;

	static $statuses = array(
		self::STATUS_NEW         => 'Новый',
		self::STATUS_IN_PROGRESS => 'В процессе',
		self::STATUS_FINISHED    => 'Завершенный',
		self::STATUS_FAILED      => 'Ошибка',
	);

	public function init()
	{
		$this->onBeforeSave = array($this, 'setDate');

		return parent::init();
	}

	public function setDate()
	{
		if ($this->isNewRecord)
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CatCsv the static model class
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
		return 'cat_csv';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, status, item_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('action', 'length', 'max'=>6),
			array('type', 'length', 'max'=>25),
			array('file', 'length', 'max'=>400),
			array('progress, data', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, status, action, type, item_id, file, progress, data, create_time, update_time', 'safe', 'on'=>'search'),
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
			'author' => array(self::BELONGS_TO, 'User', 'user_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'user_id'     => 'Инициатор операции',
			'status'      => 'Статус',
			'action'      => 'Действие',
			'type'        => 'Тип операции',
			'item_id'     => 'ID операции',
			'file'        => 'Файл',
			'progress'    => 'Прогресс',
			'data'        => 'Данные',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('status',$this->status);
		$criteria->compare('action',$this->action,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('item_id',$this->item_id);
		$criteria->compare('file',$this->file,true);
		$criteria->compare('progress',$this->progress,true);
		$criteria->compare('data',$this->data,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Возвращает html код для статуса с расцветкой по разным состояниям
	 * @return string HTML код со статусом
	 */
	public function getStatusColor()
	{
		switch($this->status) {
			case self::STATUS_NEW: $cls = 'default'; break;
			case self::STATUS_IN_PROGRESS: $cls = 'notice'; break;
			case self::STATUS_FINISHED: $cls = 'success'; break;
			case self::STATUS_FAILED: $cls = 'important'; break;
			default:
				$cls = '';
		}
		return '<span class="label '.$cls.'">'.self::$statuses[$this->status].'</span>';
	}

	/**
	 * Получает прогресс в процентах
	 *
	 * @return int Процент прогресса
	 */
	public function getProgressPercent()
	{
		/** @var $data array */
		$data = unserialize($this->progress);

		if ($data['totalItems'] == 0) {
			$percent = 0;
		} else {
			// Прогресс в процентах
			$percent = round($data['doneItems'] * 100 / $data['totalItems']);
		}

		return $percent;
	}

	/**
	 * Возвращает список производителей, хранящийся в поле данных задания.
	 * @return array
	 */
	public function getVendors()
	{
		$data = unserialize($this->data);

		if (isset($data['vendor_ids'])) {
			$vendor_ids = $data['vendor_ids'];

			$models = Vendor::model()->findAllByAttributes(array('id' => $vendor_ids));
		} else {
			$models = null;
		}


		return $models;
	}

	/**
	 * Возвращает кол-во секунд, потраченного на обработку.
	 *
	 * @return int
	 */
	public function getWorkTime()
	{
		$diff = $this->update_time - $this->create_time;
		$time = new DateTime('@'.$diff);
		$time->format('H:i:s');

		return $time->format('H:i:s');
	}

	/**
	 * Возвращает Магазин, хранящийся в поле данных задания.
	 * @return CActiveRecord|null
	 */
	public function getStore()
	{
		$data = unserialize($this->data);

		if (isset($data['store_id'])) {
			$model = Store::model()->findByPk((int)$data['store_id']);
		} else {
			$model = null;
		}

		return $model;
	}

	/**
	 * Проверяет переданный массив полей первой строки из CSV файла на валидность.
	 *
	 * @param array $head Массив полей из CSV файла
	 * @param $type Тип CSV файла для проверки. Каждому типу соответсвует свой набор полей.
	 * @return bool True в случае прохождения теста, false — иначе
	 */
	static public function checkImportCsvHead($head = array(), $type)
	{
		/** @var $result boolean */
		$result = false;

		$head = array_map(function($n){ return iconv('cp1251', 'UTF-8', $n); }, $head);
		
		switch ($type) {
			case CatCsv::TYPE_STORE:
				if (	   isset($head[0]) && mb_strtolower($head[0], 'UTF-8') == 'pid'
					&& isset($head[1]) && mb_strtolower($head[1], 'UTF-8') == 'артикул'
					&& isset($head[2]) && mb_strtolower($head[2], 'UTF-8') == 'производитель'
					&& isset($head[3]) && mb_strtolower($head[3], 'UTF-8') == 'название'
					&& isset($head[4]) && mb_strtolower($head[4], 'UTF-8') == 'категория'
					&& isset($head[5]) && mb_strtolower($head[5], 'UTF-8') == 'цена'
					&& isset($head[6]) && mb_strtolower($head[6], 'UTF-8') == 'url'
				)
					$result = true;
				break;
		}
		
			
		return $result;
	}
}