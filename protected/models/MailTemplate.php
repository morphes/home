<?php

/**
 * @brief This is the model class for table "mail_template".
 *
 * @details The followings are the available columns in table 'mail_template':
 * @param string $key
 * @param string $name
 * @param string $subject
 * @param string $from
 * @param string $keywords
 * @param string $data
 * @param integer $create_time
 * @param integer $update_time
 */
class MailTemplate extends EActiveRecord
{
	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}
	
	/**
	 * Update create_time and update_time in object
	 */
	public function setDate()
	{
		if($this->isNewRecord)
			$this->create_time=$this->update_time=time();
		else
			$this->update_time=time();
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return MailTemplate the static model class
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
		return 'mail_template';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('key', 'required'),
			array('key', 'unique'),
			array('create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('key', 'length', 'max'=>45),
			array('name, subject', 'length', 'max'=>255),
			array('from', 'length', 'max'=>200),
                        array('author', 'length', 'max'=>500),
			array('keywords', 'length', 'max'=>512),
			array('data', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('key, name, subject, from, keywords, data, create_time, update_time', 'safe', 'on'=>'search'),
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
			'key' => 'Ключ шаблона',
			'name' => 'Название',
			'subject' => 'Тема сообщения',
			'from' => 'Email отправителя',
                        'author'=>'Автор',
			'keywords' => 'Переменные для шаблона',
			'data' => 'Текст шаблона',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
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

		$criteria->compare('key',$this->key,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('subject',$this->subject,true);
		$criteria->compare('from',$this->from,true);
                $criteria->compare('author',$this->author,true);
		$criteria->compare('keywords',$this->keywords,true);
		$criteria->compare('data',$this->data,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}