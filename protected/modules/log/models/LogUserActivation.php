<?php

/**
 * This is the model class for table "log_user_activation".
 *
 * The followings are the available columns in table 'log_user_activation':
 * @property integer $id
 * @property integer $user_id
 * @property integer $referrer_id
 * @property integer $activate_time
 * @property integer $create_time
 */
class LogUserActivation extends EActiveRecord
{
        
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return LogUserActivation the static model class
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
		return 'log_user_activation';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, referrer_id, activate_time, create_time', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, referrer_id, activate_time, create_time', 'safe', 'on'=>'search'),
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
                    'user' => array(self::BELONGS_TO, 'User', 'user_id'),
                    'referrer' => array(self::BELONGS_TO, 'User', 'referrer_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'Пользователь',
			'referrer_id' => 'Кто пригласил',
			'activate_time' => 'Дата активации',
			'create_time' => 'Дата регистрации',
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('referrer_id',$this->referrer_id);
		$criteria->compare('activate_time',$this->activate_time);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}