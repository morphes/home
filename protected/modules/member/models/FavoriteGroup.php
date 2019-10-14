<?php

/**
 * This is the model class for table "favorite_group".
 *
 * The followings are the available columns in table 'favorite_group':
 * @property integer $id
 * @property string $name
 * @property integer $author_id
 * @property integer $create_time
 * @property integer $update_time
 */
class FavoriteGroup extends EActiveRecord
{
	public function init()
	{
		parent::init();

		$this->onBeforeSave = array($this, 'setDate');
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return FavoriteGroup the static model class
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
		return 'favorite_group';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('author_id, name', 'required'),
			array('author_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, author_id', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'name' => 'Name',
			'author_id' => 'Author',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
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
	 * Возвращает массив групп избранного для указанного пользователя
	 * в параметре $id
	 *
	 * @param $id Идентификатор пользователя, группы которого нужно получить
	 *
	 * @return array Массив групп избранного
	 */
	public function getGroupsByUserId($id)
	{
		$command = Yii::app()->db->createCommand();

		$command->select('id, name')
			->from($this->tableName())
			->where('author_id = :id', array(':id' => (int)$id));
		$command->order = 'create_time ASC';

		$arr = $command->queryAll();

		return $arr;
	}
}