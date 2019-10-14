<?php

/**
 * This is the model class for table "cat_main_room".
 *
 * The followings are the available columns in table 'cat_main_room':
 * @property integer $id
 * @property integer $status
 * @property integer $position
 * @property string $name
 * @property string $genetive
 * @property integer $create_time
 * @property integer $update_time
 */
class MainRoom extends Catalog2ActiveRecord
{
	const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 2;
	const STATUS_DELETED = 3;

	public static $statusNames = array(
		self::STATUS_ENABLED => 'Включен',
		self::STATUS_DISABLED => 'Выключен',
		self::STATUS_DELETED => 'Удален',
	);

	private static $_rooms = null;

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}

	public function setDate()
	{
		if ($this->getIsNewRecord())
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MainRoom the static model class
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
		return 'cat_main_room';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('status', 'in', 'range'=>array(self::STATUS_ENABLED, self::STATUS_DISABLED, self::STATUS_DELETED), 'strict'=>false),
			array('name, genetive', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, name', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'status' => 'Статус',
			'name' => 'Название',
			'genetive' => 'Родительский падеж',
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
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);

		if (empty( $this->status )) {
			$criteria->addInCondition('status', array(self::STATUS_ENABLED, self::STATUS_DISABLED));
		} else {
			$criteria->compare('status',$this->status);
		}

		$criteria->order = 't.position ASC';

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Получение все помещений за исключением 1го помещения ("Все помещения")
	 * со статусами включен и выключен
	 * Кэш в памяти скрипта
	 */
	public static function getAllRooms()
	{
		if ( self::$_rooms !== null )
			return self::$_rooms;

		$sql = 'SELECT id, `name` FROM cat_main_room '
			.'WHERE status IN (:st1, :st2) AND id<>(select id from cat_main_room ORDER BY position ASC LIMIT 1) '
			.'ORDER BY position ASC';

		$st1 = self::STATUS_ENABLED;
		$st2 = self::STATUS_DISABLED;

		$data = Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':st1', $st1)->bindParam(':st2', $st2)->queryAll();

		$result = array();
		foreach ($data as $item) {
			$result[$item['id']] = $item['name'];
		}

		self::$_rooms = $result;
		return self::$_rooms;
	}

}