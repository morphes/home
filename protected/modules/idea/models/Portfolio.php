<?php

/**
 * This is the model class for table "portfolio".
 *
 * The followings are the available columns in table 'portfolio':
 * @property integer $id
 * @property integer $author_id
 * @property integer $service_id
 * @property string $name
 * @property string $desc
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $count_comment
 * @property integer $count_photos
 * @property integer $average_rating
 *
 * The followings are the available model relations:
 * @property User $author
 * @property UploadedFile[] $uploadedFiles
 */
class Portfolio extends EActiveRecord implements IProject, IComment, IUploadImage
{
        const STATUS_MAKING = 1; // В процессе
        const STATUS_MODERATING = 2; // На модерации
        const STATUS_ACCEPTED = 3; // Принят в идеи
        const STATUS_REJECTED = 4; // Не допущен в идеи
        const STATUS_DELETED = 5; // Удален
        const STATUS_VERIFIED = 6; // Проверен Junior'ом
        const STATUS_CHANGED = 7; // Изменен после публикации

	// Список имен статусов
	public static $statusNames = array(
		self::STATUS_MAKING 	=> 'В процессе',
		self::STATUS_MODERATING => 'Сохранен',
		self::STATUS_DELETED	=> 'Удален',
	);

        private $_images;
	private $_serviceId; // Id услуги до обновлений

	public static $preview = array(
		'crop_80' => array(80, 80, 'crop', 80), // in view
		'crop_131' => array(131, 131, 'crop', 100), // in update page
		'crop_210' => array(210, 210, 'crop', 80), // preview
		'resize_710x475' => array(710, 475, 'resize', 90), // in view
		'resize_1920x1080' => array(1920, 1080, 'resize', 90, 'watermark' => true, 'decrease' => true), // in view
	);

	// Тип изображения для загрузки
	private $_imageType = null;

        /**
         * Returns the static model of the specified AR class.
         * @param string $className active record class name.
         * @return Portfolio the static model class
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
                return 'portfolio';
        }

        public function init()
        {
                parent::init();
                $this->onBeforeSave = array($this, 'setDate');
                $this->onBeforeSave = array($this, 'updatePhotoCount');

		$this->onAfterSave = array($this, 'initSorting');
		$this->onAfterFind = array($this, 'storeService');
                $this->onAfterSave = array($this, 'updateProjectCountByService');
                $this->onAfterSave = array($this, 'countUserServiceRating'); // расчет рейтинга специалиста по текущей услуге
        }

	/**
	 * Подключение сортировки проектов
	 */
	public function initSorting()
	{
		if (!$this->getIsNewRecord())
			return;

		$sorting = new PortfolioSort();
		$sorting->item_id = $this->id;
		$sorting->user_id = $this->author_id;
		$sorting->idea_type_id = Config::PORTFOLIO;
		$sorting->service_id = $this->service_id;
		$sorting->position = 1;
		$sorting->update_time = time();
		$sorting->save(false);

	}

        public function afterSave()
        {
                parent::afterSave();

                // Обновление кол-ва проектов пользователя
                $project_quantity = Interior::model()->scopeOwnPublic($this->author_id)->count();
		$project_quantity += Interiorpublic::model()->scopeOwnPublic($this->author_id)->count();
                $project_quantity+= Portfolio::model()->scopeOwnPublic($this->author_id)->count();
		$project_quantity+= Architecture::model()->scopeOwnPublic($this->author_id)->count();
                UserData::model()->updateByPk($this->author_id, array('project_quantity' => $project_quantity));
        }

	public function storeService()
	{
		$this->_serviceId = $this->service_id;
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
         * Обновление кол-ва проектов в текущей услуге
         */
        public function updateProjectCountByService()
        {
                $project_qt = self::model()->scopeOwnPublic($this->author_id, $this->service_id)->count();

                Yii::app()->db->createCommand()->update('user_service_data', array(
                        'project_qt'=>$project_qt,
                ), 'user_id=:uid AND service_id=:sid', array(':uid'=>$this->author_id, ':sid'=>$this->service_id));

		// При изменении ID услуги
		if (!is_null($this->_serviceId) && $this->_serviceId != $this->service_id) {
			$project_qt = self::model()->scopeOwnPublic($this->author_id, $this->_serviceId)->count();

			Yii::app()->db->createCommand()->update('user_service_data', array(
				'project_qt'=>$project_qt,
			), 'user_id=:uid AND service_id=:sid', array(':uid'=>$this->author_id, ':sid'=>$this->_serviceId));
		}

                /**
                 * Если проект создан со статусом "в процессе", то наращивается счетчик черновиков
                 */
                if($this->isNewRecord && $this->status == self::STATUS_MAKING) {
                        $user = $this->author;
                        $user->data->draft_qt++;
                        $user->data->save();
                }
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
         * Update create_time and update_time in object
         */
        public function updatePhotoCount()
        {
                if ($this->isNewRecord)
                        $this->count_photos = 0;
                else
                        $this->count_photos = PortfolioUploadedfile::model()->countByAttributes(array('item_id'=>$this->id));
        }

        /**
         * @return array validation rules for model attributes.
         */
        public function rules()
        {
                // NOTE: you should only define rules for those attributes that
                // will receive user inputs.
                return array(
                        array('author_id, status, service_id', 'required'),
                        // Публикация портфолио
                        array('name', 'required', 'on' => 'create'),
                        // Проверка минимально допустимого кол-ва фото в портфолио
                        array('images', 'imageCount', 'min' => 1, 'on' => 'create'),
                        array('author_id, service_id, status, create_time, update_time', 'numerical', 'integerOnly' => true),
                        array('name', 'length', 'max' => 45),
                        array('desc', 'length', 'max' => 2000),
                        // The following rule is used by search().
                        // Please remove those attributes that should not be searched.
                        array('id, author_id, service_id, name, desc, status, count_comment, count_photos, average_rating, create_time, update_time', 'safe', 'on' => 'search'),
                );
        }

        /**
         * Валидатор, проверяющий кол-во изображений у проекта
         */
        public function imageCount($attribute, $params)
        {
               if(count($this->$attribute) < $params['min'])
                       $this->addError($attribute, "Для публикации требуется не менее {$params['min']} " . CFormatterEx::formatNumeral($params['min'], array('изображения', 'изображений', 'изображений'), true));
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
                        'uploadedFiles' => array(self::MANY_MANY, 'UploadedFile', 'portfolio_uploadedfile(item_id, file_id)'),
                        'service' => array(self::BELONGS_TO, 'Service', 'service_id'),
                );
        }

        /**
         * @return array customized attribute labels (name=>label)
         */
        public function attributeLabels()
        {
                return array(
                        'id' => 'ID',
                        'author_id' => 'Автор',
                        'service_id' => 'Услуга',
                        'name' => 'Название проекта',
                        'desc' => 'Описание проекта',
                        'status' => 'Статус',
                        'create_time' => 'Дата создания',
                        'update_time' => 'Дата обновления',
                        'images' => 'Изображения проекта',
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
                $criteria->compare('author_id', $this->author_id);
                $criteria->compare('service_id', $this->service_id);
                $criteria->compare('name', $this->name, true);
                $criteria->compare('desc', $this->desc, true);
                $criteria->compare('status', $this->status);
                $criteria->compare('count_comment', $this->count_comment);
                $criteria->compare('count_photos', $this->count_photos);
                $criteria->compare('average_rating', $this->average_rating);
                $criteria->compare('create_time', $this->create_time);
                $criteria->compare('update_time', $this->update_time);

                return new CActiveDataProvider($this, array(
                                'criteria' => $criteria,
                        ));
        }

        public function getImages()
        {
                if (is_null($this->_images) && !$this->isNewRecord) {
                        $this->_images = PortfolioUploadedfile::model()->findAllByAttributes(array('item_id' => $this->id));
                }
                return $this->_images;
        }

        /**
         * Get first image for project
         * @param $config array(width, height)
         * @return string URL
         */
        public function getPreview($config)
        {
                if($this->images && isset($this->images[0])) {

                        $image = $this->images[0]->file->getPreviewName($config);

                        if (file_exists($image))
                                return $image;
                }
		$size = $config[0] . 'x' . $config[1];
		
		return UploadedFile::getDefaultImage('portfolio', $size);
        }


        /**
         * Именованное условие для выборки данных.
         * Выбирает проекты только с разрешенными статусами.
         * Если не передать $user_id, выбираются интерьеры для текущего
         * пользователя (Yii::app()->user)
         *
         * @param interger $user_id Идентификатор пользователя
         * @return Interior
         */
        public function scopeOwnPublic($user_id = null, $sid = null)
        {
                if (is_null($user_id))
                        $user_id = Yii::app()->user->id;

		$params = array(
			':author_id' => $user_id,
			':st1' => self::STATUS_MODERATING,
			':st2' => self::STATUS_ACCEPTED,
			':st3' => self::STATUS_REJECTED,
			':st4' => self::STATUS_CHANGED,
			':st5' => self::STATUS_VERIFIED,
		);

		$criteria = new CDbCriteria();
		$criteria->order = 'create_time ASC';
		$criteria->limit = 1000;

		if (!$sid) {
			$criteria->condition = 'author_id=:author_id
                                        AND (status = :st1 OR status = :st2 OR status = :st3 OR status = :st4 OR status = :st5)';
		} else {
			$criteria->condition = 'author_id=:author_id AND service_id=:sid
                                        AND (status = :st1 OR status = :st2 OR status = :st3 OR status = :st4 OR status = :st5)';
			$params[':sid'] = intval($sid);
		}
		$criteria->params = $params;

		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
        }

        /**
         * Проверка отображения комментариев
         * @return boolean
         * @author Alexey Shvedov
         */
        public function getCommentsVisibility()
        {
                return $this->status != self::STATUS_MAKING;
        }

        /**
         * Ссылка на редактирование текущего проекта
         */
        public function getUpdateLink()
        {
                return Yii::app()->createUrl("/users/{$this->author->login}/portfolio/create/{$this->id}");
        }

        public function getDeleteLink()
        {
                return Yii::app()->createUrl("/idea/portfolio/delete/id/{$this->id}");
        }

        /**
         * Вызов расчета рейтинга специалиста по текущей услуге
         */
        public function countUserServiceRating()
        {
		Yii::app()->gearman->appendJob('userService', array('userId'=>$this->author_id, 'serviceId'=>$this->service_id));
        }


	public function getServiceName($id)
	{
		$model = Service::model()->findByPk((int)$id);
		if ($model)
			$name = $model->name;
		else
			$name = 'неизвестно';

		return $name;
	}

        /**
         * Обработчик события комментирования текущего объекта.
         * @param $comment Comment
         * @return Array
         */
        public function afterComment($comment)
        {
                /**
                 * Обновление рейтинга и кол-ва комментариев текущего объекта
                 */
                $count_comment = Comment::getCountComments($this);
                $average_rating = Voting::getAverageRating($this);
                self::model()->updateByPk($this->id, array(
                        'average_rating' => $average_rating,
                        'count_comment'  => $count_comment,
                ));

                /**
                 * Отправка уведомления о новом комментарии
                 */
                $author = User::model()->findByPk($this->author_id);
                $commentAuthor = User::model()->findByPk($comment->author_id);
                if (!is_null($author) && !is_null($commentAuthor) &&
                        $author->data->idea_comment_notify &&
                        in_array($author->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR))
                ) {
                        Yii::app()->mail->create('interiorCommentNotify')
                                ->to($author->email)
                                ->params(array(
                                        'user_name' => $this->author->name,
                                        'project_name' => $this->name,
                                        'author_name' 	=> $commentAuthor->name,
                                        'project_link' => 'users/'.$this->author->login.'/project/'.$this->service_id.'/'.$this->id,
                                        'sign_A' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage()
                                ))
                                ->send();
                }

                return array($average_rating, $count_comment);
        }

	// IUploadImage
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'portfolio': return 'idea'. '/' .intval($this->author_id / UploadedFile::PATH_SIZE + 1). '/' .$this->author_id.'/portfolio/'.$this->id;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'portfolio': return 'portfolio' . $this->id . '_' . mt_rand(1, 99) . '_'. time();
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getAuthorId()
	{
		return $this->author_id;
	}

	public function setImageType($name)
	{
		$this->_imageType = $name;
	}

	public function flushImageType()
	{
		$this->_imageType = null;
		return true;
	}

	public function checkAccess()
	{
		return !is_null($this->author_id) && $this->author_id == Yii::app()->user->id;
	}

	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'portfolio': return array(
				'realtime' => array(
					self::$preview['crop_131'],
					self::$preview['crop_210'],
				),
				'background' => array(
					self::$preview['crop_80'],
					self::$preview['resize_710x475'],
					self::$preview['resize_1920x1080'],
				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}
	// end of IUploadImage
	/**
	 * Название услуги проекта
	 * @return mixed
	 */
	public function getProjectType()
	{
		return self::getServiceName($this->service_id);
	}

	/** Ссыслка на страницу модели(с комментариями) */
	public function getElementLink()
	{
		return "/users/{$this->author->login}/project/".$this->service_id."/{$this->id}";
	}

	/**
	 * Получение системного типа объекта (например Config::INTERIOR)
	 * @return mixed
	 */
	public function getTypeId()
	{
		return Config::PORTFOLIO;
	}

	/**
	 * Получение объектов на изображения интерьера
	 * @return array
	 */
	public function getPhotos()
	{
		$criteria = new CDbCriteria();
		$criteria->join = 'INNER JOIN portfolio_uploadedfile as puf ON puf.file_id=t.id';
		$criteria->condition = 'puf.item_id=:id';
		$criteria->params = array(':id'=>$this->id);
		$criteria->index = 'id';
		return UploadedFile::model()->findAll($criteria);
	}
}