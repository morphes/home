<?php

/**
 * This is the model class for table "msg_file".
 *
 * The followings are the available columns in table 'msg_file':
 * @property integer $msg_body_id
 * @property integer $uploaded_file_id
 */
class MsgFile extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return MsgFile the static model class
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
		return 'msg_file';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('msg_body_id, uploaded_file_id', 'required'),
			array('msg_body_id, uploaded_file_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('msg_body_id, uploaded_file_id', 'safe', 'on'=>'search'),
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
			'msg_body_id' => 'Msg Body',
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

		$criteria->compare('msg_body_id',$this->msg_body_id);
		$criteria->compare('uploaded_file_id',$this->uploaded_file_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}