<?php

/**
 * This is the model class for table "feedback_file".
 *
 * The followings are the available columns in table 'feedback_file':
 * @property integer $feedback_id
 * @property integer $file_id
 */
class FeedbackFile extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return FeedbackFile the static model class
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
		return 'feedback_file';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('feedback_id, file_id', 'required'),
			array('feedback_id, file_id', 'numerical', 'integerOnly'=>true),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'feedback_id' => 'Feedback',
			'file_id' => 'File',
		);
	}

	public function getDownloadLink()
	{
		return Yii::app()->getRequest()->getHostInfo().'/download/feedback/'.$this->file_id;
	}
}