<?php

/**
 * This is the model class for table "promocode".
 *
 * The followings are the available columns in table 'promocode':
 * @property integer $id
 * @property string $name
 * @property integer $status
 * @property string $desc
 *
 * The followings are the available model relations:
 * @property User[] $users
 */
class Promocode extends EActiveRecord
{
	const STATUS_NOT_ACTIVE	= 1; // Не автивен
	const STATUS_ACTIVE	= 2; // Активен
	
	// Статусы промокодов
        public static $promocodeStatus = array(
		self::STATUS_NOT_ACTIVE => 'Не активен',
		self::STATUS_ACTIVE => 'Активен',
        );
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return Promocode the static model class
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
		return 'promocode';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>45),
			array('desc', 'length', 'max'=>1000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, status, desc', 'safe', 'on'=>'search'),
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
			'users' => array(self::HAS_MANY, 'User', 'promocode_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'	=> 'ID',
			'name'	=> 'Название',
			'status'=> 'Статус',
			'desc'	=> 'Описание',
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('desc',$this->desc,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	/**
	 * Возвращает Имя промокода по его ID.
	 * 
	 * @param integer $id ID промокода
	 * @return string Имя промокода или  пустая строка
	 */
	public static function getNameById($id = null)
	{
		$model = Promocode::model()->findByPk($id);
		if ($model) {
			return $model->name;
		} else {
			return '';
		}
	}
}