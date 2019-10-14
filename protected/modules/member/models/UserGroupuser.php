<?php

/**
 * This is the model class for table "user_groupuser".
 *
 * The followings are the available columns in table 'user_groupuser':
 * @property integer $user_id
 * @property integer $group_id
 */
class UserGroupuser extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return UserGroupuser the static model class
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
		return 'user_groupuser';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, group_id', 'required'),
                        array('user_id', 'exist', 'className'=>'User', 'attributeName'=>'id'),
                        array('group_id', 'exist', 'className'=>'Usergroup', 'attributeName'=>'id'),
			array('user_id, group_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, group_id', 'safe', 'on'=>'search'),
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
                    'users' => array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'user_id' => 'User',
			'group_id' => 'Group',
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

		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('group_id',$this->group_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}