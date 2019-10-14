<?php

/**
 * This is the model class for table "specialist_rate".
 *
 * The followings are the available columns in table 'specialist_rate':
 * @property integer $id
 * @property string $name
 * @property integer $packet_3
 * @property integer $discount_3
 * @property integer $packet_7
 * @property integer $discount_7
 * @property integer $packet_14
 * @property integer $discount_14
 */
class SpecialistRate extends CActiveRecord
{
	//Пакеты на 3 7 14 дней
	const PACKET_3 = 3;

	const PACKET_7 = 7;

	const PACKET_14 = 14;
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SpecialistRate the static model class
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
		return 'specialist_rate';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('packet_3, discount_3, packet_7, discount_7, packet_14, discount_14', 'numerical', 'integerOnly'=>true),
			array('packet_3, packet_7, packet_14', 'required'),
			array('name', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, packet_3, discount_3, packet_7, discount_7, packet_14, discount_14', 'safe', 'on'=>'search'),
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
			'name' => 'Название',
			'packet_3' => 'Пакет 3',
			'discount_3' => 'Скидка пакет 3',
			'packet_7' => 'Пакет 7',
			'discount_7' => 'Скидка пакет 7',
			'packet_14' => 'Пакет 14',
			'discount_14' => 'Скидка пакет 14',
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
		$criteria->compare('packet_3',$this->packet_3);
		$criteria->compare('discount_3',$this->discount_3);
		$criteria->compare('packet_7',$this->packet_7);
		$criteria->compare('discount_7',$this->discount_7);
		$criteria->compare('packet_14',$this->packet_14);
		$criteria->compare('discount_14',$this->discount_14);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}