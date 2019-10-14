<?php

/**
 * This is the model class for table "country".
 *
 * The followings are the available columns in table 'country':
 * @property string $id
 * @property string $name
 *
 * The followings are the available model relations:
 * @property City[] $cities
 * @property Region[] $regions
 */
class Country extends EActiveRecord
{

        const COUNTRY_RUSSIA = 3159;

	private static $flags = array(
		3159 => array('Россия', 'img/flags/ru.png'),
		9908 => array('Украина', 'img/flags/ua.png'),
		248 => array('Беларусь', 'img/flags/by.png'),
		1894 => array('Казахстан', 'img/flags/kz.png'),
		2788 => array('Молдова', 'img/flags/md.png'),
		9787 => array('Узбекистан', 'img/flags/uz.png'),
		81 => array('Азербайджан', 'img/flags/az.png'),
		2448 => array('Латвия', 'img/flags/lv.png'),
		616 => array('Великобритания', 'img/flags/uk.png'),
		10668 => array('Франция', 'img/flags/fr.png'),
		245 => array('Армения', 'img/flags/am.png'),
		1707 => array('Испания', 'img/flags/es.png'),
		10968 => array('Эстония', 'img/flags/ee.png'),
		2303 => array('Киргызстан', 'img/flags/kg.png'),
		2514 => array('Литва', 'img/flags/lt.png'),
		5681 => array('США', 'img/flags/us.png'),
		1280 => array('Грузия', 'img/flags/ge.png'),
		1393 => array('Израиль', 'img/flags/il.png'),
		1786 => array('Италия', 'img/flags/it.png'),
		10874 => array('Чехия', 'img/flags/cz.png'),
		2172 => array('Канада', 'img/flags/ca.png'),
		2897 => array('Польша', 'img/flags/pl.png'),
		1012 => array('Германия', 'img/flags/de.png'),
		9705 => array('Турция', 'img/flags/tr.png'),
		4 => array('Австралия', 'img/flags/au.png'),
		63 => array('Австрия', 'img/flags/at.png'),
		582041 => array('Македония', 'img/flags/mk.png'),
		582051 => array('О.А.Э.', 'img/flags/ae.png'),
		404 => array('Бельгия', 'img/flags/be.png'),
		428 => array('Болгария', 'img/flags/bg.png'),
		9575 => array('Таджикистан', 'img/flags/tj.png'),
		9638 => array('Туркменистан', 'img/flags/tm.png'),
		10904 => array('Швейцария', 'img/flags/che.png'),
		2297 => array('Кипр', 'img/flags/cy.png'),
		2880 => array('Норвегия', 'img/flags/no.png'),
	);
	/*

*/

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
		return 'country';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, eng_name', 'length', 'max'=>128),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name', 'safe', 'on'=>'search'),
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
			'cities' => array(self::HAS_MANY, 'City', 'country_id'),
			'regions' => array(self::HAS_MANY, 'Region', 'country_id'),
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
			'eng_name' => 'Английское название',
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