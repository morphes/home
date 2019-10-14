<?php

/**
 * This is the model class for table "shared".
 *
 * The followings are the available columns in table 'shared':
 * @property integer $id
 * @property integer $type
 * @property integer $object_id
 * @property integer $user_id
 * @property integer $create_time
 */
class Shared extends CActiveRecord
{

	const TYPE_FAVORITE = 1;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Shared the static model class
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
		return 'shared';
	}

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
		$this->onAftervalidate = array($this, 'checkUniqueShare');
	}

	public function setDate()
	{
		if ($this->isNewRecord)
			$this->create_time = time();
	}


	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, object_id, user_id', 'required'),
			array('type, object_id, user_id, create_time', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, object_id, user_id, create_time', 'safe', 'on'=>'search'),
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
			'type' => 'Type',
			'data' => 'Data',
			'object_id' => 'Object',
			'user_id' => 'User',
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
		$criteria->compare('type',$this->type);
		$criteria->compare('data',$this->data,true);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Валидация шары на уникальность в таблице
	 * @return bool
	 */
	public function checkUniqueShare()
	{
		if ( $this->getErrors() )
			return false;

		$nonUnique = self::model()->exists(
			'type=:t and object_id=:oid and user_id=:uid',
			array(':t'=>$this->type, ':oid'=>$this->object_id, ':uid'=>$this->user_id)
		);

		if ($nonUnique)
			$this->addError('object_id', 'Объект уже расшарен');

		return false;
	}


	/**
	 * Возвращает хеш текущего объекта
	 * @return string
	 */
	public function getHash()
	{
		return base_convert($this->id * 100, 10, 36);
	}

	/**
	 * Возвращает объект Shared по его хешу
	 * @param $hash
	 *
	 * @return CActiveRecord
	 */
	static public function getSharedId($hash)
	{
		$id = intval($hash,36) / 100;
		return self::model()->findByPk($id);
	}

	/**
	 * Создание шары любого типа
	 * @param $type - тип того, что расшарили (см. константы TYPE_ выше)
	 * @param $object_id - id того, что расшарили
	 * @param $user_id - кто расшарил
	 *
	 * @return Shared
	 */
	static public function createShared($type, $object_id, $user_id = null)
	{
		if ( !$user_id )
			$user_id = Yii::app()->user->id;

		$model = new self();
		$model->type = $type;
		$model->user_id = $user_id;
		$model->object_id = $object_id;
		$model->save();
		return $model;
	}


	/**
	 * Возвращает объект шары по его параметрам
	 * @param $type
	 * @param $object_id
	 * @param $user_id
	 *
	 * @return CActiveRecord
	 */
	static public function findShared($type, $object_id, $user_id)
	{
		return self::model()->findByAttributes(
			array(
				'type'=>$type,
				'object_id'=>$object_id,
				'user_id'=>$user_id,
			)
		);
	}


	/**
	 * Возвращает URL текущей шары
	 * @return null
	 */
	public function getUrl()
	{
		if ( $this->type == self::TYPE_FAVORITE )
			return Yii::app()->createAbsoluteUrl('/favorite/shared/' . $this->getHash());

		return null;
	}
}
