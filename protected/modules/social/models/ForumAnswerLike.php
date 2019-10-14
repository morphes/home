<?php

/**
 * This is the model class for table "forum_like_answer".
 *
 * The followings are the available columns in table 'forum_like_answer':
 * @property integer $long_ip
 * @property string $useragent
 * @property integer $user_id
 * @property integer $answer_id
 * @property integer $create_time
 */
class ForumAnswerLike extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ForumAnswerLike the static model class
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
		return 'forum_like_answer';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('useragent', 'required'),
			array('long_ip, user_id, answer_id, create_time', 'numerical', 'integerOnly'=>true),
			array('useragent', 'length', 'max'=>32),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('long_ip, useragent, user_id, answer_id, create_time', 'safe', 'on'=>'search'),
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
			'long_ip'     => 'Long Ip',
			'useragent'   => 'Useragent',
			'user_id'     => 'Автор',
			'answer_id'   => 'ID ответа',
			'create_time' => 'Дата создания',
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

		$criteria->compare('long_ip',$this->long_ip);
		$criteria->compare('useragent',$this->useragent,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('answer_id',$this->answer_id);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Проверяет может ли текущий пользователь голосовать за указанный "ответ"
	 * @param $answerId Идентификатор ответа, который проверяем на голосовательность
	 * @return bool Если можем проголосовать возваращет TRUE, иначе FALSE
	 */
	public static function canVote($answerId)
	{
		$model = ForumAnswerLike::model()->findAllByAttributes(array(
			'long_ip'   => ip2long($_SERVER['REMOTE_ADDR']),
			'useragent' => md5($_SERVER['HTTP_USER_AGENT']),
			'user_id'   => Yii::app()->user->id,
			'answer_id' => $answerId
		));

		return ( ! $model) ? true : false;
	}
}