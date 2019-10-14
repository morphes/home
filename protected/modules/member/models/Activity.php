<?php

/**
 * This is the model class for table "activity".
 *
 * The followings are the available columns in table 'activity':
 * @property integer $id
 * @property integer $user_id
 * @property integer $model_id
 * @property string $model
 * @property integer $create_time
 */
class Activity extends EActiveRecord
{
	/**
	 * @var CActiveRecord объект модели, связанной с активностью
	 */
	private $_object = null;


	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}

	public function setDate()
	{
		if ($this->getIsNewRecord() && empty($this->create_time))
			$this->create_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Activity the static model class
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
		return 'activity';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, model_id', 'required'),
			array('user_id, model_id', 'numerical', 'integerOnly'=>true),
			array('model', 'length', 'max'=>32),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, model_id, model, create_time', 'safe', 'on'=>'search'),
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
			'user_id' => 'User',
			'model_id' => 'Model',
			'model' => 'Model',
			'create_time' => 'Create Time',
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
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function getObject()
	{
		if (is_null($this->_object)) {
			$className = $this->model;
			$this->_object = $className::model()->findByPk($this->model_id);
		}
		return $this->_object;
	}

	/**
	 * Создание объекта активности
	 * @static
	 * @param $object
	 * @return Activity
	 * @throws CException
	 */
	public static function createActivity($object)
	{
		if (! $object instanceof IActivity)
			throw new CException('Invalid object');
		$activity = self::model()->findByAttributes(array('user_id'=>$object->getAuthorId(), 'model'=>get_class($object), 'model_id'=>$object->id));
		if (!is_null($activity))
			return false;

		$activity = new Activity();
		$activity->user_id = $object->getAuthorId();
		$activity->model = get_class($object);
		$activity->model_id = $object->id;
		$activity->create_time = $object->create_time;
		$activity->save();
		return $activity;
	}


	public static function deleteActivity($object)
	{
		if (! $object instanceof IActivity)
			throw new CException('Invalid object');

		$activity = self::model()->findByAttributes(array('user_id'=>$object->getAuthorId(), 'model'=>get_class($object), 'model_id'=>$object->id));
		if (is_null($activity))
			return false;

		return $activity->delete();
	}
}