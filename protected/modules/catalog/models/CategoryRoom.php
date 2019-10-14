<?php

/**
 * This is the model class for table "cat_category_room".
 *
 * The followings are the available columns in table 'cat_category_room':
 * @property integer $category_id
 * @property integer $room_id
 */
class CategoryRoom extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CategoryRoom the static model class
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
		return 'cat_category_room';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('category_id, room_id', 'required'),
			array('category_id, room_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('category_id, room_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'category_id' => 'Category',
			'room_id' => 'Room',
		);
	}

	public static function updateRooms($selected, $categoryId)
	{
		$sql = 'DELETE FROM cat_category_room WHERE category_id=:cid';
		Yii::app()->db->createCommand($sql)->bindParam(':cid', $categoryId)->execute();

		if (!empty($selected)) {
			$sql = 'INSERT INTO  cat_category_room (`room_id`, `category_id`) VALUES ';
			$sqlValues = array();
			foreach ($selected as $key => $item) {
				$sqlValues[] = '('.intval($key).','.$categoryId.')';
			}

			$sql .= implode(',', $sqlValues);

			Yii::app()->db->createCommand($sql)->execute();
		}
	}

	/**
	 * Получение выбранных помещений для категории
	 * @param $categoryId
	 * @return array
	 */
	public static function getSelectedRooms($categoryId)
	{
		$sql = 'SELECT room_id FROM cat_category_room WHERE category_id=:cid';
		$data = Yii::app()->db->createCommand($sql)->bindParam(':cid', $categoryId)->queryColumn();

		$result = array();
		foreach ($data as $item) {
			$result[$item] = 1;
		}
		return $result;
	}
}