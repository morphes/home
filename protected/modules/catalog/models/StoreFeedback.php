<?php

/**
 * This is the model class for table "cat_store_feedback".
 *
 * The followings are the available columns in table 'cat_store_feedback':
 * @property integer $id
 * @property integer $store_id
 * @property integer $user_id
 * @property integer $parent_id
 * @property integer $mark
 * @property string $message
 * @property integer $create_time
 * @property integer $update_time
 */
class StoreFeedback extends CActiveRecord
{
        const  DEFAULT_PAGESIZE = 10;

        public $feedback; // аттрибут определяет заполненность отзыва (см. $this->anyOneRequired())

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return StoreFeedback the static model class
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
		return 'cat_store_feedback';
	}

        public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
                $this->onAfterSave = array($this, 'updateVoting');
                $this->onAfterSave = array($this, 'updateAverageRating');
        }

        public function setDate()
        {
                if ($this->isNewRecord)
                        $this->create_time = $this->update_time = time();
                else
                        $this->update_time = time();
        }

        /**
         * Обновление таблицы голосований после отзыва о товаре
         */
        public function updateVoting()
        {
                $voting = new Voting();
                $voting->author_id = $this->user_id;
                $voting->mark = $this->mark;
                $voting->model = 'Store';
                $voting->model_id = $this->store_id;
                $voting->save();
        }

        /**
         * Обновление среднего рейтинга товара
         */
        public function updateAverageRating()
        {
                $marks = Yii::app()->db->createCommand()->select('mark')->from($this->tableName())
                        ->where('store_id=:sid', array(':sid'=>$this->store_id))->queryAll();
                $mark_count = 0;
                $mark_summ = 0;
                foreach($marks as $mark) {
                        $mark_count++;
                        $mark_summ+=$mark['mark'];
                }
                $store = Store::model()->findByPk($this->store_id);
                $store->average_rating = round($mark_summ / $mark_count);
                $store->save(false, array('average_rating'));
        }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('store_id, user_id, parent_id, message', 'required', 'on'=>'feedback, answer'),
			array('mark', 'required', 'on'=>'feedback'),
			array('store_id, user_id, mark, parent_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('message', 'length', 'max'=>3000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, store_id, user_id, parent_id, mark, message, create_time, update_time', 'safe', 'on'=>'search'),
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
                        'author' => array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
                return array(
			'id'          => 'ID',
			'store_id'    => 'Магазин',
			'user_id'     => 'Автор',
			'parent_id'   => 'Ответ на',
			'mark'        => 'Оценка',
			'message'     => 'Ваш отзыв',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
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

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('mark', $this->mark);
		$criteria->compare('store_id', $this->store_id);
		$criteria->compare('user_id', $this->user_id);

		// Дата начала регистрации
		if (($date_from = Yii::app()->request->getParam('date_from'))) {
			$criteria->compare('create_time', '>=' . strtotime($date_from));
		}

		// Дата окончания регистрации
		if (($date_to = Yii::app()->request->getParam('date_to'))) {
			$criteria->compare('create_time', '<' . strtotime('+1 day', strtotime($date_to)));
		}

		$sort = new CSort();
		$sort->defaultOrder = array('create_time' => 'DESC');



		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'sort'     => $sort
		));
	}

}