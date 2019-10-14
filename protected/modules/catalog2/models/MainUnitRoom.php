<?php

/**
 * This is the model class for table "cat_main_unit_room".
 *
 * The followings are the available columns in table 'cat_main_unit_room':
 * @property integer $room_id
 * @property integer $unit_id
 */
class MainUnitRoom extends Catalog2ActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MainUnitRoom the static model class
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
		return 'cat_main_unit_room';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('room_id, unit_id', 'required'),
			array('room_id, unit_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('room_id, unit_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'room_id' => 'Room',
			'unit_id' => 'Unit',
		);
	}

	public static function updateRooms($selected, $itemId)
	{
		$sql = 'DELETE FROM cat_main_unit_room WHERE unit_id=:uid';
		Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':uid', $itemId)->execute();

		if (!empty($selected)) {
			$sql = 'INSERT INTO  cat_main_unit_room (`room_id`, `unit_id`) VALUES ';
			$sqlValues = array();
			foreach ($selected as $key => $item) {
				$sqlValues[] = '('.intval($key).','.$itemId.')';
			}

			$sql .= implode(',', $sqlValues);

			Yii::app()->dbcatalog2->createCommand($sql)->execute();
		}
	}

	public static function getSelectedRooms($itemId)
	{
		$sql = 'SELECT room_id FROM cat_main_unit_room WHERE unit_id=:uid';
		$data = Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':uid', $itemId)->queryColumn();

		$result = array();
		foreach ($data as $item) {
			$result[$item] = 1;
		}
		return $result;
	}
}