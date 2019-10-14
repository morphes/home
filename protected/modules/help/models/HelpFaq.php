<?php

/**
 * This is the model class for table "help_faq".
 *
 * The followings are the available columns in table 'help_faq':
 * @property integer $id
 * @property integer $status
 * @property integer $author_id
 * @property integer $base_path_id
 * @property integer $position
 * @property string $link
 * @property string $question
 * @property integer $create_time
 * @property integer $update_time
 */
class HelpFaq extends EActiveRecord
{
	const STATUS_OPEN = 1;
	const STATUS_HIDE = 2;
	const STATUS_DELETED = 3;
	public static $statusNames = array(
		self::STATUS_OPEN => 'Открыт',
		self::STATUS_HIDE => 'Скрыт',
		self::STATUS_DELETED => 'Удален',
	);

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}

	public function setDate()
	{
		if ($this->getIsNewRecord())
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return HelpFaq the static model class
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
		return 'help_faq';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('status, link, question', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('link', 'length', 'max'=>255),
			array('question', 'length', 'max'=>512),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'status' => 'Статус',
			'author_id' => 'Author',
			'position' => 'Position',
			'link' => 'Ссылка на главу',
			'question' => 'Вопрос',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
		);
	}
}