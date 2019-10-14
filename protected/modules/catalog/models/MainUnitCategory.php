<?php

/**
 * This is the model class for table "cat_main_unit_category".
 *
 * The followings are the available columns in table 'cat_main_unit_category':
 * @property integer $category_id
 * @property integer $unit_id
 */
class MainUnitCategory extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MainUnitCategory the static model class
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
		return 'cat_main_unit_category';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('category_id, unit_id', 'required'),
			array('category_id, unit_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('category_id, unit_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'category_id' => 'Category',
			'unit_id' => 'Unit',
		);
	}

	public static function updateCategories($selected, $itemId)
	{
		$sql = 'DELETE FROM cat_main_unit_category WHERE unit_id=:uid';
		Yii::app()->db->createCommand($sql)->bindParam(':uid', $itemId)->execute();

		if (!empty($selected)) {
			$sql = 'INSERT INTO  cat_main_unit_category (`category_id`, `unit_id`) VALUES ';
			$sqlValues = array();
			foreach ($selected as $key => $item) {
				$sqlValues[] = '('.intval($key).','.$itemId.')';
			}

			$sql .= implode(',', $sqlValues);

			Yii::app()->db->createCommand($sql)->execute();
		}
	}

	public static function getSelectedCategories($itemId)
	{
		$sql = 'SELECT category_id, 1 FROM cat_main_unit_category WHERE unit_id=:uid';
		$data = Yii::app()->db->createCommand($sql)->bindParam(':uid', $itemId)->setFetchMode(PDO::FETCH_KEY_PAIR)->queryAll();
		return $data;
	}
}