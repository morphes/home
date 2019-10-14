<?php

/**
 * This is the model class for table "mailer".
 *
 * The followings are the available columns in table 'mailer':
 * @property integer $id
 * @property integer $status
 * @property string $from
 * @property string $subject
 * @property integer $create_time
 * @property integer $update_time
 * @property string $data
 */
class Mailer extends EActiveRecord
{
	const STATUS_NEW = 0;
	const STATUS_TO_SEND = 1;
	const STATUS_SENDED = 2;
	public static $statusNames = array(
		self::STATUS_NEW => 'Новaя рассылка',
		self::STATUS_TO_SEND => 'На отправку',
		self::STATUS_SENDED => 'Рассылка выполнена',
	);

	const UPLOAD_IMAGE_DIR = 'uploads/public/mailer';
	
	
	private static $_groups = null;
	
	
	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
		$this->onAfterSave = array($this, 'sendMailer');
	}

	/**
	 * Send delivery
	 */
	public function sendMailer()
	{
		if ($this->status == self::STATUS_TO_SEND) {
			Yii::app()->gearman->appendJob('mail:send_mailer', $this->id);
		}
	}
	
	/**
	 * Update create_time and update_time in object
	 */
	public function setDate()
	{
		if($this->isNewRecord)
			$this->create_time=$this->update_time=time();
		else
			$this->update_time=time();
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return Mailer the static model class
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
		return 'mailer';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('status, create_time, update_time, group_id, user_status', 'numerical', 'integerOnly'=>true),
			array('from, subject, author', 'length', 'max'=>255),
			array('role', 'length', 'max'=>64),
			array('data', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, from, subject, create_time, author, update_time, data, user_status', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'status' => 'Статус',
			'from' => 'Email отправителя',
			'subject' => 'Тема письма',
			'author' => 'Автор',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
			'data' => 'Текст письма',
			'user_status' => 'Статус пользователя',
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
		$criteria->compare('status',$this->status);
		$criteria->compare('from',$this->from,true);
		$criteria->compare('subject',$this->subject,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);
		$criteria->compare('data',$this->data,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * @return array of user groups
	 */
	public function getGroups()
	{
		if (is_null(self::$_groups)) {
			$groupList = Usergroup::model()->findAll();
			self::$_groups = array(0=>'Все')+CHtml::listData($groupList, 'id', 'name'); 
		}
		return self::$_groups;
	}
	
	public function getUserStatus()
	{
		$data = Config::$userStatus;
		$data[0] = 'Все';
		return $data;
	}
}