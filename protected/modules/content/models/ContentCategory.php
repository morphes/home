<?php

/**
 * @brief This is the model class for table "content_category".
 *
 * The followings are the available columns in table 'content_category':
 * @property integer $id
 * @property integer $left_key
 * @property integer $right_key
 * @property integer $level
 * @property integer $author_id
 * @property integer $status
 * @property string $title
 * @property string $alias
 * @property string $desc
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Content[] $contents
 * @property User $author
 */
class ContentCategory extends EActiveRecord
{
        
        public $node_id = null;

        const STATUS_NOT_ACTIVE = 0;
        const STATUS_ACTIVE = 1;
        const STATUS_DELETED = 3;

        static public $statuses = array(
            self::STATUS_NOT_ACTIVE => 'Не активна',
            self::STATUS_ACTIVE => 'Активна',
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
        
        static public function checkRoot()
        {
                if(!self::model()->roots()->find()){
                        $root = new self();
                        $root->status = 1;
                        $root->author_id = Yii::app()->user->id;
                        $root->saveNode(true);
                }
        }


        
	/**
	 * Returns the static model of the specified AR class.
	 * @return ContentCategory the static model class
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
		return 'content_category';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('author_id, status, title, node_id', 'required', 'on'=>array('create', 'update')),
                        array('left_key, right_key, level, status', 'required', 'on'=>'insertRoot'),
			array('left_key, right_key, level, author_id, status, node_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>200),
			array('alias, desc', 'length', 'max'=>100),
                        array('alias', 'unique', 'className'=>'Content', 'attributeName'=>'alias'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, left_key, right_key, level, author_id, status, title, alias, desc, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}
        
        public function behaviors()
	{
		return array(
                        'NestedSetBehavior'=>array(
                                'class'=>'ext.behaviors.NestedSetBehavior',
                                'leftAttribute'=>'left_key',
                                'rightAttribute'=>'right_key',
                                'levelAttribute'=>'level',
                        ),
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
			'contents' => array(self::HAS_MANY, 'Content', 'category_id'),
			'author' => array(self::BELONGS_TO, 'User', 'author_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'left_key' => 'Left Key',
			'right_key' => 'Right Key',
			'level' => 'Level',
                        'node_id' => 'Родитель',
			'author_id' => 'Author',
			'status' => 'Статус',
			'title' => 'Название',
			'alias' => 'Синоним в URL',
			'desc' => 'Описание',
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
		$criteria->compare('left_key',$this->left_key);
		$criteria->compare('right_key',$this->right_key);
		$criteria->compare('level',$this->level);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('status',$this->status);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('alias',$this->alias,true);
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}