<?php

/**
 * @brief This is the model class for table "content".
 *
 * The followings are the available columns in table 'content':
 * @property integer $id
 * @property integer $sharebox
 * @property string $alias
 * @property string $menu_key
 * @property integer $category_id
 * @property integer $author_id
 * @property integer $status
 * @property string $title
 * @property string $desc
 * @property string $content
 * @property string $meta_title
 * @property string $meta_desc
 * @property string $meta_keyword
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property ContentCategory $category
 * @property User $author
 */
class Content extends EActiveRecord
{
        const UPLOAD_IMAGE_DIR = 'uploads/public/content/';
        
        const STATUS_ACTIVE = 2;
        const STATUS_NOT_ACTIVE = 1;
        const STATUS_DELETED = 0;
        
        static public $statuses = array(
            self::STATUS_NOT_ACTIVE => 'Не активна',
            self::STATUS_ACTIVE => 'Активна',
        );
	
	const SHAREBOX_ENABLED = 1;
	const SHAREBOX_DISABLED = 0;
	static public $shareboxStatus = array(
	    self::SHAREBOX_ENABLED => 'Включено',
	    self::SHAREBOX_DISABLED => 'Выключено',
	);

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setAlias');
                $this->onBeforeSave = array($this, 'setDate');
	}
        
        /**
	 * Save alias, if it empty
	 */
	public function setAlias()
	{
		if(empty($this->alias)){
                        $this->alias = Amputate::rus2route(Amputate::getLimb($this->title, 20, ''));
                }	
	}
	
	/**
	 * Update create_time and update_time in object
	 */
	public function setDate()
	{
		if($this->isNewRecord)
			$this->create_time=$this->update_time=time();
		else
			$this->update_time=time();
	}
        
	/**
	 * Returns the static model of the specified AR class.
	 * @return Content the static model class
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
		return 'content';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('category_id, author_id, title, content', 'required'),
			array('category_id, author_id, status, create_time, update_time, sharebox', 'numerical', 'integerOnly'=>true),
			array('alias', 'length', 'max'=>100),
                        array('menu_key, meta_title, meta_desc, meta_keyword', 'length', 'max'=>255),
                        array('alias', 'unique', 'className'=>'Content', 'attributeName'=>'alias'),
			array('title', 'length', 'max'=>300),
			array('desc', 'length', 'max'=>1000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, category_id, author_id, status, alias, menu_key, title, desc, content, create_time, update_time', 'safe', 'on'=>'search'),
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
			'author' => array(self::BELONGS_TO, 'User', 'author_id'),
			'category' => array(self::BELONGS_TO, 'ContentCategory', 'category_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'category_id' => 'Категория',
			'author_id' => 'Автор',
			'status' => 'Статус',
			'alias' => 'Синоним в URL',
			'menu_key' => 'Ключ пункта меню для подсветки',
			'title' => 'Заголовок',
			'desc' => 'Описание',
			'content' => 'Содержание',
                        'meta_title' => 'Title',
			'meta_desc' => 'Description',
			'meta_keyword' => 'Keywords',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
			'sharebox' => '"Поделиться с друзьями"',
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
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('status',$this->status);
		$criteria->compare('alias',$this->alias,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('content',$this->content,true);
                $criteria->compare('meta_title',$this->meta_title,true);
		$criteria->compare('meta_desc',$this->meta_desc,true);
		$criteria->compare('meta_keyword',$this->meta_keyword,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Возвращает содержательную часть статической страницы,
	 * по переданному параметру $alias.
	 * 
	 * @param string $alias Строковой идентификатор статической страницы
	 * @return string HTML контент статической страницы 
	 */
	public static function getContentByAlias($alias)
	{
		$html = '';
		
		$model = Content::model()->findByAttributes(array('alias' => $alias));
		if ($model) {
			$html = $model->content;
		}
		
		return $html;
	}
}