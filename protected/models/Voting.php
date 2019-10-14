<?php

/**
 * This is the model class for table "voting".
 *
 * The followings are the available columns in table 'voting':
 * @property integer $author_id
 * @property integer $model_id
 * @property string $model
 * @property integer $mark
 * @property integer $create_time
 */
class Voting extends EActiveRecord
{
	public $cnt_mark;
	
	public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
                $this->onBeforeSave = array($this, 'checkExist');
        }
	
	/**
         * Update create_time and update_time in object
         */
        public function setDate()
        {
                if ($this->isNewRecord)
                        $this->create_time = time();		
        }
	
	/**
	 * Метод запускается перед сохранением. 
	 * Запрещает повторное голосование за один и тот же элемент.
	 * @param CModelEvent $event
	 */
	public function checkExist($event)
	{
		if (Voting::model()->exists('author_id = :author_id AND model_id = :model_id', array('author_id' => $this->author_id, 'model_id' => $this->model_id)) ) {
			$event->isValid = false;
		}
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return Voting the static model class
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
		return 'voting';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('author_id, model_id', 'required'),
			array('author_id, model_id, mark, create_time', 'numerical', 'integerOnly'=>true),
			array('model', 'length', 'max'=>45),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('author_id, model_id, model, mark, create_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'author_id' => 'Author',
			'model_id' => 'Model',
			'model' => 'Model',
			'mark' => 'Mark',
			'create_time' => 'Create Time',
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

		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('mark',$this->mark);
		$criteria->compare('create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	
	/**
	 * Вычисляет средний рейтинг для переданной модели 
	 * 
	 * @param ActiveRecord $model Модель для которой надо подсчитать рейтинг
	 * @return integer Средний рейтинг за модель.
	 */
	public static function getAverageRating($model)
	{
		$model = Voting::model()->find(new CDbCriteria(array(
			'select'	=> 'COUNT(*) as cnt_mark, SUM(mark) as mark',
			'condition'	=> 'model = :model AND model_id = :model_id AND mark <> 0',
			'params'		=> array(':model' => get_class($model), ':model_id' => $model->id ),
		)));
		if ($model && $model->cnt_mark != 0)
			$average = round($model->mark / $model->cnt_mark, 1);
		else 
			$average = 0;
		
		return $average;
	}
	
	/**
	 * Выдает для конкретного пользователя оценку за указанную модель.
	 * Если ее нет, возваращает 0.
	 * @param integer $author_id ID пользователя для которого нужно взять город
	 * @param integer $model_id ID моделя за которую нужно взять голос
	 * @param string $model название модели (Interior)
	 * @return integer 
	 */
	public static function getMark($author_id, $model_id, $model)
	{
		$vote = Voting::model()->findByPk(array('author_id' => $author_id, 'model_id' => $model_id, 'model' => $model));
		$mark = ($vote) ? $vote->mark : 0;
		
		return $mark;
	}
	
		
}