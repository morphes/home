<?php

/**
 * This is the model class for table "media_event_place".
 *
 * The followings are the available columns in table 'media_event_place':
 * @property integer $id
 * @property integer $event_id
 * @property integer $city_id
 * @property string $name
 * @property string $address
 * @property string $geocode
 * @property string $event_time
 * @property integer $create_time
 * @property integer $update_time
 */
class MediaEventPlace extends EActiveRecord
{
	private $_city = null;
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
	 * @return MediaEventPlace the static model class
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
		return 'media_event_place';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('event_id', 'required'),
			array('city_id', 'required', 'on'=>'update'),
			array('event_id, city_id', 'numerical', 'integerOnly'=>true),
			array('name, address', 'length', 'max'=>512),
			array('event_time', 'length', 'max'=>255),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'event_id' => 'Event',
			'city_id' => 'City',
			'name' => 'Name',
			'address' => 'Address',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
		);
	}

	/**
	 * Create new row for set place info later (by ajax request)
	 * @param int $eventId
	 * @return MediaEventPlace
	 * @author Alexey Shvedov
	 */
	static public function createRow($eventId)
	{
		$place = new self();
		$place->event_id = $eventId;

		if (!$place->save()) {
			die ();
		}

		return $place;
	}

	/**
	 * @return City
	 */
	public function getCity()
	{
		if (is_null($this->city_id))
			return null;

		if (is_null($this->_city)) {
			$this->_city = City::model()->findByPk($this->city_id);
		}
		return $this->_city;
	}

	public function getCityName()
	{
		$city = $this->getCity();
		return is_null($city) ? '' : ($city->name);
	}
}