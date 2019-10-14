<?php

/**
 * This is the model class for table "moderator_log".
 *
 * The followings are the available columns in table 'moderator_log':
 * @property integer $id
 * @property integer $user_id
 * @property integer $class_id
 * @property integer $record_id
 * @property integer $crud_id
 * @property integer $create_time
 */
class ModeratorLog extends EActiveRecord
{
	// classes
	const CLASS_INTERIOR = 1;
	public static $classNames = array(
	    self::CLASS_INTERIOR => 'Интерьеры',
	);
	
	public static $classId = array(
	    'Interior' => self::CLASS_INTERIOR,
	);

	// operations
	const OPERATION_CREATE = 1;
	const OPERATION_MODERATE = 2;
	const OPERATION_DELETE = 3;
	public static $operationNames = array(
	    self::OPERATION_CREATE => 'Создание',
	    self::OPERATION_MODERATE => 'Модерация',
	    self::OPERATION_DELETE => 'Удаление',
	);
	
	public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
        }

        /**
         * Update create_time and update_time in object
         */
        public function setDate()
        {
                if ($this->isNewRecord)
                        $this->create_time = time();
        }

	/**
	 * Returns the static model of the specified AR class.
	 * @return ModeratorLog the static model class
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
		return 'moderator_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, class_id, record_id, crud_id', 'required'),
			array('user_id, class_id, record_id, crud_id, create_time', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, class_id, record_id, crud_id, create_time', 'safe', 'on'=>'search'),
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
			'user_id' => 'Модератор',
			'class_id' => 'Раздел',
			'record_id' => '№ записи',
			'crud_id' => 'Операция',
			'create_time' => 'Время операции',
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
		$criteria->compare('class_id',$this->class_id);
		$criteria->compare('record_id',$this->record_id);
		$criteria->compare('crud_id',$this->crud_id);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public static function operationCreate($object)
	{
		return self::addOperation($object, self::OPERATION_CREATE);
	}
	
	public static function operationModerate($object)
	{
		return self::addOperation($object, self::OPERATION_MODERATE);
	}
	
	public static function operationDelete($object)
	{
		return self::addOperation($object, self::OPERATION_DELETE);
	}
	
	private static function addOperation($object, $crudId)
	{
		if (!is_object($object))
			return;
		
		$log = new self();
		$log->user_id = Yii::app()->user->id;
		if (!empty(self::$classId[get_class($object)]))
			$log->class_id = self::$classId[get_class($object)];
		else 
			return;

		$log->crud_id = $crudId;
		$log->record_id = $object->id;
		$log->save();
	}
	
	public function getUserName()
	{
		$user = User::model()->findByPk($this->user_id);
		if (is_null($user))
			return '';
		return $user->login;
	}
	
	public function getItemUrl()
	{
		switch ($this->class_id) {
			case self::CLASS_INTERIOR:
				return CHtml::link($this->record_id, Yii::app()->controller->createUrl('/idea/admin/interior/view/', array('interior_id' => $this->record_id)) );
				break;

			default:
				return $this->record_id;
		}
	}
}