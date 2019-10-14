<?php

/**
 * This is the model class for table "forum_section".
 *
 * The followings are the available columns in table 'forum_section':
 * @property integer $id
 * @property integer $status
 * @property string $name
 * @property string $key
 * @property string $theme_id
 * @property integer $create_time
 * @property integer $update_time
 */
class ForumSection extends EActiveRecord
{
	// --- СТАТУСЫ ---
	const STATUS_PUBLIC 	= 1; // Опубикован
	const STATUS_HIDE 	= 2; // Скрыт
	const STATUS_DELETED 	= 3; // Удален

	public static $statusNames = array(
		self::STATUS_PUBLIC => 'Открыт',
		self::STATUS_HIDE => 'Скрыт'
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
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ForumSection the static model class
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
		return 'forum_section';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, key', 'required'),
			array('status, theme_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name, key', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, name, key, theme_id, create_time, update_time', 'safe', 'on'=>'search'),
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
			'id'          => 'ID',
			'status'      => 'Статус',
			'name'        => 'Название',
			'key'         => 'Ключ',
			'theme_id'    => 'Тематика (связь со «Знаниями»)',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('status',$this->status);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('key',$this->key,true);
		$criteria->compare('theme_id',$this->theme_id,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/**
	 * Получаем список разделов форума в виде массива array('id_раздела' => 'имя_раздела',...)
	 * @return array
	 */
	public static function getSections()
	{
		$command = Yii::app()->getDb()->createCommand();
		$command->select('id, name');
		$command->from(ForumSection::model()->tableName());
		$command->order = 'name ASC';
		$command->where = 'status = :status';
		$command->params = array(':status' => ForumSection::STATUS_PUBLIC);
		$arr = $command->queryAll();

		$result = array();
		foreach($arr as $item) {
			$result[$item['id']] = $item['name'];
		}

		return $result;
	}

	public function getElementLink()
	{
		return '/forum/'.$this->key;
	}
}