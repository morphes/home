<?php

/**
 * This is the model class for table "cat_store_offer".
 *
 * The followings are the available columns in table 'cat_store_offer':
 * @property integer $id
 * @property string $company
 * @property integer $city_name
 * @property string $company_phone
 * @property string $email
 * @property string $name
 * @property string $job
 * @property string $site
 * @property string $comment
 * @property integer $accept_rule
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 * @property string $selected_services
 */
class StoreOffer extends Catalog2ActiveRecord
{
    public $verifyCode;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return StoreOffer the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function behaviors()
	{
		return array(
			'ModelTimeBehavior' => array(
				'class'     => 'application.components.ModelTimeBehavior',
				'nameCTime' => 'create_time',
				'nameUTime' => 'update_time'
			)
		);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'cat_store_offer';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('accept_rule, status, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('company, city_name, email', 'required'),
			array('email', 'email'),
			array('company, company_phone, email, job', 'length', 'max'=>50),
			array('name', 'length', 'max'=>70),
			array('city_name, selected_services', 'length', 'max'=>100),
			array('site', 'length', 'max'=>255),
			array('comment', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, company, selected_services, city_name, company_phone, email, name, job, site, comment, accept_rule, status, create_time, update_time', 'safe', 'on'=>'search'),
            array('verifyCode', 'captcha', 'captchaAction' => '/site/captchaWhite','allowEmpty'=>!Yii::app()->user->isGuest),

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
			'id'            => 'ID',
			'company'       => 'Компания',
			'city_name'     => 'Город',
			'company_phone' => 'Телефон компании',
			'email'         => 'Эл. почта компании',
			'name'          => 'ФИО',
			'job'           => 'Должность',
			'site'          => 'Сайт',
			'comment'       => 'Комментарий',
			'accept_rule'   => 'Принял правила?',
			'selected_services' => 'Выбранные услуги',
			'status'        => 'Статус',
			'create_time'   => 'Дата создания',
			'update_time'   => 'Дата обновления',
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
		$criteria->compare('company',$this->company,true);
		$criteria->compare('city_name',$this->city_name, true);
		$criteria->compare('company_phone',$this->company_phone,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('job',$this->job,true);
		$criteria->compare('site',$this->site,true);
		$criteria->compare('comment',$this->comment,true);
		$criteria->compare('accept_rule',$this->accept_rule);
		$criteria->compare('status',$this->status);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}