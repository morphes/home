<?php

/**
 * This is the model class for table "layout_uploaded_file".
 *
 * The followings are the available columns in table 'layout_uploaded_file':
 * @property integer $item_id
 * @property integer $idea_type_id
 * @property integer $uploaded_file_id
 *
 * The followings are the available model relations:
 * @property Interior $item
 * @property UploadedFile $uploadedFile
 */
class LayoutUploadedFile extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return LayoutUploadedFile the static model class
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
		return 'layout_uploaded_file';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('item_id, idea_type_id, uploaded_file_id', 'required'),
			array('item_id, idea_type_id, uploaded_file_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('item_id, idea_type_id, uploaded_file_id', 'safe', 'on'=>'search'),
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
			'item' => array(self::BELONGS_TO, 'Interior', 'item_id'),
			'uploadedFile' => array(self::BELONGS_TO, 'UploadedFile', 'uploaded_file_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'item_id' => 'Item',
			'idea_type_id' => 'Idea Type',
			'uploaded_file_id' => 'Uploaded File',
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
		$criteria->compare('idea_type_id',$this->idea_type_id);
		$criteria->compare('uploaded_file_id',$this->uploaded_file_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}