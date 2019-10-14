<?php

/**
 * This is the model class for table "forum_file".
 *
 * The followings are the available columns in table 'forum_file':
 * @property integer $item_id
 * @property integer $file_id
 * @property integer $type
 */
class ForumFile extends EActiveRecord
{

	const TYPE_TOPIC = 1;
	const TYPE_ANSWER = 2;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ForumFile the static model class
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
		return 'forum_file';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('item_id, file_id, type', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('item_id, file_id, type', 'safe', 'on'=>'search'),
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
			'item_id' => 'ID элемента',
			'file_id' => 'ID файла',
			'type' => 'Тип',
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

		$criteria->compare('item_id',$this->item_id);
		$criteria->compare('file_id',$this->file_id);
		$criteria->compare('type',$this->type);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}