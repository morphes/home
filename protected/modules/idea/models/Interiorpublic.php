<?php

/**
 * This is the model class for table "interiorpublic".
 *
 * The followings are the available columns in table 'interiorpublic':
 * @property integer $id
 * @property integer $author_id
 * @property integer $object_id
 * @property integer $building_type_id
 * @property integer $interior_id
 * @property integer $status
 * @property string $name
 * @property string $desc
 * @property integer $image_id
 * @property string $tag
 * @property integer $create_time
 * @property integer $update_time
 */
class Interiorpublic extends EActiveRecord implements IProject, IComment, IUploadImage
{
	const REDIS_VIEW = 'interiorpublic:view:';

	public static $sphinxWeight = array(
		'object' => 80,
		'style' => 40,
		'color' => 40,
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
	const SERVICE_ID = 2;

	/**
	 * Задаем типы посторек для интерьера. По этим типам
	 * потом различается вывод формы редактирования и назначение сценариев.
	 */
	const BUILD_TYPE_LIVE 		= 1; // Жилые помещения
	const BUILD_TYPE_PUBLIC 	= 2; // Общественные помещения

	const PROPERTY_ID_LIVE = 72; 	// Идентификатор свойства "Жилой интерьер"
					// Используется в некоторых условиях для логики кода.
	const PROPERTY_ID_PUBLIC = 84;  // Идентификатор свойства "Общественный интерьер"


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


	// Переменная для сохранения изображений.
	public $image;
	/** @var int позиция для вывода в портфолио */
	public $position = 0;

	// Переменная, в которую методом getImages сохраняется массив всех загруженных
	// фотографий к проекту.
	private $_images = null;


	// Используется для проверки кол-ва добавленных изображений, и вывода ошибки.
	public $imagesCount = 0;

	// Используется для проверки корректности ввода данных соавторов, и вывода ошибки
	public $coauthors;

	// Тип изображения для загрузки
	private $_imageType = null;

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
		$sorting->idea_type_id = Config::INTERIOR_PUBLIC;
		$sorting->service_id = $this->service_id;
		$sorting->position = 1;
		$sorting->update_time = time();
		$sorting->save(false);

	}

	public function updateSphinx()
	{
		Yii::app()->gearman->appendJob('sphinx:interiorpublic', $this->id);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Interiorpublic the static model class
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
		return 'interiorpublic';
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
			array('author_id, object_id, building_type_id, architecture_id, status, image_id, create_time, update_time', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			array('desc', 'length', 'max'=>5000),
			array('name, image_id, color_id, style_id', 'required',
				'on' => 'edit'
			),
			array('color_id', 'validCheckColors', 'message' => 'Цвета помещения повторяются',
				'on' => 'edit'
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
			'architecture_id'=> 'Архитектура',
			'status' 	=> 'Статус',
			'name' 		=> 'Название',
			'desc' 		=> 'Описание проекта',
			'image_id' 	=> 'Обложка проекта',
			'tag' 		=> 'Теги',
			'create_time' 	=> 'Дата создания',
			'update_time' 	=> 'Дата обновления',

			'style_id' 	=> 'Стиль',
			'color_id' 	=> 'Основной цвет',
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
		$criteria->compare('architecture_id',$this->architecture_id);
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
		return Yii::app()->createUrl("/idea/interiorpublic/update/id/{$this->id}");
	}

	public function getDeleteLink()
	{
		return Yii::app()->createUrl("/idea/interiorpublic/delete/id/{$this->id}");
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
	 * @return Interiorpublic
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
		$tmp1 = Interiorpublic::model()->scopeOwnPublic($this->author_id, $this->service_id)->count();
		$tmp2 = Interior::model()->scopeOwnPublic($this->author_id, $this->service_id)->count();
		$project_qt = $tmp1 + $tmp2;

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
	public function getPreview($config)
	{
		$fileObject = UploadedFile::model()->findByPk($this->image_id);
		if (!is_null($fileObject)) {
			$preview = $fileObject->getPreviewName($config, 'interior');
			if (file_exists($preview))
				return $preview;
		}
		$name = $config[0] . 'x' . $config[1];
		return UploadedFile::getDefaultImage('interior', $name);
	}

	public function afterSave()
	{
		parent::afterSave();

		// Обновляем кол-во фотографий Архитекутры для пользователя
		$this->countPhotos();


		// Обновление кол-ва проектов пользователя
		$project_quantity = Interior::model()->scopeOwnPublic($this->author_id)->count();
		$project_quantity += Interiorpublic::model()->scopeOwnPublic($this->author_id)->count();
		$project_quantity += Portfolio::model()->scopeOwnPublic($this->author_id)->count();
		$project_quantity += Architecture::model()->scopeOwnPublic($this->author_id)->count();
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
			'idea_type_id' => Config::INTERIOR_PUBLIC
		));

		Interiorpublic::model()->updateByPk($this->id, array('count_photos' => $count_photos));
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
			array('idea_type_id' => Config::INTERIOR_PUBLIC, 'item_id' => $this->id, 'color_id' => $this->color_id)
		);
		if ($result)
			$this->addError('color_id', $params['message']);
	}


	/**
	 * Гетер для псевдосвойства $images объекта Interiorpublic
	 * @return null
	 * @deprecated
	 */
	public function getImages()
	{
		if (is_null($this->_images) && ! $this->isNewRecord) {
			$this->_images = IdeaUploadedFile::model()->findAllByAttributes(array('item_id' => $this->id, 'idea_type_id' => Config::INTERIOR_PUBLIC));
		}
		return $this->_images;
	}

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
                                        'project_name'=> $this->name,
                                        'author_name' 	=> $commentAuthor->name,
                                        'project_link' => $this->getElementLink(),
                                        'sign_A'=> Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage()
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
		$options['ideatype'] = Config::INTERIOR_PUBLIC;
		$params = http_build_query($options);
		return '/idea/catalog?'.$params;
	}

	// IUploadImage
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'interiorPublic': return 'idea/'.intval($this->author_id/UploadedFile::PATH_SIZE + 1).'/'.$this->author_id.'/interiorpublic/'.$this->id;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'interiorPublic': return 'interiorpublic'.$this->id.'_'.mt_rand(0, 100).'_'.time();
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
			case 'interiorPublic': return array(
				'realtime' => array(
					self::$preview['crop_210'],
					self::$preview['crop_150'],
				),
				'background' => array(
					self::$preview['crop_80'],
					self::$preview['crop_230'],
					self::$preview['resize_710x475'],
					self::$preview['resize_1920x1080'],
					self::$preview['width_520'],
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
		if (is_null(self::$_service)) {
			self::$_service = Service::model()->findByPk(self::SERVICE_ID);
		}
		return self::$_service->name;
	}

	/** Ссыслка на страницу модели(с комментариями) */
	public function getElementLink()
	{
		return "/users/{$this->author->login}/project/".self::SERVICE_ID."/{$this->id}?t=2";
	}

        public function getIdeaLink()
        {
                return '/idea/interiorpublic/' . $this->id;
        }

	/** For new front */

	/**
	 * Colors id list
	 */
	public function getColorsList()
	{
		$sql = 'SELECT color_id FROM idea_additional_color as iac WHERE iac.idea_type_id='.Config::INTERIOR_PUBLIC.' AND NOT ISNULL(iac.color_id) AND iac.item_id = '.$this->id;
		$result = Yii::app()->db->createCommand($sql)->queryColumn();
		return array_merge(array($this->color_id), $result);
	}

	/**
	 * Получение объектов на изображения интерьера
	 * @return array
	 */
	public function getPhotos()
	{
		$criteria = new CDbCriteria();
		$criteria->join = 'INNER JOIN idea_uploaded_file as iuf ON iuf.uploaded_file_id=t.id AND idea_type_id=:type';
		$criteria->condition = 'iuf.item_id=:id';
		//$criteria->select = 'DISTINCT *';
		$criteria->params = array(':type'=>Config::INTERIOR_PUBLIC, ':id'=>$this->id);
		$criteria->index = 'id';
		return UploadedFile::model()->findAll($criteria);
	}

	/**
	 * Увеличивает количество просмотров для указанного интерьера
	 * @static
	 * @param $interiorId
	 */
	public static function appendView($interiorId)
	{
		return Yii::app()->redis->incr(self::REDIS_VIEW.$interiorId);
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
		return Config::INTERIOR_PUBLIC;
	}


	/**
	 * Увеличивает счетчик количества отклонений для идеи
	 */
	public function increaseReject()
	{
		$sql = 'UPDATE interiorpublic SET count_rejected = count_rejected + 1 WHERE id = ' . $this->id;

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
		$sql = 'SELECT count_rejected FROM interiorpublic WHERE id = ' . $this->id;

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