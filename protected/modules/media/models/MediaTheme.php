<?php

/**
 * This is the model class for table "media_theme".
 *
 * The followings are the available columns in table 'media_theme':
 * @property integer $id
 * @property integer $status
 * @property integer $name
 * @property integer $create_time
 */
class MediaTheme extends EActiveRecord
{

	const STATUS_ACTIVE = 1; // Активен
	const STATUS_DELETED = 2; // Удален

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MediaTheme the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'media_theme';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('status, pos, create_time', 'numerical', 'integerOnly'=>true),
			array('name', 'required'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, name, pos, create_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' 		=> 'ID',
			'status' 	=> 'Статус',
			'name' 		=> 'Имя',
			'pos' 		=> 'Позиция',
			'create_time' 	=> 'Время создания',
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
		$criteria->compare('status', MediaTheme::STATUS_ACTIVE);
		$criteria->compare('pos',$this->pos);
		$criteria->compare('name',$this->name);
		$criteria->compare('create_time',$this->create_time);


		$sort = new CSort();
		$sort->defaultOrder = array('pos' => CSort::SORT_ASC);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination' => array(
				'pageSize' => 20
			),
			'sort' => $sort
		));
	}


	/**
	 * Update create_time and update_time in object
	 */
	public function setDate()
	{
		if ($this->isNewRecord)
			$this->create_time = time();
	}


	/**
	 * Получаем ассоциативный массив тематик array('id тематики' => 'название тематики', ...)
	 * @static
	 * @return array
	 */
	static public function getThemes()
	{
		$command = Yii::app()->getDb()->createCommand();
		$command->select('id, name');
		$command->from(MediaTheme::model()->tableName());
		$command->order = 'pos ASC';
		$command->where = 'status = :status';
		$command->params = array(':status' => MediaTheme::STATUS_ACTIVE);
		$arr = $command->queryAll();

		$result = array();
		foreach($arr as $item) {
			$result[$item['id']] = $item['name'];
		}

		return $result;
	}
}