<?php

/**
 * This is the model class for table "like_item".
 *
 * The followings are the available columns in table 'like_item':
 * @property integer $id
 * @property integer $author_id
 * @property integer $guest_id
 * @property string $model
 * @property integer $model_id
 * @property integer $create_time
 * @property integer $update_time
 */
class LikeItem extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return LikeItem the static model class
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
		return 'like_item';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('author_id, guest_id, model_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('model', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, author_id, guest_id, model, model_id, create_time, update_time', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'author_id' => 'Author',
			'guest_id' => 'Guest',
			'model' => 'Model',
			'model_id' => 'Model',
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
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('guest_id',$this->guest_id);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Функция проверяет лайкал ли пользователь материал
	 * @param $user_id - id пользователя
	 * @param $model_name - имя модели
	 * @param $model_id - id материала
	 * @return integer - id записи в таблице с лайками
	 */
	public function isAdded($user_id, $model_name, $model_id)
	{
		$command = Yii::app()->db->createCommand();

		if(Yii::app()->user->isGuest)
		{
			$command->select('id')
				->from($this->tableName())
				->where('guest_id = :id AND model_id = :model_id AND model = :model', array(
					':id' => $user_id,
					':model_id' => $model_id,
					':model' => $model_name,
					)
				);
		} else {
			$command->select('id')
				->from($this->tableName())
				->where('author_id = :id AND model_id = :model_id AND model = :model', array(
						':id' => $user_id,
						':model_id' => $model_id,
						':model' => $model_name,
					)
				);
		}

		$item = $command->queryScalar();

		return $item;
	}

	/**
	 * Функция возвращает количество лайков для элемента
	 * @param $model_name - имя модели
	 * @param $model_id - id  материала
	 * @return int - количество лайков
	 */
	public static function countLikes($model_name, $model_id)
	{
		$command = Yii::app()->db->createCommand();

		$command->select('COUNT(*) as cnt')
			->from(self::model()->tableName())
			->where('model_id = :model_id AND model = :model', array(':model_id' => $model_id,':model' => $model_name));

		$count = $command->queryScalar();

		return $count;
	}
}