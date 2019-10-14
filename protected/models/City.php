<?php

/**
 * This is the model class for table "city".
 *
 * The followings are the available columns in table 'city':
 * @property integer $id
 * @property float $lat
 * @property float $lng
 * @property string $country_id
 * @property string $region_id
 * @property string $name
 * @property string $genitiveCase
 * @property string $prepositionalCase
 * @property string $eng_name
 * @property integer rating
 *
 * The followings are the available model relations:
 * @property Country $country
 * @property Region $region
 * @property User[] $users
 */
class City extends EActiveRecord
{
        const ID_MOSCOW = 4400;
	const ID_NSK = 4549;

	/**
	 * Returns the static model of the specified AR class.
	 * @return City the static model class
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
		return 'city';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('country_id, region_id', 'length', 'max'=>11),
			array('name', 'length', 'max'=>128),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, country_id, region_id, name', 'safe', 'on'=>'search'),
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
			'country' => array(self::BELONGS_TO, 'Country', 'country_id'),
			'region' => array(self::BELONGS_TO, 'Region', 'region_id'),
			'users' => array(self::HAS_MANY, 'User', 'city_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'country_id' => 'Country',
			'region_id' => 'Region',
			'name' => 'Name',
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
		$criteria->compare('country_id',$this->country_id,true);
		$criteria->compare('region_id',$this->region_id,true);
		$criteria->compare('name',$this->name,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public static function getNameById($cityId)
	{
		if (empty($cityId))
			return '';
		$city = self::model()->findByPk( intval($cityId) );
		return is_null($city) ? '' : $city->name;
	}
}