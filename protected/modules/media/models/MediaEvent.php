<?php

/**
 * This is the model class for table "media_event".
 *
 * The followings are the available columns in table 'media_event':
 * @property integer $id
 * @property integer $status
 * @property integer $author_id
 * @property integer $image_id
 * @property integer $whom_interest
 * @property integer $event_type
 * @property integer $city_id главный город события(1й)
 * @property integer $is_online
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $send_status
 * @property integer $cost
 * @property integer $public_time
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $count_visit
 * @property integer $count_comment
 * @property integer $count_view
 * @property string $name
 * @property string $meta_desc
 * @property string $site
 * @property string $organizer
 * @property string $content
 * @property array $params
 */
class MediaEvent extends EActiveRecord implements IUploadImage, IComment
{
	// --- СТАТУСЫ ---
	const STATUS_IN_PROGRESS = 1; // Создается
	const STATUS_PUBLIC 	= 2; // Опубикован
	const STATUS_HIDE 	= 3; // Скрыт
	const STATUS_DELETED 	= 4; // Удален

	public static $statusNames = array(
		self::STATUS_IN_PROGRESS => 'Создается',
		self::STATUS_PUBLIC => 'Опубликован',
		self::STATUS_HIDE => 'Скрыт',
		self::STATUS_DELETED => 'Удален',
	);

	// Констнаты для поля "Кому это интересно"
	const WHOM_SPEC = 1; // Только специалистам
	const WHOM_USER = 2; // Только владельцам квартир
	const WHOM_SPEC_USER = 3; // И специалистам и владельцам квартир

	/** Сортировки */
	const SORT_DATE = 1; // по дате
	const SORT_POPULAR = 2; // по популярности

	const SORT_DIRECT_DESC = 1; // по убыванию
	const SORT_DIRECT_ASC = 2; // По возростанию

	public static $preview = array(
		'crop_80' => array(80, 80, 'crop', 80),
		'crop_300x213' => array(300, 213, 'crop', 80),
		'crop_60' => array(60, 60, 'crop', 80),
		'crop_700x450' => array(700, 450, 'crop', 90),
		'crop_160x110' => array(160, 110, 'crop', 80),
		/*
		'crop_210' => array(210, 210, 'crop', 80), // preview
		'crop_280x200' => array(280, 200, 'crop', 80),
		*/
	);

	/** За это время, до начала события отправляются письма */
	const NOTIFY_PERIOD = 172800;
	const NOTIFY_NOT_SENT = 0; // Уведомления не отправлены
	const NOTIFY_SENT = 1; // Уведомления отправлены

	/** @var integer для поиска по тематикам */
	public $theme = null;
	/**
	 * @var array Десериализованный массив параметров
	 */
	private $_params = null;

	// Переменная для множественного свойства "Тематики"
	private $_themes = null;

	// Тип изображения для загрузки
	private $_imageType = null;

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
		$this->onBeforeSave = array($this, 'saveThemes');
		$this->onAfterSave = array($this, 'updateParams');
		$this->onAfterSave = array($this, 'updateSphinx');
	}

	public function __get($name)
	{
                if($name == 'preview')
                        return parent::__get($name);

		$getter = 'get' . $name;
		if (method_exists($this, $getter))
			return $this->$getter();
		return parent::__get($name);
	}

	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter))
			return $this->$setter($value);
		return parent::__set($name, $value);
	}

	/**
	 * Update create_time and update_time in object
	 */
	public function setDate()
	{
		if ($this->getIsNewRecord())
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	public function setParams($value)
	{
		parent::__set('params', serialize($value));
		$this->_params = null;
	}

	public function getParams()
	{
		if (is_null($this->_params))
			$this->_params = unserialize(parent::__get('params'));
		return $this->_params;
	}

	public function updateSphinx()
	{
		Yii::app()->gearman->appendJob('sphinx:media', array('type'=>Media::TYPE_EVENT, 'id'=>$this->id));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MediaEvent the static model class
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
		return 'media_event';
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'author'=>array(self::BELONGS_TO, 'User', 'author_id'),
                        'preview'=>array(self::BELONGS_TO, 'UploadedFile', 'image_id'),
		);
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('start_time, public_time, name, themes, event_type, organizer', 'required'),

			array('author_id, image_id, whom_interest, event_type, start_time, public_time', 'numerical', 'integerOnly'=>true, 'allowEmpty'=>false, 'min'=>1),
			array('status, author_id, end_time', 'numerical', 'integerOnly'=>true),

			array('site, name, cost, meta_desc', 'length', 'max'=>255),
			array('organizer', 'length', 'max'=>1024),
			array('is_online', 'boolean'),
			array('content', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, status, author_id, image_id, whom_interest, event_type, start_time, end_time, cost, public_time, create_time, update_time, count_comment, count_view, site, organizer, content', 'safe', 'on'=>'search'),
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
			'image_id' => 'Превью',
			'whom_interest' => 'Кому интересно',
			'event_type' => 'Тип события',
			'start_time' => 'Дата начала',
			'end_time' => 'Дата окончания',
			'cost' => 'Стоимость участия',
			'public_time' => 'Дата публикации',
			'create_time' => 'Дата создания',
			'update_time' => 'Дата обновления',
			'count_comment' => 'Количество комментариев',
			'count_view' => 'Количество просмотров',
			'site' => 'Веб-сайт',
			'organizer' => 'Организатор',
			'content' => 'Содержание',
			'is_online' => 'Онлайн-событие',
			'name' => 'Название',
			'place' => 'Место проведения',
			'theme' => 'Тематика',
			'meta_desc' => 'Meta Description',
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
		$criteria->compare('event_type',$this->event_type);

		if ($this->start_time)
			$criteria->compare('t.start_time', '>='.$this->start_time);
		if ($this->end_time)
			$criteria->compare('t.end_time', '<='.($this->end_time+86400));

		$criteria->compare('public_time',$this->public_time);
		if (isset($_GET['MediaEvent']['city_id']))
			$cityId = intval( $_GET['MediaEvent']['city_id'] );
		if ( !empty($cityId) ) {
			$criteria->join= 'INNER JOIN media_event_place as mep ON mep.event_id ';
			$criteria->compare('mep.city_id', $cityId);
		}

		if (isset($_GET['MediaEvent']['theme']))
			$theme = intval( $_GET['MediaEvent']['theme'] );
		if ( !empty($theme) ) {
			$criteria->join .= 'INNER JOIN media_theme_select as mts ON mts.model_id=t.id AND model="MediaEvent" ';
			$criteria->compare('mts.theme_id', $theme);
		}


		if ($this->status) {
			$criteria->compare('status', $this->status);
		} else {
			$criteria->addCondition('status IN (:st1,:st2,:st3)');
			$criteria->params[':st1'] = self::STATUS_IN_PROGRESS;
			$criteria->params[':st2'] = self::STATUS_PUBLIC;
			$criteria->params[':st3'] = self::STATUS_HIDE;
		}

		$sort = new CSort();
		$sort->defaultOrder = array('id' => CSort::SORT_DESC);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>$sort,
		));
	}


	/**
	 * Возвращает значение для множественного поля themes.
	 * Если значение еще не было получено, то получает выставленные
	 * Тематики из модели MediaThemeSelect
	 * @return array|null
	 */
	public function getThemes()
	{
		if ($this->getIsNewRecord())
		{
			$this->_themes = array();
		}
		elseif (is_null($this->_themes))
		{
			$selected = MediaThemeSelect::model()->findAllByAttributes(array(
				'model' => get_class($this),
				'model_id' => $this->id
			));

			if ($selected) {
				foreach($selected as $item) {
					$this->_themes[] = $item->theme_id;
				}
			}
		}

		return $this->_themes;
	}

	/**
	 * Устанавливает значение для множественного поля themes
	 * @param $value
	 * @return bool
	 */
	public function setThemes($value)
	{
		if (is_array($value)) {
			$this->_themes = $value;
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Сохраняет значения мультизначительного свойства "Тематики" в связные таблицу
	 */
	public function saveThemes()
	{
		if ( !$this->getIsNewRecord() && ! empty($this->_themes)) {

			$className = get_class($this);

			MediaThemeSelect::model()->deleteAllByAttributes(array(
				'model' => $className,
				'model_id' => $this->id
			));

			$sql = 'INSERT INTO '.MediaThemeSelect::model()->tableName().' (`model`, `model_id`, `theme_id`) VALUES';
			foreach($this->_themes as $i=>$theme_id) {
				$id = (int)$theme_id;
				if ($i > 0)
					$sql .= ',';
				$sql .= "('{$className}', '{$this->id}', '{$id}')";
			}
			Yii::app()->getDb()->createCommand($sql)->execute();
		}
	}

	// IUploadImage
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			// Фотка превью для всей статьи
			case 'main': return 'media/event/'.intval($this->id % 100); break;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			// Имя превью файла
			case 'main': return 'event'.$this->id.'_'.time(); break;
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
		return !is_null($this->author_id) && $this->author_id === Yii::app()->user->id;
	}

	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'main': return array(
				'realtime' => array(
					self::$preview['crop_80'],
				),
				'background' => array(
					self::$preview['crop_300x213'],
					self::$preview['crop_700x450'],
					self::$preview['crop_60'],
					self::$preview['crop_160x110'],
				),
			);
			default: throw new CException('Invalid upload image type');
		}
	}

	// end of IUploadImage

	/**
	 * Get image preview path
	 */
	public function getPreview($config)
	{
		$fileObject = UploadedFile::model()->findByPk($this->image_id);
		if (!is_null($fileObject)) {
			$preview = $fileObject->getPreviewName($config);
			if (file_exists($preview))
				return $preview;
		}
		$name = $config[0] . 'x' . $config[1];
		return UploadedFile::getDefaultImage('default', $name);
	}

	public function getCommentsVisibility()
	{
		return true;
	}

	public function afterComment($comment)
	{
		/**
		 * Обновление кол-ва комментариев текущего объекта
		 */
		$count_comment = Comment::getCountComments($this);
		self::model()->updateByPk($this->id, array(
			'count_comment'  => $count_comment,
		));

		return array(0, $count_comment);
	}

	/**
	 * Возвращает ссылку на элемент для фронтенда
	 */
	public function getElementLink()
	{
		return '/journal/events/'.$this->id;
	}

	/**
	 * Ссылка на фильтр событий (фронт)
	 * @static
	 */
	static public function getListLink($options=array())
	{
		if (empty($options))
			return '/journal/events';

		$params = http_build_query($options);
		return '/journal/events?'.$params;
	}

        static public function getSectionLink()
        {
                return '/journal/events';
        }

	public function getIsOwner()
	{
		return $this->checkAccess();
	}

	/**
	 * Увеличивает количество просмотров для указанного события
	 * @static
	 * @param $eventId
	 */
	public static function appendView($eventId)
	{
		$sql = 'UPDATE media_event SET count_view=count_view+1 WHERE id='.intval($eventId);
		Yii::app()->db->createCommand($sql)->execute();
	}

	/**
	 * Обновление количества решивших прийти на событие
	 * @static
	 * @param $eventId
	 */
	public static function updateVisitCount($eventId)
	{
		$count = MediaEventVisit::model()->countByAttributes(array('event_id'=>$eventId));
		self::model()->updateByPk($eventId, array('count_visit'=>$count));
	}

	public function updateParams()
	{
		if ($this->getIsNewRecord())
			return;

		$params = array(
			'typeName'=>'',
			'theme'=>array(),
		);

		$sql = 'SELECT DISTINCT t.id, t.name FROM media_theme as t '
			.'INNER JOIN media_theme_select as mts ON mts.theme_id=t.id AND mts.model="MediaEvent" '
			.'WHERE mts.model_id=:eid';
		$eid = $this->id;
		$themes = Yii::app()->db->createCommand($sql)->bindParam(':eid', $eid)->queryAll();
		foreach ($themes as $theme) {
			$params['theme'][$theme['id']] = $theme['name'];
		}
		$eventType = MediaEventType::model()->findByPk($this->event_type);
		if (!is_null($eventType))
			$params['typeName'] = $eventType->name;

		self::model()->updateByPk($this->id, array('params'=>serialize($params)));
	}

	/**
	 * Проверяет, идет ли указанный пользователь на событие
	 * @param $userId
	 */
	public function getIsVisit($userId)
	{
		if (empty($userId))
			return false;
		return MediaEventVisit::model()->exists('event_id=:eid AND user_id=:uid', array(':eid'=>$this->id, ':uid'=>$userId));
	}
}