<?php

/**
 * This is the model class for table "idea_uploaded_file".
 *
 * The followings are the available columns in table 'idea_uploaded_file':
 * @property integer $item_id
 * @property integer $idea_type_id
 * @property integer $uploaded_file_id
 *
 * The followings are the available model relations:
 * @property UploadedFile $uploadedFile
 * @property ExteriorContent $item
 */
class IdeaUploadedFile extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return IdeaUploadedFile the static model class
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
		return 'idea_uploaded_file';
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
			'uploadedFile' => array(self::BELONGS_TO, 'UploadedFile', 'uploaded_file_id'),
			'item' => array(self::BELONGS_TO, 'ExteriorContent', 'item_id'),
			'architecture' => array(self::BELONGS_TO, 'Architecture', 'item_id'),
			'interiorPublic' => array(self::BELONGS_TO, 'Interiorpublic', 'item_id'),
		);
	}
}