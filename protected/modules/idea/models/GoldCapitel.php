<?php

/**
 * This is the model class for table "gold_capitel".
 *
 * The followings are the available columns in table 'gold_capitel':
 * @property integer $id
 * @property integer $interior_id
 * @property integer $status
 * @property integer $author_id
 *
 * The followings are the available model relations:
 * @property User $author
 * @property Interior $interior
 */
class GoldCapitel extends EActiveRecord
{
	const STATUS_ADDED    = 1; // Добавлен
 	const STATUS_SELECTED = 2; // Выбран
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return GoldCapitel the static model class
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
		return 'gold_capitel';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			//array('interior_id, status, author_id', 'numerical', 'integerOnly'=>true),
			array('status', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, interior_id, status, author_id', 'safe', 'on'=>'search'),
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
			'author' => array(self::BELONGS_TO, 'User', 'author_id'),
			'interior' => array(self::BELONGS_TO, 'Interior', 'interior_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'		=> 'ID',
			'interior_id'	=> 'Interior',
			'status'	=> 'Status',
			'author_id'	=> 'Author',
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
		$criteria->compare('interior_id',$this->interior_id);
		$criteria->compare('status',$this->status);
		$criteria->compare('author_id',$this->author_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}