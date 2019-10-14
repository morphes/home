<?php

/**
 * This is the model class for table "adv_question".
 *
 * The followings are the available columns in table 'adv_question':
 * @property integer $id
 * @property string $author_name
 * @property string $question
 * @property string $email
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 */
class AdvQuestion extends CActiveRecord
{
	/**
	 * Новый вопрос
	 */
	const STATUS_NEW = 1;

	/**
	 * Вопрос обработан
	 */
	const STATUS_PROCESSED = 2;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AdvQuestion the static model class
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
		return 'adv_question';
	}

	public static $statusLabels = array(
		self::STATUS_NEW       => 'Не обработана',
		self::STATUS_PROCESSED => 'Обработана',
	);

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
		if ($this->isNewRecord)
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('status, author_name, email, question', 'required'),
			array('status, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('author_name, email', 'length', 'max'=>60),
			array('question', 'length', 'max'=>3000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, email, create_time, update_time, author_name, question, status', 'safe', 'on'=>'search'),
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
			'author_name' => 'Имя автора',
			'question' => 'Вопрос',
			'email' => 'Email',
			'update_time' => 'Время обновления',
			'status' => 'Статус',
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
		$criteria->compare('author_name',$this->author_name,true);
		$criteria->compare('question',$this->question,true);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


}