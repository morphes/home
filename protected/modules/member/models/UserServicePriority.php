<?php

/**
 * This is the model class for table "user_service_priority".
 *
 * The followings are the available columns in table 'user_service_priority':
 * @property integer $user_id
 * @property integer $service_id
 * @property integer $city_id
 * @property integer $date_start
 * @property integer $date_end
 * @property integer $status
 */
class UserServicePriority extends CActiveRecord
{
	const STATUS_PAY_WAIT = 1;
	const STATUS_PAY_SUCCESS = 2;
	const STATUS_PAY_ERROR = 3;

	public static $statuses = array(
		self::STATUS_PAY_WAIT => 'Ожидание оплаты',
		self::STATUS_PAY_SUCCESS => 'Оплачено',
		self::STATUS_PAY_ERROR => 'Отказ',
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserServicePriority the static model class
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
		return 'user_service_priority';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, service_id, city_id, date_start, date_end, status', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, service_id, city_id, date_start, date_end, status', 'safe', 'on'=>'search'),
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

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'user_id' => 'Пользователь',
			'service_id' => 'Услуга',
			'city_id' => 'Город',
			'date_start' => 'Дата начала',
			'date_end' => 'Дата конца',
			'status' => 'статус',

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

		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('service_id',$this->service_id);
		$criteria->compare('city_id',$this->city_id);
		$criteria->compare('date_start',$this->date_start);
		$criteria->compare('date_end',$this->date_end);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}