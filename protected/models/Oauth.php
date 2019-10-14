<?php

/**
 * @brief This is the model class for table "oauth".
 *
 * @details The followings are the available columns in table 'oauth':
 * @param integer $user_id
 * @param integer $type_id
 * @param integer $uid
 *
 * The followings are the available model relations:
 * @param User $user
 */
class Oauth extends EActiveRecord
{
	// type list, used in table
	const VKONTAKTE = 1;
	const FACEBOOK = 2;
	const TWITTER = 3;
	const ODKL = 4;
	
	const SOC_SESSION_INFIX = '_social_user_data';
	public static $defSocialData = array(
		'typeid' => '',
		'uid' => '',
		'firstname' => '',
		'lastname' => '',
		'login' => '',
		'email' => '',
		'account_name' => '',
		'social_name' => '',
	);
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return Oauth the static model class
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
		return 'oauth';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, type_id, uid', 'required'),
			array('user_id, type_id', 'numerical', 'integerOnly'=>true),
			array('uid', 'length', 'max' => 45),
			array('type_id+uid', 'uniqueMultiColumnValidator', 'message' => 'Данный аккаунт уже привязан.'),
			array('type_id+user_id', 'uniqueMultiColumnValidator', 'message' => 'Данный аккаунт уже привязан к соц сети.'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, type_id, uid', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'user_id' => 'User',
			'type_id' => 'Type',
			'uid' => 'Uid',
		);
	}

	public function login()
	{
		/** @var $user User */
		$user = User::model()->findByPk($this->user_id);
		if (is_null($user)) {
			throw new CHttpException(404, 'Пользователь не найден');
		}
		return $user->login(true);
	}
	
	
	/**
	 * Проверяет привязан ли к пользователю аккаунт твиттера.
	 * @param integer $uid Идентификатор пользователя на сайте
	 * @return Oauth Если существует, возвращает модель
	 * 
	 * @author Sergey Seregin
	 */
	public static function checkBindTwitter($uid)
	{
		$model = Oauth::model()->findByAttributes(array('user_id' => $uid, 'type_id' => Oauth::TWITTER));
		if ($model)
			return $model;
		else
			return null;
	}
	
	/**
	 * Проверяет привязан ли к пользователю аккаунт Вконтакте.
	 * @param integer $uid Идентификатор пользователя на сайте
	 * @return Oauth Если существует, возвращает модель
	 * 
	 * @author Sergey Seregin
	 */
	public static function checkBindVkontakte($uid)
	{
		$model = Oauth::model()->findByAttributes(array('user_id' => $uid, 'type_id' => Oauth::VKONTAKTE));
		if ($model)
			return $model;
		else
			return null;
	}
	
	/**
	 * Проверяет привязан ли к пользователю аккаунт Facebook'a.
	 * @param integer $uid Идентификатор пользователя на сайте
	 * @return Oauth Если существует, возвращает модель
	 * 
	 * @author Sergey Seregin
	 */
	public static function checkBindFacebook($uid)
	{
		$model = Oauth::model()->findByAttributes(array('user_id' => $uid, 'type_id' => Oauth::FACEBOOK));
		if ($model)
			return $model;
		else
			return null;
	}

	/**
	 * Проверяет привязан ли к пользователю аккаунт Одноклассников.
	 * @param integer $uid Идентификатор пользователя на сайте
	 * @return Oauth Если существует, возвращает модель
	 *
	 * @author Alexey Shvedov
	 */
	public static function checkBindODKL($uid)
	{
		$model = Oauth::model()->findByAttributes(array('user_id' => $uid, 'type_id' => Oauth::ODKL));
		if ($model)
			return $model;
		else
			return null;
	}
}