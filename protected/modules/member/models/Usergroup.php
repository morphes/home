<?php

/**
 * This is the model class for table "usergroup".
 *
 * The followings are the available columns in table 'usergroup':
 * @property integer $id
 * @property string $name
 * @property string $desc
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property User[] $users
 */
class Usergroup extends EActiveRecord
{
	public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
                $this->onAfterDelete = array($this, 'deleteUsers');
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
         * Удаление пользователей из связующей таблицы при удалении группы
         */
        public function deleteUsers()
        {
                Yii::app()->db->createCommand()->delete('user_groupuser', 'group_id = :gid', array(':gid'=>$this->id));
        }
        
        
        /**
         * Проверка наличия определенного пользователя в определенной группе
         * @param integer $uid
         * @return boolean 
         */
        public function isChecked($uid)
        {
                return UserGroupuser::model()->exists('user_id = :uid AND group_id = :gid', array(':uid'=>$uid, ':gid'=>$this->id));
        }
        
	/**
	 * Returns the static model of the specified AR class.
	 * @return Usergroup the static model class
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
		return 'usergroup';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name, desc', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, desc, create_time, update_time', 'safe', 'on'=>'search'),
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
			'users' => array(self::MANY_MANY, 'User', 'user_groupuser(group_id, user_id)'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Название группы',
			'desc' => 'Описание',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
			'group_id' => 'Группа польльзователей',
			'role' => 'Роль пользователя',
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
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public function getUserCount()
	{
		return UserGroupuser::model()->countByAttributes(array('group_id'=>  $this->id));
	}
}