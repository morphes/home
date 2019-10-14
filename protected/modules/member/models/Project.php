<?php

/**
 * This is the model class for table "project".
 *
 * The followings are the available columns in table 'project':
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $description
 * @property integer $release_date
 * @property string $tag
 * @property string $photo
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property User $user
 */
class Project extends EActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Project the static model class
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
		return 'project';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, release_date, tag, create_time, update_time', 'required'),
			array('id, user_id, release_date, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>45),
			array('description', 'length', 'max'=>2048),
			array('tag', 'length', 'max'=>255),
			array('photo', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, title, description, release_date, tag, photo, create_time, update_time', 'safe', 'on'=>'search'),
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
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'title' => 'Title',
			'description' => 'Description',
			'release_date' => 'Release Date',
			'tag' => 'Tag',
			'photo' => 'Photo',
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('release_date',$this->release_date);
		$criteria->compare('tag',$this->tag,true);
		$criteria->compare('photo',$this->photo,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}