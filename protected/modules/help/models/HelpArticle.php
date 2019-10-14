<?php

/**
 * This is the model class for table "help_article".
 *
 * The followings are the available columns in table 'help_article':
 * @property integer $id
 * @property integer $status
 * @property integer $author_id
 * @property integer $section_id
 * @property integer $base_path_id
 * @property integer $position
 * @property string $name
 * @property string $description
 * @property string $data
 * @property integer $create_time
 * @property integer $update_time
 */
class HelpArticle extends EActiveRecord
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
	 * @return HelpArticle the static model class
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
		return 'help_article';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('status, name', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			//array('description', 'length', 'max'=>2048), // закомментировано, не используется временно
			array('data', 'safe'),
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
			'section_id' => 'Section',
			'position' => 'Position',
			'name' => 'Название',
			'data' => 'Описание',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
		);
	}

	/**
	 * Число открытых глав данной статьи
	 * @return string
	 */
	public function getChapterCount()
	{
		return HelpChapter::model()->countByAttributes(array('article_id'=>$this->id, 'status'=>HelpChapter::STATUS_OPEN));
	}

	public function getFrontLink()
	{
		return Yii::app()->getController()->createUrl('/help/help/article', array('article_id'=>$this->id, 'baseId'=>$this->base_path_id) );
	}
}