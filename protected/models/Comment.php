<?php

/**
 * @brief This is the model class for table "comment".
 *
 * @details The followings are the available columns in table 'comment':
 * @property integer $id
 * @property integer $parent_id
 * @property integer $author_id
 * @property integer $guest_id
 * @property integer $status
 * @property string $model
 * @property integer $model_id
 * @property string $message
 * @property integer $create_time
 * @property integer $author_ip
 */
class Comment extends EActiveRecord implements IActivity
{
	public $formated_create_time;
	private $_linkedModel=null;

	/**
	 * Сообщение открыто
	 */
	const ACTIVE = 1;

	/**
	 * Сообщение закрыто
	 * и находиться на
	 * модерации
	 */
	const ON_MODERATE = 2;

	/**
	 * Отклонено модератором
	 */
	const REJECTED = 3;


	public function init()
        {
                parent::init();
		Yii::import('application.modules.content.models.News');
		Yii::import('application.modules.idea.models.Interior');
		Yii::import('application.modules.media.models.MediaNew');
		Yii::import('application.modules.media.models.MediaKnowledge');
		Yii::import('application.modules.media.models.MediaEvent');
		Yii::import('application.modules.catalog.models.Product');
		Yii::import('application.modules.catalog.models.StoreNews');

                $this->onBeforeSave = array($this, 'setDate');	
		$this->onAfterFind = array($this, 'getDate');
		$this->onAfterDelete = array($this, 'recountComments');
		$this->onAfterSave = array($this, 'recountComments');

                // пересчет комментариев пользователя
                $this->onAfterSave = array($this, 'userCommentCount');
                $this->onAfterDelete = array($this, 'userCommentCount');
		// Создание/удаление активности  юзера
		$this->onAfterSave = array($this, 'createActivity');
		$this->onAfterDelete = array($this, 'deleteActivity');
        }

	public function createActivity()
	{
		if(!$this->guest_id)
		{
			if ($this->getIsNewRecord())
				Activity::createActivity($this);
		}
	}

	public function deleteActivity()
	{

		if(!$this->guest_id)
		{
			Activity::deleteActivity($this);

		}
	}

	public static $statusLabels = array(
		self::ACTIVE       => 'Активно',
		self::ON_MODERATE => 'На модерации',
		self::REJECTED => 'Отклонено',
	);


        /**
         * Пересчет кол-ва всех комментариев пользователя
         * Запускается после сохранения или удаления объекта текущей модели
         */
        public function userCommentCount()
        {
                if(!$this->guest_id)
		{
			$comment_count = self::model()->count('author_id=:uid', array(':uid'=>Yii::app()->user->id));
			UserData::model()->updateByPk(Yii::app()->user->id, array('comment_count' => $comment_count));
			Yii::app()->user->model->countActivityRating();

		}
        }

        /**
         * Update create_time and update_time in object
         */
        public function setDate()
        {
                if ($this->isNewRecord)
                        $this->create_time = time();
		
        }
	
	public function getDate()
	{
		$this->formated_create_time = date("d.m.Y H:i", $this->create_time);
	}
	
	public function recountComments()
	{
		$model = $this->getLinkedModel();
		if ($model) {
			$class = get_class($model);
			$count_comment = Comment::getCountComments($model);

			// Обнволяем количество коментов и средний рейтинг
			$class::model()->updateByPk($model->id, array(
				'count_comment'  => $count_comment,
			));
		}
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return Comment the static model class
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
		return 'comment';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('parent_id, author_id, model_id, message', 'required'),
			array('parent_id, author_id, status, model_id, author_ip, create_time, guest_id', 'numerical', 'integerOnly'=>true),
			array('model', 'length', 'max'=>45),
			array('message', 'length', 'max'=>2000),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, parent_id, author_id, status, guest_id, model, author_ip, model_id, message, create_time', 'safe', 'on'=>'search'),
		);
	}

	/**
         * @return array relational rules.
         */
        public function relations()
        {
		return array(

                );

        }


	/**
	 * @return array|CActiveRecord|mixed|null|User
	 * Функция для связывания свойства объекта author
	 * с модель. User
	 */
	public function getAuthor()
	{
		if($this->guest_id)
		{
			$userModel= new User();
			$userModel -> login = 'Guest';
			$userModel -> firstname = 'Гость';
			return $userModel;

		}
		return  User::model()->findByPk($this->author_id);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'parent_id'	=> 'Parent',
			'author_id'	=> 'Автор',
			'model'		=> 'Имя модели',
			'model_id'	=> 'ID элемента модели',
			'message'	=> 'Комментарий',
			'create_time'	=> 'Дата создания',
			'status'	=> 'Статус',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;
		$criteria->compare('id',$this->id);
		$criteria->compare('parent_id',$this->parent_id);
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('model_id',$this->model_id);
		if($this->status!=null)
		{
			$criteria->compare('status',$this->status);

		}
		$criteria->compare('message',$this->message,true);
		$criteria->compare('create_time',$this->create_time);
		
		$criteria->order = 'create_time DESC';

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination' => array(
				'pageSize' => 20
			)
		));
	}
	
	/**
	 * Возвращает имя экземпляра того элемента, за
	 * который оставлен комент.
	 * @return string Имя экземпляра модели 
	 */
	public function getElementName()
	{
		$model = $this->getLinkedModel();
		// Имя элемента
		$name = '';
		if ($model) {
			if (method_exists($model, 'getCommentName')) {
				$name = $model->getCommentName();
			} else if ( ! empty($model->name))
				$name = $model->name;
			else
				$name = $model->id;
		}
		return $name;
	}
	
	/**
	 * Возваращает ссылку на тот экземпляр элемента, за 
	 * который оставлен комент. Ссылка ведет на фронтенд.
	 * @return string
	 */
	public function getElementLink()
	{
		$model = $this->getLinkedModel();
		if ($model instanceof CModel) {
			return $model->getElementLink();
		} else {
			return '#';
		}

	}
	
	/**
	 * Gets quantity of comments for $model
	 * 
	 * @param IComment $model The instance of ActiveRecord object
	 * @return integet Quantity of comments. 
	 */
	public static function getCountComments($model)
	{
		$count = Comment::model()->countByAttributes(array('model' => get_class($model), 'model_id' => $model->id, 'status' => self::ACTIVE));
		
		return ($count) ? (int)$count : 0;
	}
	
	/**
	 * Возвращает связанную с комментарием модель
	 * @return IComment
	 */
	public function getLinkedModel()
	{
		if (is_null($this->_linkedModel)) {
			$class = $this->model;
			$this->_linkedModel = $class::model()->findByPk($this->model_id);
		}
		return $this->_linkedModel;
	}

	/**
	 * Возвращает фрагмент активности для вывода
	 * @return string
	 */
	public function renderActivityItem($user)
	{
		return Yii::app()->getController()->renderPartial('//member/profile/_activity/_comment', array('comment'=>$this, 'user'=>$user), true);
	}

	/**
	 * Получение ID автора
	 * @return mixed
	 */
	public function getAuthorId()
	{
		return $this->author_id;
	}


}