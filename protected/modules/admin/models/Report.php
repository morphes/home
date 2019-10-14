<?php

/**
 * This is the model class for table "report".
 *
 * The followings are the available columns in table 'report':
 * @property integer $id
 * @property integer $user_id
 * @property integer $status
 * @property integer $type_id
 * @property string $file
 * @property string $progress
 * @property string $data
 * @property integer $create_time
 * @property integer $update_time
 */
class Report extends EActiveRecord
{
	// Состояния отчетов
	const STATUS_NEW = 1;
	const STATUS_PROGRESS = 2;
	const STATUS_SUCCESS = 3;
	const STATUS_ERROR = 4;
	public static $statusNames = array(
		self::STATUS_NEW => 'Новый',
		self::STATUS_PROGRESS => 'В обработке',
		self::STATUS_SUCCESS => 'Готов',
		self::STATUS_ERROR => 'Ошибка',
	);

	// Типы отчетов
	const TYPE_CONSOLIDATE = 1;
	const TYPE_CITY = 2;
	const TYPE_STORE = 3;
	const TYPE_VENDOR = 4;
	const TYPE_CONTRACTOR = 5;
	const TYPE_SPECIALIST = 6;
	const TYPE_STORE_VIEW = 7;
	public static $typeNames = array(
		self::TYPE_CONSOLIDATE => 'Сводный отчет',
		self::TYPE_CITY        => 'Отчет по городам',
		self::TYPE_STORE       => 'Отчет по магазинам',
		self::TYPE_VENDOR      => 'Сводный отчет по производителям',
		self::TYPE_CONTRACTOR  => 'Сводный отчет по контрагентам',
		self::TYPE_SPECIALIST  => 'Отчет по специалистам',
		self::TYPE_STORE_VIEW  => 'По просмотрам магазинов',
	);

	const CITY_PRIORITY = 1;
	const CITY_NOT_EMPTY = 2;
	const CITY_HAND = 3;

	public static $citiesSelect = array(
		self::CITY_PRIORITY  => 'Приоритетные',
		self::CITY_NOT_EMPTY => 'Все ненулевые',
		self::CITY_HAND      => 'Вручную',
	);

	const CRITERIA_MAXIMUM = 1;
	const CRITERIA_OPTIMUM = 2;
	const CRITERIA_FOTO = 3;
	const CRITERIA_PORTFOLIO = 4;
	const CRITERIA_ALL = 5;
	public static $criteriaNames = array(
		self::CRITERIA_MAXIMUM   => 'Максимум',
		self::CRITERIA_OPTIMUM   => 'Оптимум',
		self::CRITERIA_FOTO      => 'С фото',
		self::CRITERIA_PORTFOLIO => 'С портфолио',
		self::CRITERIA_ALL       => 'Всего',
	);

	const SERVICE_PRIORITY = 2;
	const SERVICE_ALL = 3;
	const SERVICE_HAND = 4;
	public static $serviceNames = array(
		self::SERVICE_PRIORITY => 'Приоритетные',
		self::SERVICE_ALL      => 'Все',
		self::SERVICE_HAND     => 'Вручную',
	);


	public $start_time;
	public $end_time;

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}

	/**
	 * Update create_time in object
	 */
	public function setDate()
	{
		if($this->isNewRecord)
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Report2 the static model class
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
		return 'report';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, status, type_id, create_time, update_time', 'required'),
			array('user_id, status, type_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('file, progress', 'length', 'max'=>512),
			array('data', 'safe'),
			array('start_time, end_time', 'integer'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, status, type_id, file, progress, data, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'user_id'     => 'User',
			'status'      => 'Status',
			'type_id'     => 'Type',
			'file'        => 'File',
			'progress'    => 'Progress',
			'data'        => 'Data',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
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
		$criteria->compare('type_id',$this->type_id);
		$criteria->compare('file',$this->file,true);
		$criteria->compare('progress',$this->progress,true);
		$criteria->compare('data',$this->data,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function getUserName()
	{
		if (is_null($this->user_id))
			return '';
		$user = User::model()->findByPk($this->user_id);
		return is_null($user) ? '' : $user->name;
	}
}