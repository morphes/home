<?php

/**
 * @brief This is the model class for table "menu".
 *
 * @details The followings are the available columns in table 'menu':
 * @param integer $id
 * @param string $key
 * @param integer $parent_id
 * @param integer $type_id
 * @param integer $status
 * @param string $label
 * @param string $label_hidden
 * @param string $url
 * @param integer $create_time
 * @param integer $update_time
 */
class Menu extends EActiveRecord
{
	// menu types
	const TYPE_MAIN = 1;
	const TYPE_HEADER_ADD = 2;
	const TYPE_FOOTER = 3;
	const TYPE_FOOTER_ADD = 4;
	const TYPE_FOOTER_CAT = 5;

	const STATUS_ACTIVE = 1;
	const STATUS_NOTACTIVE = 0; // display, but has not link
	const STATUS_INPROGRESS = 2; // показывается неактивный пункт
	// For cache
	const MENU_FRONTEND_REDIS_KEY = 'MenuFrontend_updateTime';

	public static $statusNames = array(
		self::STATUS_ACTIVE	=> 'Активен',
		self::STATUS_INPROGRESS	=> 'В разработке',
		self::STATUS_NOTACTIVE	=> 'Не активен',
	);
	public static $menuNames = array(
		self::TYPE_MAIN		=> 'Главное меню',
		self::TYPE_HEADER_ADD	=> 'Дополнительное меню',
		self::TYPE_FOOTER	=> 'Нижнее меню',
		self::TYPE_FOOTER_ADD	=> 'Нижнее дополнительное меню',
		self::TYPE_FOOTER_CAT   => 'Нижнее меню (категории)'
	);

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
		$this->onAfterSave = array($this, 'setCacheTime');
		$this->onAfterDelete = array($this, 'setCacheTime');
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
	 * Set time dependency for depend cache keys
	 */
	public function setCacheTime()
	{
		Yii::app()->redis->set(self::MENU_FRONTEND_REDIS_KEY, time());
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @return Menu the static model class
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
		return 'menu';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
		    array('key, type_id', 'required'),
		    array('parent_id, type_id, status, create_time, update_time', 'numerical', 'integerOnly' => true),
		    array('key, label, label_hidden, url, no_active_text', 'length', 'max' => 255),
		    array('key', 'unique', 'message' => 'Данный ключ существует.'),
		    // The following rule is used by search().
		    // Please remove those attributes that should not be searched.
		    array('id, key, parent_id, type_id, status, label, label_hidden, url, no_active_text, create_time, update_time', 'safe', 'on' => 'search'),
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
		    'id'		=> 'ID',
		    'key'		=> 'Ключ',
		    'parent_id' 	=> 'Parent',
		    'type_id'		=> 'Type',
		    'status'		=> 'Статус',
		    'label'		=> 'Название',
		    'label_hidden'	=> 'Скрытое название<br>(class="-text-block")',
		    'url'		=> 'URL',
		    'no_active_text'	=> 'Подсказка для пункта со статусом "В разработке"',
		    'create_time'	=> 'Дата создания',
		    'update_time'	=> 'Дата обновления',
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

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('key', $this->key, true);
		$criteria->compare('parent_id', $this->parent_id);
		$criteria->compare('type_id', $this->type_id);
		$criteria->compare('status', $this->status);
		$criteria->compare('label', $this->label, true);
		$criteria->compare('label_hidden', $this->label_hidden, true);
		$criteria->compare('url', $this->url, true);
		$criteria->compare('no_active_text', $this->no_active_text, true);
		$criteria->compare('create_time', $this->create_time);
		$criteria->compare('update_time', $this->update_time);

		return new CActiveDataProvider($this, array(
		    'criteria' => $criteria,
		));
	}
}