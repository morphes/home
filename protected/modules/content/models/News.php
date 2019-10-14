<?php

/**
 * This is the model class for table "news".
 *
 * The followings are the available columns in table 'news':
 * @property integer $id
 * @property integer $status
 * @property integer $public_time
 * @property integer $author_id
 * @property string $title
 * @property string $content
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property User $user
 */
class News extends EActiveRecord implements IComment
{
        
        const UPLOAD_IMAGE_DIR = 'uploads/public/news/';
        
        /**
         * Frontend page size (qt of news)
         */
        const PAGE_SIZE = 20;
        
        /**
         * News is not active and not published
         */
        const STATUS_INACTIVE = 0;

        /**
         * News is active
         */
        const STATUS_ACTIVE = 1;

        /**
         * Labels for status const's
         * @var array 
         */
        static public $status = array(
            self::STATUS_INACTIVE => 'Неактивна',
            self::STATUS_ACTIVE => 'Активна',
        );

        public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
                $this->onBeforeSave = array($this, 'setPublicTime');
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
         * Setter for public_time attribute.
         * (Working if news is active and empty public_time)
         */
        public function setPublicTime()
        {
                if (empty($this->public_time))
                        $this->public_time = time();         
        }

        /**
         * Returns the static model of the specified AR class.
         * @return News the static model class
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
                return 'news';
        }

        /**
         * @return array validation rules for model attributes.
         */
        public function rules()
        {
                // NOTE: you should only define rules for those attributes that
                // will receive user inputs.
                return array(
                    array('author_id, title, content, status', 'required'),
                    array('status, public_time, author_id, create_time, update_time', 'numerical', 'integerOnly' => true),
                    array('title', 'length', 'max' => 500),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('id, status, public_time, author_id, title, content, create_time, update_time', 'safe', 'on' => 'search'),
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
                    'user' => array(self::BELONGS_TO, 'User', 'author_id'),
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
                    'public_time' => 'Дата публикации',
                    'author_id' => 'Автор',
                    'title' => 'Заголовок',
                    'content' => 'Новость',
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
                $criteria->compare('status', $this->status);
                $criteria->compare('public_time', $this->public_time);
                $criteria->compare('author_id', $this->author_id);
                $criteria->compare('title', $this->title, true);
                $criteria->compare('content', $this->content, true);
                $criteria->compare('create_time', $this->create_time);
                $criteria->compare('update_time', $this->update_time);

                return new CActiveDataProvider($this, array(
                            'criteria' => $criteria,
                        ));
        }

	public function getCommentName()
	{
		return Amputate::getLimb($this->title, 40, '...');
	}
	
	// Предыдущая новость
	public function getPrevNews()
	{
		$criteria = new CDbCriteria();
		$criteria->condition = 'status=:status AND public_time<=:time AND public_time <= UNIX_TIMESTAMP() AND id<>:id';
		$criteria->limit = 1;
		$criteria->order = 'public_time DESC, id DESC';
		$criteria->params = array(
		    ':status' => News::STATUS_ACTIVE,
		    ':time' => $this->public_time,
		    ':id' => $this->id,
		);
		return $this->find($criteria);
	}
	
	// Следующая новость
	public function getNextNews()
	{
		$criteria = new CDbCriteria();
		$criteria->condition = 'status=:status AND public_time>=:time AND public_time <= UNIX_TIMESTAMP() AND id<>:id';
		$criteria->limit = 1;
		$criteria->order = 'public_time ASC, id ASC';
		$criteria->params = array(
		    ':status' => News::STATUS_ACTIVE,
		    ':time' => $this->public_time,
		    ':id' => $this->id,
		);
		return $this->find($criteria);
	}
	
	/**
         * Проверка владения моделью
         * @author Alexey Shvedov
         */
        public function getIsOwner()
        {
                return $this->author_id === Yii::app()->user->id;
        }

        /**
         * Обработчик события комментирования текущего объекта.
         * @param $comment Comment
         * @return Array
         */
        public function afterComment($comment)
        {
                $this->count_comment = Comment::getCountComments($this);
                $this->save(false);

                return array(0, $this->count_comment);
        }

	public function getCommentsVisibility()
	{
		return $this->status == self::STATUS_ACTIVE;
	}

	/** Ссыслка на страницу модели(с комментариями) */
	public function getElementLink()
	{
		return '/content/news/view/id/'.$this->id;
	}
}