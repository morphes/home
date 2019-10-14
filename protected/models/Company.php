<?php

/**
 * This is the model class for table "company".
 *
 * The followings are the available columns in table 'company':
 * @property string $id
 * @property string $name
 *
 * The followings are the available model relations:
 * @property City[] $cities
 * @property Region[] $regions
 */
class Company extends EActiveRecord
{

	/**
	 * Returns the static model of the specified AR class.
	 * @return Country the static model class
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
		return 'company';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			// array('name, eng_name', 'length', 'max'=>128),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			// array('id, name', 'safe', 'on'=>'search'),
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
			// 'cities' => array(self::HAS_MANY, 'City', 'country_id'),
			// 'regions' => array(self::HAS_MANY, 'Region', 'country_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			// 'id' => 'ID',
			// 'name' => 'Название',
			// 'eng_name' => 'Английское название',
		);
	}

	/**
	 * Получение названия страны по id
	 * @static
	 * @param $countryId
	 * @return string
	 */
	public static function getNameById($countryId)
	{
		if (!empty(self::$flags[$countryId]))
			return self::$flags[$countryId][0];

		$country = self::model()->findByPk($countryId);
		return is_null($country) ? '' : $country->name;
	}

	/**
	 * Получение пути к флагу страны
	 * @static
	 * @param $countryId
	 * @return string
	 */
	public static function getFlagById($countryId)
	{
		if (empty(self::$flags[$countryId]))
			return 'img/flags/no_flag.png';
		return self::$flags[$countryId][1];
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('eng_name',$this->eng_name,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

        public static function getList($withImportant = true)
        {

                if($withImportant)
                        return self::model()->findAll(array('order'=>'pos desc, id asc'));
                else
                        return self::model()->findAll(array('condition'=>'pos = 0','order'=>'id asc'));
        }

        public static function getImportantList()
        {
                return self::model()->findAll(array('condition'=>'pos > 0', 'order'=>'pos desc, id asc'));
        }
}