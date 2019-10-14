<?php

/**
 * This is the model class for table "media_event_visit".
 *
 * The followings are the available columns in table 'media_event_visit':
 * @property integer $event_id
 * @property integer $user_id
 * @property integer $create_time
 */
class MediaEventVisit extends EActiveRecord
{
	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}

	/**
	 * Update create_time in object
	 */
	public function setDate()
	{
		if ($this->getIsNewRecord())
			$this->create_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MediaEventVisit the static model class
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
		return 'media_event_visit';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('event_id, user_id', 'required'),
			array('event_id, user_id', 'numerical', 'integerOnly'=>true),
		);
	}

}