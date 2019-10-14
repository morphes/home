<?php

/**
 * This is the model class for table "favorite_item".
 *
 * The followings are the available columns in table 'favorite_item':
 * @property integer $favoritegroup_id
 * @property integer $author_id
 * @property integer $cookie_id
 * @property string $model
 * @property string $data
 * @property integer $model_id
 * @property integer $create_time
 * @property integer $update_time
 */
class FavoriteItem extends EActiveRecord
{

	private $_favoriteObject = null;
	private $_parentObject = null;


	public function init()
	{
		parent::init();

		$this->onBeforeSave = array($this, 'setDate');
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return FavoriteItem the static model class
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
		return 'favorite_item';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('model, model_id', 'required'),
			array('favoritegroup_id, author_id, model_id', 'numerical', 'integerOnly'=>true),
			array('model', 'length', 'max'=>45),
			array('data', 'length'),
			array('data', 'jsonArray'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('favoritegroup_id, data, author_id, model, model_id', 'safe', 'on'=>'search'),
		);
	}


	/**
	 * Валидатор значения на json массив
	 * @param $attribute
	 * @param $params
	 *
	 * @return bool
	 */
	public function jsonArray($attribute, $params)
	{
		if ( is_null($this->$attribute) || empty($this->$attribute) ) {
			return true;
		}

		$value = json_decode($this->$attribute, true);

		$message = 'Некорректный формат';
		if ( isset($params['message']) )
			$message = $params['message'];

		if ( !is_array($value) || is_null($value) )
			$this->addError('data', $message);
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
			'favoritegroup_id' => 'Favoritegroup',
			'author_id' => 'Author',
			'model' => 'Model',
			'model_id' => 'Model',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
			'data' => 'Data',
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

		$criteria->compare('favoritegroup_id',$this->favoritegroup_id);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
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

	public function getItemsByUserId($id)
	{
		$command = Yii::app()->db->createCommand();

		if (Yii::app()->user->getIsGuest()) {
			$command->select('CONCAT_WS(":", cookie_id, model, model_id) as k')
				->from($this->tableName())
				->where('cookie_id = :id', array(':id' => User::getCookieId()));
		} else {
			$command->select('CONCAT_WS(":", author_id, model, model_id) as k')
				->from($this->tableName())
				->where('author_id = :id', array(':id' => Yii::app()->user->id));
		}

		$arr = $command->queryAll();

		return $arr;
	}

	/**
	 * Возвращает ключ для кеша, в котором хранится кол-во иэлементов,
	 * добавленных в избранное пользователем.
	 *
	 * @static
	 * @return string Ключ для memcache
	 */
	public static function getCacheKey()
	{
		if (Yii::app()->user->getIsGuest())
			$key = 'favoriteCount:guest:'.User::getCookieId();
		else
			$key = 'favoriteCount:auth:'.Yii::app()->user->id;

		return $key;
	}

	/**
	 * Возвращает кол-во элементов в избранном для указанного пользователя
	 *
	 * @static
	 * @param $userId Идентификатор пользователя
	 * @return int Кол-во элементов в избранном
	 */
	public static function countFavorite($userType, $userId)
	{
		$command = Yii::app()->db->createCommand();
		$command->select('COUNT(*) as cnt');
		$command->from(self::model()->tableName());

		if ($userType == 'auth')
			$command->where('author_id = :author_id', array(':author_id' => $userId));
		else
			$command->where('cookie_id = :cookie_id', array(':cookie_id' => User::getCookieId()));

		$count = $command->queryScalar();

		return (int)$count;
	}


	/**
	 * Устанавливает data, конвертируя $value значение из array в json
	 * @param $value array
	 * @return boolean
	 */
	public function setData($value)
	{
		if ( is_array($value) )
			parent::__set('data', json_encode($value, JSON_NUMERIC_CHECK));
		return true;
	}


	/**
	 * Возвращает data из базы, конвертируя ее из json в array
	 * @return mixed|null
	 */
	public function getData()
	{
		$value = parent::__get('data');
		if ( !is_null($value) && !empty($value) )
			return json_decode($value, true);
		return $value;
	}


	/**
	 * Возвращает объект из избранного
	 * @return null
	 */
	public function getFavoriteObject()
	{
		if ( !$this->_favoriteObject ) {

			Yii::import('application.modules.idea.models.*');
			Yii::import('application.modules.media.models.*');
			Yii::import('application.modules.catalog.models.*');

			$class = $this->model;

			if ( class_exists($class) )
				$this->_favoriteObject = $class::model()->findByPk($this->model_id);
		}

		return $this->_favoriteObject;
	}


	/**
	 * Возвращает родительский объект объекта в избранном
	 * Используется для того, чтобы получить родителя для объекта UploadedFile
	 * Например, объекта интерьера, картинку которого добавили в избранное
	 * @return null
	 */
	public function getParentObject()
	{
		if ( !$this->_parentObject ) {

			Yii::import('application.modules.idea.models.*');
			Yii::import('application.modules.media.models.*');
			Yii::import('application.modules.catalog.models.*');

			$data = $this->getData();

			if ( !isset($data['parent_object_class']) || !isset($data['parent_object_id']) )
				return null;

			$class = $data['parent_object_class'];

			if ( class_exists($class) )
				$this->_parentObject = $class::model()->findByPk((int) $data['parent_object_id']);
		}

		return $this->_parentObject;
	}
}