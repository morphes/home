<?php

/**
 * This is the model class for table "portfolio_sort".
 *
 * The followings are the available columns in table 'portfolio_sort':
 * @property integer $item_id
 * @property integer $user_id
 * @property integer $idea_type_id
 * @property integer $service_id
 * @property integer $position
 * @property integer $update_time
 */
class PortfolioSort extends EActiveRecord
{
	// сохраненная старая позиция
	private $_position=null;
	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');

		$this->onAfterFind = array($this, 'storeData');
		$this->onAfterSave = array($this, 'moveItem');
	}

	/**
	 * Update create_time and update_time in object
	 */
	public function setDate()
	{
		$this->update_time = time();
	}

	public function storeData()
	{
		$this->_position = $this->position;
	}

	/**
	 * Перемещение на новую позицию в списке
	 * @param $newPosition
	 */
	public function moveItem()
	{
		if  ($this->position == $this->_position)
			return;

		if (empty($this->_position)) {
			$timeOrder = 'DESC';
		} else {
			$timeOrder = ($this->_position > $this->position) ? 'DESC' : 'ASC';
		}
		// установка новой позиции
		$this->_position = $this->position;

		$sql = 'SET @a:=0; UPDATE '.$this->tableName().' SET position = @a := @a + 1 WHERE user_id=:uid AND service_id=:sid ORDER BY position ASC, update_time '.$timeOrder;
		$uid = $this->user_id;
		$serviceId = $this->service_id;
		Yii::app()->db->createCommand($sql)->bindParam(':uid', $uid)->bindParam(':sid', $serviceId)->execute();
		return true;
	}


	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PortfolioSort the static model class
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
		return 'portfolio_sort';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('item_id, user_id, idea_type_id, service_id, position, update_time', 'required'),
			array('item_id, user_id, idea_type_id, service_id, position, update_time', 'numerical', 'integerOnly'=>true),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'item_id' => 'Item',
			'user_id' => 'User',
			'idea_type_id' => 'Idea Type',
			'service_id' => 'Service',
			'position' => 'Position',
			'update_time' => 'Update Time',
		);
	}

}