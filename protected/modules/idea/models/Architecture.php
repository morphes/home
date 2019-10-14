<?php

/**
 * This is the model class for table "architecture".
 *
 * The followings are the available columns in table 'architecture':
 * @property integer $id
 * @property integer $author_id
 * @property integer $object_id
 * @property integer $building_type_id
 * @property integer $interior_id
 * @property integer $material_id
 * @property integer $style_id
 * @property integer $floor_id
 * @property integer $room_mansard
 * @property integer $room_garage
 * @property integer $room_ground
 * @property integer $room_basement
 * @property integer $status
 * @property string $name
 * @property string $desc
 * @property integer $image_id
 * @property string $tag
 * @property integer $create_time
 * @property integer $update_time
 */
class Architecture extends EActiveRecord implements IComment // /*IProject, */ /*, IUploadImage*/
{
	const REDIS_VIEW = 'architecture:view:';

	public static $sphinxWeight = array(
		'object' => 80,
		'style' => 40,
		'material' => 40,
		'floor' => 40,
		'color' => 40,
		'mansard' => 20,
		'ground' => 20,
		'basement' => 20,
		'garage' => 20,
	);

	const STATUS_MAKING = 1; // В процессе
	const STATUS_MODERATING = 2; // На модерации
	const STATUS_ACCEPTED = 3; // Принят в идеи
	const STATUS_REJECTED = 4; // Не допущен в идеи
	const STATUS_DELETED = 5; // Удален
	const STATUS_VERIFIED = 6; // Проверен Junior'ом
	const STATUS_CHANGED = 7; // Изменен после публикации
	const STATUS_TEMP_IMPORT = 8; // Временные статус для импортированных Архитектура из старого типа

	// ID услуги Архитектура (для подсветки в меню)
	const SERVICE_ID = 4;

	/**
	 * Задаем типы посторек для архитектуры. По этим типам
	 * потом различается вывод формы редактирования и назначение сценариев.
	 */
	const BUILD_TYPE_HOUSE 		= 1; // Дом, котедж, особняк
	const BUILD_TYPE_OUTBUILDING 	= 2; // Хозяйственные постройки
	const BUILD_TYPE_PUBLIC 		= 3; // Общественные здания

	public static $preview = array(
		'crop_80' => array(80, 80, 'crop', 80), // in view
		'crop_150' => array(150, 150, 'crop', 80), // in update page
		'crop_180' => array(180, 180, 'crop', 80), // страница просмотра на фронте(главная пикча)
		'crop_210' => array(210, 210, 'crop', 80), // preview
		'crop_230' => array(230, 230, 'crop', 80), // in update page
		'resize_710x475' => array(710, 475, 'resize', 90), // in view
		'resize_1920x1080' => array(1920, 1080, 'resize', 90, 'watermark' => true, 'decrease' => true), // in view
		'width_520' => array(520, 0, 'resize', 90, false), // просмотр идеи на фронте
	);

	// Список имен статусов
	public static $statusNames = array(
		self::STATUS_MAKING => 'В процессе',
		self::STATUS_MODERATING => 'На модерации',
		self::STATUS_REJECTED => 'Не допущен в идеи',
		self::STATUS_VERIFIED => 'Проверен',
		self::STATUS_ACCEPTED => 'Опубликован',
		self::STATUS_CHANGED => 'Опубликован, Изменен',
		self::STATUS_TEMP_IMPORT => 'Импортирован старый',
		// Статус скрыт, потомучто к нему нет доступа у пользователей
		//self::STATUS_DELETED	=> 'Удален'
	);
	public static $statusNamesForJuniors = array(
		self::STATUS_MAKING => 'В процессе',
		self::STATUS_MODERATING => 'На модерации',
		self::STATUS_REJECTED => 'Не допущен в идеи',
		self::STATUS_VERIFIED => 'Проверен',
		// Статус скрыт, потомучто к нему нет доступа у пользователей
		//self::STATUS_DELETED	=> 'Удален'
	);
	// Все возможные статусы
	public static $allStatusNames = array(
		self::STATUS_MAKING => 'В процессе',
		self::STATUS_MODERATING => 'На модерации',
		self::STATUS_REJECTED => 'Не допущен в идеи',
		self::STATUS_VERIFIED => 'Проверен',
		self::STATUS_ACCEPTED => 'Опубликован',
		self::STATUS_CHANGED => 'Опубликован, Изменен',
		self::STATUS_DELETED	=> 'Удален'
	);

	// Используется для проверки кол-ва добавленных изображений, и вывода ошибки.
	public $imagesCount = 0;

	// Используется для проверки корректности ввода данных соавторов, и вывода ошибки
	public $coauthors;

	private static $_service = null;


	public function init()
	{
		parent::init();

		$this->onBeforeSave = array($this, 'setDate');
		$this->onBeforeSave = array($this, 'setServiceId');

		$this->onAfterSave = array($this, 'initSorting');
		$this->onAfterSave = array($this, 'updateProjectCountByService');
		$this->onAfterSave = array($this, 'countUserServiceRating'); // расчет рейтинга специалиста по текущей услуге
		$this->onAfterSave = array($this, 'updateSphinx');
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
		$sorting->idea_type_id = Config::ARCHITECTURE;
		$sorting->service_id = $this->service_id;
		$sorting->position = 1;
		$sorting->update_time = time();
		$sorting->save(false);

	}

	public function updateSphinx()
	{
		Yii::app()->gearman->appendJob('sphinx:architecture', $this->id);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Architecture the static model class
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
		return 'architecture';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('author_id', 'required'),
			array('author_id, object_id, building_type_id, interior_id, status, image_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			array('desc', 'length', 'max'=>5000),
			array('tag', 'length', 'max'=>3000),
			// Дополнтиельные ограничения для "Дом, котедж, особняк"
			array(
				'name, image_id', 'required',
				'on' => 'edit_type_'.self::BUILD_TYPE_HOUSE
			),
			array(
				'style_id, material_id, color_id, floor_id', 'required',
				'on' => 'edit_type_'.self::BUILD_TYPE_HOUSE
			),
			array(
				'room_mansard, room_garage, room_ground, room_basement', 'boolean',
				'on' => 'edit_type_'.self::BUILD_TYPE_HOUSE
			),
			array(
				'color_id', 'validCheckColors', 'message' => 'Цвета помещения повторяются',
				'on' => 'edit_type_'.self::BUILD_TYPE_HOUSE
			),
			// Дополнтиельные ограничения для "Хозяйственные постройки"
			array(
				'name, image_id', 'required',
				'on' => 'edit_type_'.self::BUILD_TYPE_OUTBUILDING
			),
			array(
				'material_id', 'required', 'message' => 'Необходимо заполнить поле Материал',
				'on' => 'edit_type_'.self::BUILD_TYPE_OUTBUILDING
			),
			// Дополнительные ограничения для "Общественные здания"
			array(
				'name, image_id', 'required',
				'on' => 'edit_type_'.self::BUILD_TYPE_PUBLIC
			),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, author_id, object_id, building_type_id, interior_id, status, name, desc, image_id, tag', 'safe', 'on'=>'search'),
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
			'service' => array(self::BELONGS_TO, 'Service', 'service_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' 		=> 'ID',
			'author_id' 	=> 'Автор',
			'object_id' 	=> 'Object',
			'building_type_id' => 'Тип строения',
			'interior_id' 	=> 'Interior',
			'status' 	=> 'Статус',
			'name' 		=> 'Название',
			'desc' 		=> 'Описание проекта',
			'image_id' 	=> 'Обложка проекта',
			'tag' 		=> 'Теги',
			'create_time' 	=> 'Дата создания',
			'update_time' 	=> 'Дата обновления',

			'style_id' 	=> 'Архитекутрный стиль',
			'material_id' 	=> 'Материалы несущих конструкций',
			'color_id' 	=> 'Основной цвет',
			'floor_id' 	=> 'Этажность',

			'room_mansard'	=> 'Мансарда',
			'room_garage'	=> 'Встроенный гараж',
			'room_ground'	=> 'Цокольный этаж',
			'room_basement'	=> 'Подвал',
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
		$criteria->compare('author_id',$this->author_id);
		$criteria->compare('object_id',$this->object_id);
		$criteria->compare('building_type_id',$this->building_type_id);
		$criteria->compare('interior_id',$this->interior_id);
		$criteria->compare('status',$this->status);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('image_id',$this->image_id);
		$criteria->compare('tag',$this->tag,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Ссылка на редактирование текущего проекта
	 */
	public function getUpdateLink()
	{
		return Yii::app()->createUrl("/idea/architecture/update/id/{$this->id}");
	}

	public function getDeleteLink()
	{
		return Yii::app()->createUrl("/idea/architecture/delete/id/{$this->id}");
	}

	/**
	 * Выдает массив всех констант в случае, если не указан $status_id.
	 * Иначе выдает название статуса $status_id.
	 *
	 * @param integer $status_id id нужной константы
	 * @return array Массив вида
	 * 	array( "id_константы" => "имя_константы", ...)
	 */
	public static function getStatusName($status_id = NULL)
	{
		switch (Yii::app()->user->role) {
			case User::ROLE_JUNIORMODERATOR :
				return (!array_key_exists($status_id, self::$statusNamesForJuniors)) ? self::$statusNamesForJuniors : self::$statusNamesForJuniors[$status_id];
				break;

			default:
				return (!array_key_exists($status_id, self::$statusNames)) ? self::$statusNames : self::$statusNames[$status_id];
				break;
		}
	}


	/**
	 * Именованное условие для выборки данных.
	 * Выбирает проекты только с разрешенными статусами.
	 * Если не передать $author_id, выбираются интерьеры для текущего
	 * пользователя (Yii::app()->user)
	 *
	 * @param integer $author_id Идентификатор пользователя
	 * @return Architecture
	 */
	public function scopeOwnPublic($author_id = null, $sid = null)
	{
		if (is_null($author_id))
			$author_id = Yii::app()->user->id;

		$params = array(
			':author_id' => $author_id,
			':st1' => self::STATUS_MODERATING,
			':st2' => self::STATUS_ACCEPTED,
			':st3' => self::STATUS_REJECTED,
			':st4' => self::STATUS_CHANGED,
			':st5' => self::STATUS_VERIFIED,
			':st6' => self::STATUS_TEMP_IMPORT,
		);

		$criteria = new CDbCriteria();
		$criteria->order = 'create_time ASC';
		$criteria->limit = 1000;

		if (!$sid) {
			$criteria->condition = 'author_id=:author_id
                                        AND (status = :st1 OR status = :st2 OR status = :st3 OR status = :st4 OR status = :st5 OR status = :st6)';
		} else {
			$criteria->condition = 'author_id=:author_id AND service_id=:sid
                                        AND (status = :st1 OR status = :st2 OR status = :st3 OR status = :st4 OR status = :st5 OR status = :st6)';
			$params[':sid'] = intval($sid);
		}
		$criteria->params = $params;

		$this->getDbCriteria()->mergeWith($criteria);
		return $this;
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
	 * Вызов расчета рейтинга специалиста по текущей услуге
	 */
	public function countUserServiceRating()
	{
		Yii::app()->gearman->appendJob('userService', array('userId'=>$this->author_id, 'serviceId'=>$this->service_id));
	}

	/**
	 * Get image preview path
	 */
	public function getPreview($configName)
	{
		/*$fileObject = UploadedFile::model()->findByPk($this->image_id);
		if (!is_null($fileObject)) {
			$preview = $fileObject->getPreviewName($configName, 'Architecture');
			if (file_exists($preview))
				return $preview;
		}
		$name = $configName[0] . 'x' . $configName[1];
		return UploadedFile::getDefaultImage('Architecture', $name);*/
		return Yii::app()->img->getPreview($this->image_id, $configName);
	}

	public function afterSave()
	{
		parent::afterSave();

		// Обновляем кол-во фотографий Архитекутры для пользователя
		$this->countPhotos();


		// Обновление кол-ва проектов пользователя
		$project_quantity = Interior::model()->scopeOwnPublic($this->author_id)->count();
		$project_quantity += Interiorpublic::model()->scopeOwnPublic($this->author_id)->count();
		$project_quantity+= Portfolio::model()->scopeOwnPublic($this->author_id)->count();
		$project_quantity+= Architecture::model()->scopeOwnPublic($this->author_id)->count();
		UserData::model()->updateByPk($this->author_id, array('project_quantity' => $project_quantity));
	}

	/**
	 * Подсчет кол-ва фотографий "Архитектуры" и обновление параметра в БД
	 */
	public function countPhotos()
	{
		if ($this->getIsNewRecord())
			return;


		// Получаем фотки
		$count_photos = IdeaUploadedFile::model()->countByAttributes(array(
			'item_id' => $this->id,
			'idea_type_id' => Config::ARCHITECTURE
		));

		Architecture::model()->updateByPk($this->id, array('count_photos' => $count_photos));
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

	public function setServiceId()
	{
		if($this->isNewRecord)
			$this->service_id = self::SERVICE_ID;
	}

	/**
	 * Возвращает объект IdeaHeap со свойство object для типа архитектуры (главное свойство)
	 * @return IdeaHeap|null
	 */
	public function getObject()
	{
		// Объект типа строения.
		$obj = null;

		if ($this->object_id)
		{
			$obj = IdeaHeap::model()->findByPk((int)$this->object_id);
		}
		elseif ($this->building_type_id)
		{
			// Получаем тип строения
			if (($buildingType = IdeaHeap::model()->findByPk((int)$this->building_type_id))) {
				$obj = IdeaHeap::model()->findByPk((int)$buildingType->parent_id);
			}
		}

		if ( ! $obj)
			$obj = new IdeaHeap();

		return $obj;
	}

	/**
	 * Возвращает объект IdeaHeap Для свойства building_type для типа строения архитектуры
	 * @return CActiveRecord|null
	 */
	public function getBuild()
	{
		$build = null;

		if ($this->building_type_id)
			$build = IdeaHeap::model()->findByPk((int)$this->building_type_id);

		if ( ! $build)
			$build = new IdeaHeap();

		return $build;
	}

	/**
	 * Проверяет на повторение цвета в дополнительных цветах
	 * @param $attribute
	 * @param $params
	 * @return mixed
	 */
	public function validCheckColors($attribute, $params)
	{
		if (is_null($this->color_id))
			return;
		$result = IdeaAdditionalColor::model()->exists(
			'idea_type_id=:idea_type_id AND item_id=:item_id AND color_id=:color_id',
			array('idea_type_id' => Config::ARCHITECTURE, 'item_id' => $this->id, 'color_id' => $this->color_id)
		);
		if ($result)
			$this->addError('color_id', $params['message']);
	}


	/**
	 * Гетер для псевдосвойства $images объекта Architecture
	 * @return null
	 * @deprecated
	 */
//	public function getImages()
//	{
//		if (is_null($this->_images) && ! $this->isNewRecord) {
//			$this->_images = IdeaUploadedFile::model()->findAllByAttributes(array('item_id' => $this->id, 'idea_type_id' => Config::ARCHITECTURE));
//		}
//		return $this->_images;
//	}

	/**
	 * Проставляет новые статусы при сохранении на фронте
	 */
	public function changeStatus()
	{
		if (
			$this->status == self::STATUS_CHANGED
			|| $this->status == self::STATUS_MODERATING
		)
			return;

		if ($this->status == self::STATUS_ACCEPTED)  {
			$this->status = self::STATUS_CHANGED;
		} else {
			$this->status = self::STATUS_MODERATING;
		}

		if ($this->reachedLimitReject()) {
			$this->status = self::STATUS_REJECTED;
		}
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

	/**
	 * Генерация ссылки на фильтр с параметрами
	 * @param $options
	 * @return string
	 */
	public function getFilterLink($options=null)
	{
		$options['filter'] = 1;
		$options['ideatype'] = Config::ARCHITECTURE;
		$params = http_build_query($options);
		return '/idea/catalog?'.$params;
	}

	// IUploadImage
	public function getAuthorId()
	{
		return $this->author_id;
	}

	public function checkAccess()
	{
		return !is_null($this->author_id) && $this->author_id == Yii::app()->user->id;
	}

	public function getImageConfig($background=true)
	{
		if ($background) {
			return array(
				'crop_80', 'crop_230', 'resize_710x475', 'resize_1920x1080', 'width_520',
			);
		} else {
			return array('crop_210', 'crop_150');
		}
	}
	// end of IUploadImage

        /**
         * Возвращает список тегов данного проекта
         */
        public function getTags()
        {
                $tags = Yii::app()->db->createCommand()
                        ->select('t.id, t.name')->from('architecture_tag at')
                        ->join('tag t', 'at.tag_id=t.id')
                        ->where('at.architecture_id=:id', array(':id'=>$this->id))->queryAll();

                $tag_array = array();
                foreach($tags as $tag)
                        $tag_array[] = array('name'=>$tag['name'], 'value'=>$tag['id']);

                return $tag_array;
        }

	/**
	 * Название услуги проекта
	 * @return mixed
	 */
	public function getProjectType()
	{
		if (is_null(self::$_service)) {
			self::$_service = Service::model()->findByPk(self::SERVICE_ID);
		}
		return self::$_service->name;
	}

	/** Ссыслка на страницу модели(с комментариями) */
	public function getElementLink()
	{
		return "/users/{$this->author->login}/project/{$this->service_id}/{$this->id}";
	}

        public function getIdeaLink()
        {
                return '/idea/architecture/' . $this->id;
        }

	/** For new front */

	/**
	 * Colors id list
	 */
	public function getColorsList()
	{
		$sql = 'SELECT color_id FROM idea_additional_color as iac WHERE iac.idea_type_id='.Config::ARCHITECTURE.' AND NOT ISNULL(iac.color_id) AND iac.item_id = '.$this->id;
		$result = Yii::app()->db->createCommand($sql)->queryColumn();
		return array_merge(array($this->color_id), $result);
	}

	/**
	 * Увеличивает количество просмотров для указанного интерьера
	 * @static
	 * @param $archId
	 */
	public static function appendView($archId)
	{
		return Yii::app()->redis->incr(self::REDIS_VIEW.$archId);
	}

	public function getCountView()
	{
		return intval(Yii::app()->redis->get( self::REDIS_VIEW.$this->id) );
	}

	/**
	 * Получение системного типа объекта (например Config::INTERIOR)
	 * @return mixed
	 */
	public function getTypeId()
	{
		return Config::ARCHITECTURE;
	}

	// Методы для компонента фоточек
	/**
	 * Список id фоточек
	 * @return mixed
	 */
	public function getPhotoList()
	{
		$sql = 'SELECT uploaded_file_id as id FROM idea_uploaded_file as iuf WHERE idea_type_id=:type AND item_id=:id';
		$type = Config::ARCHITECTURE;
		$id = $this->id;
		return Yii::app()->db->createCommand($sql)->bindParam(':type', $type)->bindParam(':id', $id)->queryColumn();
	}

	/**
	 * Загрузка изображений
	 * @param $file CUploadedFile
	 * @param string $desc
	 */
	public function loadImage($fileName, $desc='')
	{
		$file = CUploadedFile::getInstance($this, $fileName);
		if ( $file===null )
			return null;

		/** @var $imgComp ImageComponent */
		$imgComp = Yii::app()->img;
		$fileId = $imgComp->putImage($file->getTempName(), $file->getName(), $this->author_id, $desc);
		if ( $fileId===null ) {
			return null;
		}
		// генерация превью
		$imgComp->generatePreview($fileId, $this->getImageConfig(false), false);
		$imgComp->generatePreview($fileId, $this->getImageConfig(true), true);
		return $fileId;
	}

	static public function imageFormater($images = array(), $show_products = false)
	{
		if(!is_array($images))
			$images = array($images);

		$html = '';

		/** @var $imgComp ImageComponent */
		$imgComp = Yii::app()->img;
		foreach($images as $imgId){

			$image = CHtml::image($imgComp->getPreview($imgId, 'crop_150'));
			$src_big = $imgComp->getPreview($imgId, 'resize_710x475');

			// Показываем кол-во товаров, привязанных к элементу
			$cssBind = '';
			if ($show_products == true) {
				Yii::import('application.modules.catalog.models.ProductOnPhotos');

				$html .= ProductOnPhotos::getQntProducts($imgId);
				$cssBind = 'bind_products';
			}

			$html.=Chtml::openTag('div',array('style'=>'position: relative; overflow: hidden; '));
			$link = CHtml::link( $image, $src_big, array( 'class' => 'preview '.$cssBind, 'data-file_id' => $imgId, 'title'=>$imgComp->getOriginSize($imgId) ) );
			$html.=CHtml::tag('div', array('style'=>'float:left;'), $link, true);
			$html.=CHtml::openTag('div', array('style'=>'position: absolute; top: 0pt; left: 0pt; padding-left: 170px; z-index: -1;'));
			$html.= CHtml::tag('strong', array(), 'Описание',true)."<br>";
			$html.= $imgComp->getDesc($imgId);
			$html.=CHtml::closeTag('div');
			$html.= CHtml::closeTag('div');
			$html.= CHtml::tag('div', array('style' => 'clear: both;margin-bottom: 10px;'), '', true);


		}

		return $html;
	}


	/**
	 * Увеличивает счетчик количества отклонений для идеи
	 */
	public function increaseReject()
	{
		$sql = 'UPDATE architecture SET count_rejected = count_rejected + 1 WHERE id = ' . $this->id;

		Yii::app()->db
			->createCommand($sql)
			->execute();
	}

	/**
	 * Возвращает количество отклонений для идеи
	 *
	 * @return mixed
	 */
	public function getCountReject()
	{
		$sql = 'SELECT count_rejected FROM architecture WHERE id = ' . $this->id;

		$qt = Yii::app()->db->createCommand($sql)->queryScalar();

		return $qt;
	}


	/**
	 * Проверяем достигла идея устанволенного количество разрешенных
	 * отклонений или нет.
	 */
	public function reachedLimitReject()
	{
		return $this->getCountReject() >= 3;
	}
}