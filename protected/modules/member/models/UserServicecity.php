<?php

/**
 * This is the model class for table "user_servicecity".
 *
 * The followings are the available columns in table 'user_servicecity':
 * @property integer $user_id
 * @property integer $city_id
 *
 * The followings are the available model relations:
 * @property City $city
 * @property User $user
 */
class UserServicecity extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserServicecity the static model class
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
		return 'user_servicecity';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, city_id', 'required'),
			array('user_id, city_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, city_id', 'safe', 'on'=>'search'),
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
                        //'region' => array(self::BELONGS_TO, 'Region', 'region_id'),
                        //'country' => array(self::BELONGS_TO, 'Country', 'country_id'),
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'user_id' => 'Пользователь',
			'city_id' => 'Город',
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

		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('city_id',$this->city_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


        public function getLocationLabel() {
                if(!empty($this->city_id))
                        return City::model()->findByPk($this->city_id)->name;
                if(!empty($this->region_id))
                        return Region::model()->findByPk($this->region_id)->name;
                if(!empty($this->country_id))
                        return Country::model()->findByPk($this->country_id)->name;
        }

        public function getLocationId() {
                if(!empty($this->city_id))
                        return $this->city_id;
                if(!empty($this->region_id))
                        return $this->region_id;
                if(!empty($this->country_id))
                        return $this->country_id;
        }

        public function getLocationType() {
                if(!empty($this->city_id))
                        return 'city';
                if(!empty($this->region_id))
                        return 'region';
                if(!empty($this->country_id))
                        return 'country';
        }

    static public function getNewestSpecsByCityAndService($cityId, array $serviceIds, $excludeUserId)
    {
        if (is_null($cityId) || empty($serviceIds)) {
            return null;
        }

        $serviceIdsNew = [];
        foreach($serviceIds as $serv) {
            $serviceIdsNew[] = $serv['service_id'];
        }

        $services = implode(',', $serviceIdsNew);
        $cityId = (int) $cityId;

        $userIds = Yii::app()->db->createCommand('
            select distinct usc.user_id from user_servicecity usc
              inner join user_service us on us.user_id=usc.user_id
              inner join user u on u.id=usc.user_id
            where usc.city_id='.$cityId.' and us.service_id in ('.$services.') and usc.user_id<>'.$excludeUserId.'
            order by u.create_time desc
            limit 15
        ')->queryColumn();

        $users = implode(',', $userIds);
        if ($userIds) {
            return User::model()->findAll('id in ('.$users.')');
        }
        return [];
    }
}