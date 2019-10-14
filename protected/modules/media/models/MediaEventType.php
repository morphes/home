<?php

/**
 * This is the model class for table "media_event_type".
 *
 * The followings are the available columns in table 'media_event_type':
 * @property integer $id
 * @property integer $status
 * @property string $name
 * @property integer $create_time
 * @property integer $update_time
 */
class MediaEventType extends EActiveRecord
{
	// --- СТАТУСЫ ---
	const STATUS_ACTIVE 	= 1; // Опубикован
	const STATUS_DELETED 	= 2; // Удален

	public static $statusNames = array(
		self::STATUS_ACTIVE => 'Опубликован',
		self::STATUS_DELETED => 'Удален',
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
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MediaEventType the static model class
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
		return 'media_event_type';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, name, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'status' => 'Status',
			'name' => 'Название',
			'create_time' => 'Добавлено',
			'update_time' => 'Обновлено',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('status', self::STATUS_ACTIVE);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		$sort = new CSort();
		$sort->defaultOrder = array('id' => CSort::SORT_DESC);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>$sort,
		));
	}
}