<?php

/**
 * This is the model class for table "operationlog".
 *
 * The followings are the available columns in table 'operationlog':
 * @property integer $id
 * @property integer $objecttype
 * @property integer $operationtype
 * @property string $description
 * @property integer $create_time
 */
class Operationlog extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Operationlog the static model class
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
		return 'operationlog';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('objecttype, operationtype, create_time', 'required'),
			array('objecttype, operationtype, create_time', 'numerical', 'integerOnly'=>true),
			array('description', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, objecttype, operationtype, description, create_time', 'safe', 'on'=>'search'),
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
			'objecttype' => 'Objecttype',
			'operationtype' => 'Operationtype',
			'description' => 'Description',
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
		$criteria->compare('objecttype',$this->objecttype);
		$criteria->compare('operationtype',$this->operationtype);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}