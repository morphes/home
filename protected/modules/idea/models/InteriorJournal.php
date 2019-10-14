<?php

/**
 * This is the model class for table "interior_journal".
 *
 * The followings are the available columns in table 'interior_journal':
 * @property integer $id
 * @property integer $interior_id
 * @property integer $user_id
 * @property integer $create_time
 * @property integer $key
 * @property string $value
 */
class InteriorJournal extends EActiveRecord
{
        
        const LOG_STATUS = 1;
        const LOG_COMMENT = 2;
        
        
	/**
	 * Returns the static model of the specified AR class.
	 * @return InteriorJournal the static model class
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
		return 'interior_journal';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('interior_id, user_id, create_time, key', 'required'),
			array('interior_id, user_id, create_time, key', 'numerical', 'integerOnly'=>true),
			array('value', 'length', 'max'=>200),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, interior_id, user_id, create_time, key, value', 'safe', 'on'=>'search'),
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
			'interior_id' => 'Interior',
			'user_id' => 'User',
			'create_time' => 'Create Time',
			'key' => 'Key',
			'value' => 'Value',
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('key',$this->key);
		$criteria->compare('value',$this->value,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
        
        static public function setComment($interior, $message)
        {
                if($interior instanceof Interior && !$interior->isNewRecord){
                        
                        $msg = new self();
                        $msg->interior_id = $interior->id;
                        $msg->user_id = Yii::app()->user->id;
                        $msg->create_time = time();
                        $msg->key = self::LOG_COMMENT;
                        $msg->value = CHtml::encode($message);
                        $msg->save(false);
                        
                        return $msg;
                }
                return false;
        }
        
        
        static public function getJournal($interior)
        {
                if ($interior instanceof Interior && isset($interior->id)) {
                        return new CActiveDataProvider('InteriorJournal', array(
                            'criteria'=>array(
                                'condition'=>'interior_id = :interior_id',
                                'params'=>array(':interior_id'=>$interior->id),
                                'order'=>'create_time DESC',
                            ),
                            'pagination'=>array(
                                'pageSize'=>30,
                                
                            ),
                        ));
                        //return self::model()->findAll($criteria);
                }
                return array();
        }
        
        
        static public function getMessage($msg)
        {
                if($msg->key == self::LOG_STATUS)
                        return 'Установил статус <b>'.Interior::$allStatusNames[$msg->value].'</b>';
                
                if($msg->key == self::LOG_COMMENT)
                        return '<b>'.$msg->value.'</b>';
        }
        
        
}