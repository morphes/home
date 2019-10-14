<?php

/**
 * This is the model class for table "cat_contractor_contact".
 *
 * The followings are the available columns in table 'cat_contractor_contacts':
 * @property integer $id
 * @property integer $contractor_id
 * @property string $name
 * @property string $post
 * @property string $mobile
 * @property string $phone
 * @property string $email
 * @property integer $create_time
 * @property integer $update_time
 */
class ContractorContact extends EActiveRecord
{
	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}

	public function setDate()
	{
		if ($this->isNewRecord) {
			$this->create_time = $this->update_time = time();
		} else
			$this->update_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ContractorContacts the static model class
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
		return 'cat_contractor_contact';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('contractor_id', 'required'),
			array('contractor_id', 'numerical', 'integerOnly'=>true),
			array('name, post, mobile, phone', 'length', 'max'=>255),
			array('email', 'length', 'max'=>50),
			array('email', 'email'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, contractor_id, name, post, mobile, phone, email, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'contractor_id' => 'Contractor',
			'name' => 'ФИО',
			'post' => 'Должность',
			'mobile' => 'Моб. телефон',
			'phone' => 'Рабочий телефон',
			'email' => 'Email',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
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
		$criteria->compare('contractor_id',$this->contractor_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('post',$this->post,true);
		$criteria->compare('mobile',$this->mobile,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}