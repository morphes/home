<?php

/**
 * This is the model class for table "geoip".
 *
 * The followings are the available columns in table 'geoip':
 * @property integer $id
 * @property integer $start
 * @property integer $end
 * @property string $country
 * @property integer $city_id
 */
class Geoip extends EActiveRecord
{
        const COOKIE_GEO_SELECTED = 'city_selected';
        const COOKIE_GEO_DETECTED = 'city_detected';

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Geoip the static model class
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
		return 'geoip';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('start, end', 'required'),
			array('start, end, city_id', 'numerical', 'integerOnly'=>true),
			array('country', 'length', 'max'=>500),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, start, end, country, city_id', 'safe', 'on'=>'search'),
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
                        'city' => array(self::BELONGS_TO, 'City', 'city_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'start' => 'Start',
			'end' => 'End',
			'country' => 'Country',
			'city_id' => 'City',
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
		$criteria->compare('start',$this->start);
		$criteria->compare('end',$this->end);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('city_id',$this->city_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

        /**
         * Возвращает объект города, которому соответствует указанный ip
         * @param string/null $ip - ip адрес. Если пустой, берется ip пользователя
         * @return CActiveRecord|mixed
         */
        static public function getCity($ip = null)
        {
                if(!$ip)
                        $ip = Yii::app()->request->userHostAddress;

                $long_ip = ip2long($ip);

                $geoip = self::model()->find('start<:ip and end>:ip', array(':ip'=>$long_ip));

                if ( $geoip && isset($geoip->city_id) )
                        return $geoip->city;
                else
                        return City::model()->findByPk(City::ID_MOSCOW);
        }
}