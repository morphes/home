<?php

/**
 * Класс для отзывов пользователей к товарам каталога
 *
 * The followings are the available columns in table 'cat_feedback':
 * @property integer $id
 * @property integer $product_id
 * @property integer $parent_id
 * @property integer $user_id
 * @property integer $mark
 * @property string  $merits
 * @property string  $limitations
 * @property string  $message
 * @property integer $create_time
 * @property integer $update_time
 */
class Feedback extends EActiveRecord
{
	const  DEFAULT_PAGESIZE = 10;

	public $feedback; // аттрибут определяет заполненность отзыва (см. $this->anyOneRequired())

	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}


	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
		$this->onAfterValidate = array($this, 'anyOneRequired');
		$this->onAfterSave = array($this, 'updateVoting');
		$this->onAfterSave = array($this, 'updateAverageRating');
	}


	public function setDate()
	{
		if ($this->isNewRecord) {
			$this->create_time = $this->update_time = time();
		} else {
			$this->update_time = time();
		}
	}


	/**
	 * Обновление таблицы голосований после отзыва о товаре
	 */
	public function updateVoting()
	{
		$voting = new Voting();
		$voting->author_id = $this->user_id;
		$voting->mark = $this->mark;
		$voting->model = 'Product';
		$voting->model_id = $this->product_id;
		$voting->save();
	}


	/**
	 * Обновление среднего рейтинга товара
	 */
	public function updateAverageRating()
	{
		$marks = Yii::app()->db->createCommand()
			->select('mark')
			->from($this->tableName())
			->where('product_id=:pid and parent_id is null', array(':pid' => $this->product_id))
			->queryAll();
		$mark_count = 0;
		$mark_summ = 0;
		foreach ($marks as $mark) {
			$mark_count++;
			$mark_summ += $mark['mark'];
		}
		$product = Product::model()->findByPk($this->product_id);
		$product->average_rating = round($mark_summ / $mark_count);
		$product->save(false, array('average_rating'));
	}


	public function tableName()
	{
		return 'cat_feedback';
	}


	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('product_id, user_id', 'required'),
			array('mark', 'required', 'on' => 'feedback'),
			array('parent_id, message', 'required', 'on' => 'answer'),
			array('merits, limitations, message', 'anyOneRequired'),
			array('product_id, user_id, mark, create_time, update_time, parent_id', 'numerical', 'integerOnly' => true),
			array('merits, limitations, message', 'length', 'max' => 3000),
			array('id, product_id, parent_id, user_id, mark, merits, limitations, message, create_time, update_time', 'safe', 'on' => 'search'),
		);
	}


	public function relations()
	{
		return array(
			'author'  => array(self::BELONGS_TO, 'User', 'user_id'),
			'product' => array(self::BELONGS_TO, 'Product', 'product_id'),
		);
	}


	/**
	 * Валидатор полей, из которых хотя-бы одно должно быть заполнено
	 */
	public function anyOneRequired()
	{
		if (empty($this->merits) && empty($this->limitations) && empty($this->message)) {
			$this->addErrors(array('feedback' => 'Необходимо заполнить хотя-бы одно текстовое поле.'));
		}
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'product_id'  => 'Товар',
			'user_id'     => 'Автор',
			'mark'        => 'Оценка',
			'merits'      => 'Достоинства',
			'limitations' => 'Недостатки',
			'message'     => 'Отзыв',
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
		$criteria->compare('product_id', $this->product_id);
		$criteria->compare('user_id', $this->user_id);
		$criteria->compare('mark', $this->mark);
		$criteria->compare('merits', $this->merits, true);
		$criteria->compare('limitations', $this->limitations, true);
		$criteria->compare('message', $this->message, true);
		$criteria->compare('create_time', $this->create_time);
		$criteria->compare('update_time', $this->update_time);

		$sort = new CSort();
		$sort->defaultOrder = array('create_time' => 'DESC');

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'sort'     => $sort
		));
	}
}