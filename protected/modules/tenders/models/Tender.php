<?php

/**
 * This is the model class for table "tender".
 *
 * The followings are the available columns in table 'tender':
 * @property integer $id
 * @property integer $author_id
 * @property integer $city_id
 * @property integer $status
 * @property integer $send_notify
 * @property string $name
 * @property string $desc
 * @property double $cost
 * @property integer $expire
 * @property integer $create_time
 * @property integer $update_time
 * @property string token
 * @property string email
 * @property string author_name
 */
class Tender extends EActiveRecord
{
	/** Config */
	const DURATION_TENDER = 600;
	
	const STATUS_MAKING = 1;
	const STATUS_OPEN = 2;
	const STATUS_IN_COMPLETITION = 3;
	const STATUS_CLOSED = 4;
	const STATUS_DELETED = 5;
	const STATUS_MODERATING = 6;
	const STATUS_CHANGED = 7;

	
	public static $statusNames = array(
		self::STATUS_MAKING => 'В процессе',
		self::STATUS_OPEN => 'Открыт',
		self::STATUS_CLOSED => 'Закрыт',
		self::STATUS_MODERATING => 'На модерации',
		self::STATUS_CHANGED => 'Изменен',
		self::STATUS_IN_COMPLETITION => 'На доработке',
		self::STATUS_DELETED => 'Удален',
	);
	
	const TENDER_TYPE_ALL = 0;
	const TENDER_TYPE_OPEN = 1;
	const TENDER_TYPE_CLOSED = 2;
	public static $typeNames = array(
	    self::TENDER_TYPE_ALL => 'Открытые и завершенные',
	    self::TENDER_TYPE_OPEN => 'Открытые',
	    self::TENDER_TYPE_CLOSED => 'Завершенные',
	);
	
	const SORT_DATE = 0;
	const SORT_RESPONSE = 1;
	const SORT_COST = 2;
	public static $sortNames = array(
	    self::SORT_DATE => 'Дате',
	    self::SORT_RESPONSE => 'Количеству откликов',
	    self::SORT_COST => 'Бюджету',
	);

	const NOTIFY_NEW = 0; // Новый тендер, уведомление не отправляется
	const NOTIFY_SEND = 1; // Уведомление в очереди на отправку
	const NOTIFY_SENDED = 2; // Уведомление отправлено
	public static $notifyNames = array(
		self::NOTIFY_NEW => 'Не отправлено',
		self::NOTIFY_SEND => 'На отправку',
		self::NOTIFY_SENDED => 'Отправлено',
	);
	
	public static $listPageSizes = array(20 => 20, 40 => 40, 60 => 60);

	const COST_COMPARE = 0;
	const COST_EXECT = 1;

	// Список полей, которые должны быть за encode'ны при присваивании значения
        protected $encodedFields = array('name', 'desc');
	
	/** Code */
	private $_city = null;
	private $_country = null;
	private $_oldStatus = null;
	private $_user = false;

	public $cost_flag = self::COST_COMPARE;

	public $afterSaveCommit = false;


	public function init()
	{
		parent::init();
		$this->onAfterFind = array($this, 'checkCostFlag');
		$this->onBeforeSave = array($this, 'setDate');
		$this->onBeforeSave = array($this, 'checkStatus');
		$this->onAfterSave = array($this, 'doOnAfterSave');

	}

    public  function doOnAfterSave() {
        $this->updateSphinx();
        $this->notifySend();
    }

	/** Установка флага "сравню варианты" */
	public function checkCostFlag()
	{
		if (empty($this->cost))
			$this->cost_flag = self::COST_COMPARE;
		else
			$this->cost_flag = self::COST_EXECT;
	}

	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter))
			return $this->$setter($value);
		else
			return parent::__set($name, $value);
	}

	public function setStatus($value)
	{
		$this->_oldStatus = $this->status;
		return parent::__set('status', $value);
	}

	/** Обрабока в соответствии с изм статуса */
	public function checkStatus()
	{
		if (is_null($this->_oldStatus) || $this->_oldStatus == $this->status)
			return true;

		if ($this->_oldStatus == self::STATUS_MAKING && $this->status == self::STATUS_MODERATING) {
			$this->send_notify = self::NOTIFY_NEW; // Первичная установка значения
			return true;
		}
		if ( in_array($this->_oldStatus, array(self::STATUS_MODERATING, self::STATUS_IN_COMPLETITION)) && $this->status == self::STATUS_OPEN) {
			// письмо автору о публикации тендера + отправка уведомления
			if ($this->send_notify == self::NOTIFY_NEW)
				$this->send_notify = self::NOTIFY_SEND;

			return true;
		}
		return true;
	}

	/**
	 * Собственно отправка уведомлений
	 * @return bool
	 */
	public function notifySend()
	{
		if (is_null($this->_oldStatus) || $this->_oldStatus == $this->status)
			return true;

		if ($this->_oldStatus == self::STATUS_MAKING && $this->status == self::STATUS_MODERATING) {
			$this->adminNotify(); // уведомление админам
			return true;
		}

		if ( in_array($this->_oldStatus, array(self::STATUS_MODERATING, self::STATUS_IN_COMPLETITION)) && $this->status == self::STATUS_OPEN && $this->send_notify == self::NOTIFY_SEND) {
			// письмо автору о публикации тендера + отправка уведомления
			$this->openNotify();
			return true;
		}

		if ( $this->status == self::STATUS_CLOSED && $this->_oldStatus != self::STATUS_CLOSED) {
			// закрытие тендера + отправка письма о закрытии
			$this->closeNotify();
			return true;
		}

		if ( $this->status == self::STATUS_DELETED && $this->_oldStatus != self::STATUS_DELETED ) {
			$this->deleteNotify();
			return true;
		}
	}

	public function updateSphinx()
	{
		Yii::app()->gearman->appendJob('sphinx:tender', $this->id);
	}

	public function setDate()
	{
		if ($this->isNewRecord) {
                        $this->create_time = $this->update_time = time();
			$this->token = self::generateToken();
		} else
                        $this->update_time = time();
	}

	public function behaviors()
        {
                return array(
                    'CSafeContentBehavor' => array(
                        'class' => 'application.components.CSafeContentBehavior',
                        'attributes' => $this->encodedFields,
                    ),
                );
        }

	/**
	 * Обработчик, переопределенное для транзакционного сохранения
	 * @param bool $manualRun
	 */
	public function afterSave($manualRun = false)
	{
        if ($this->afterSaveCommit == false || ($this->afterSaveCommit == true && $manualRun == true))
            parent::afterSave();
	}


	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Tender the static model class
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
		return 'tender';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		$rules = array(
			// update
			array('city_id', 'exist', 'className'=>'City', 'attributeName'=>'id', 'allowEmpty'=>false, 'message'=>'Заполните город', 'on'=>'createTender'),

			array('name', 'length', 'max'=>255, 'tooLong'=>'Слишком длинное название'),
			array('name', 'required', 'message'=>'Заполните наменование заказа', 'on'=>'createTender, admUpdate, changeTender'),
		    
			array('expire', 'required', 'message'=>'Заполните срок заявки', 'on'=>'admUpdate, createTender'),
			array('cost_flag', 'numerical', 'integerOnly'=>true, 'min'=>self::COST_COMPARE, 'max'=>self::COST_EXECT, 'on'=>'createTender, changeTender, admUpdate'),
			array('desc', 'required', 'message' => 'Необходимо заполнить описание', 'on'=>'createTender, changeTender, admUpdate'),

			//admUpdate
			array('status', 'numerical', 'integerOnly'=>true, 'on'=>'admUpdate'),
			array('expire', 'numerical', 'min'=>time(), 'integerOnly'=>true, 'tooSmall' => 'Некорректный срок действия заявки', 'on'=>'admUpdate'),

			array('cost', 'numerical', 'allowEmpty'=>false, 'integerOnly'=>true, 'max'=>10000000000, 'message' => 'Бюджет должен быть числом', 'tooBig'=>'Слишком большое число'),
			array('desc', 'length', 'max'=>3000, 'tooLong' => 'Слишком длинное описание'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, author_id, city_id, status, name, desc, cost, expire, create_time, update_time', 'safe', 'on'=>'search'),
		);

		if (Yii::app()->getUser()->getIsGuest()) {
			return array_merge($rules,array(
				array('author_name', 'required', 'message'=>'Заполните автора заказа', 'on'=>'createTender'),
				array('author_name', 'length', 'max'=>255, 'tooLong'=>'Слишком длинное имя автора', 'on'=>'createTender'),
				array('email', 'required', 'message'=>'Заполните email', 'on'=>'createTender'),
				array('email', 'email', 'message'=>'Некорректный email', 'on'=>'createTender'),
				array('email', 'length', 'max'=>255, 'tooLong'=>'Слишком длинное имя автора', 'on'=>'createTender'),
			));
		} else {
			return array_merge($rules, array(
				array('author_id', 'required', 'on'=>'createTender, updateTender'),
				array('author_id', 'numerical', 'integerOnly'=>true),

			));
		}
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'author_id' => 'Автор',
			'city_id' => 'City',
			'status' => 'Статус',
			'name' => 'Название',
			'desc' => 'Описание',
			'send_notify' => 'Уведомление',
			'cost' => 'Бюджет заказа',
			'expire' => 'Expire',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
			'response_count' => 'Откликов',
		);
	}

	/**
	 * Проверка тендера на открытость
	 * @return boolean 
	 */
	public function getIsClosed()
	{
		return $this->status == Tender::STATUS_CLOSED;
	}
	
	public function getCutDesc()
	{
		return Amputate::getLimb($this->desc, 350);
	}


	/**
	 * Список выбранных услуг тендера
	 */
	public function getServiceList()
	{
		Yii::import('application.modules.member.models.Service');
		$criteria = new CDbCriteria();
		$criteria->join = 'INNER JOIN `tender_service` as ts ON ts.service_id=t.id';
		$criteria->condition = 'ts.tender_id=:id';
		$criteria->params = array('id'=> $this->id);
		return Service::model()->findAll($criteria);
	}
	
	/**
	 * Получение города, в котором объявлен тендер
	 * @return City
	 */
	public function getCity()
	{
		if ( is_null($this->_city) && !is_null($this->city_id) ) {
			$this->_city = City::model()->findByPk($this->city_id);
		}
		return $this->_city;
	}
	
	/**
	 * Получение страны, в котором объявлен тендер
	 * @return Country
	 */
	public function getCountry()
	{
		if ( is_null($this->_country) && !is_null($this->_country) ) {
			$this->_country = Country::model()->findByPk($this->_country);
		}
		return $this->_country;
	}
	
	public function getCityName()
	{
		$city = $this->getCity();
		if (is_null($city))
			return '';
		return $city->name;
	}
	
	/**
	 * Получение владельца тендера
	 * @return User
	 */
	public function getUser()
	{
		if ($this->_user === false) {
			if (is_null($this->author_id))
				$this->_user = null;
			else
				$this->_user = User::model()->findByPk($this->author_id);
		}
		return $this->_user;
	}
	
	/**
	 * Получение списка подходящих тендеров
	 * @param integer $uid
	 * @param array $filters
	 * @return CSphinxDataProvider
	 */
	public static function getSuitedProvider($uid, $filters=array())
	{

		$sphinxClient = Yii::app()->search;
		if ( empty($filters)) {
			$filters['status'] = array(Tender::STATUS_OPEN, Tender::STATUS_CLOSED, Tender::STATUS_CHANGED);
		}
		$filters['author_id'] = array('val'=>$uid, 'exclude'=>true);

		// service filter
		$sql = 'SELECT service_id FROM user_service WHERE user_id='.$uid;
		$services = Yii::app()->db->createCommand($sql)->queryAll();

		$matchMode = SPH_MATCH_FULLSCAN;
		if (!empty($services)) {
			$data = array();
			foreach ($services as $service) {
				$data[] = $service['service_id'];
			}
			$filters['services'] = $data;
		} else {
			$matchMode = SPH_MATCH_ANY;
		}

		// city filter
		$sql = 'SELECT DISTINCT city.id FROM user_servicecity as usc '
			.'INNER JOIN city  ON  city.id=usc.city_id OR (ISNULL(usc.city_id) AND city.region_id = usc.region_id) '
			.'WHERE usc.user_id ='.$uid;
		$cities = Yii::app()->db->createCommand($sql)->queryAll();

		if (!empty($cities)) {
			$data = array();
			foreach ($cities as $city) {
				$data[] = $city['id'];
			}
			$filters['city_id'] = $data;
		} else {
			$matchMode = SPH_MATCH_ANY;
		}

		$tenders = Yii::app()->cookieStorage->getValue('tenders', array());
		if (!empty($tenders)) {
			$tenders = array_keys($tenders);
			$filters['@id'] = array('val'=>$tenders, 'exclude'=>true);
		}

		$dataProvider = new CSphinxDataProvider($sphinxClient,
			array('index' => 'tender',
			      'modelClass' => 'Tender',
			      'filters' => $filters,
			      'matchMode' => $matchMode,
			      'sortMode' => SPH_SORT_EXTENDED,
			      'sortExpr' => 'is_open DESC @id DESC',
			      'pagination' => array('pageSize' => 100, 'pageVar'=>'page'),
			)
		);
		return $dataProvider;
	}
	
	/**
	 * Получение списка тендеров, где user - исполнитель
	 * @param integer $uid
	 * @param array $filters
	 * @return CSphinxDataProvider
	 */
	public static function getIdoerProvider($uid, $filters=array())
	{
		$sphinxClient = Yii::app()->search;
		if ( empty($filters)) {
			$filters['status'] = array(Tender::STATUS_OPEN, Tender::STATUS_CLOSED, Tender::STATUS_CHANGED);
		}
		$filters['author_id'] = array('val'=>$uid, 'exclude'=>true);
		$filters['users'] = array($uid);

		$dataProvider = new CSphinxDataProvider($sphinxClient,
			array('index' => 'tender',
			      'modelClass' => 'Tender',
			      'filters' => $filters,
			      'sortMode' => SPH_SORT_EXTENDED,
			      'sortExpr' => 'is_open DESC @id DESC',
			      'matchMode' => SPH_MATCH_FULLSCAN,
			      'pagination' => array('pageSize' => 100, 'pageVar'=>'page'),
			)
		);
		return $dataProvider;
	}
	
	/**
	 * Получение списка тендеров, где user - клиент
	 * @param integer $uid
	 * @param array $filters
	 * @return CSphinxDataProvider
	 */
	public static function getIclienProvider($uid, $filters=array())
	{
		$sphinxClient = Yii::app()->search;
		if ( empty($filters)) {
			$filters['status'] = array(Tender::STATUS_OPEN, Tender::STATUS_CLOSED, Tender::STATUS_CHANGED);
		}
		$tenders = Yii::app()->cookieStorage->getValue('tenders', array());

		$select = '*';
		if (!empty($tenders)) {
			$tenders = implode(' OR @id=', array_keys($tenders));
			$select = '*, (IF(author_id='.$uid.', 1, 0) + (@id='.$tenders.') ) as param';
			$filters['param'] = array('val'=>0, 'exclude'=>true);
		} else {
			$filters['author_id'] = array($uid);
		}

		$dataProvider = new CSphinxDataProvider($sphinxClient,
			array('index' => 'tender',
			      'modelClass' => 'Tender',
				'select' => $select,
			      'filters' => $filters,
			      'matchMode' => SPH_MATCH_FULLSCAN,
			      'sortMode' => SPH_SORT_EXTENDED,
			      'sortExpr' => 'is_open DESC @id DESC',
			      'pagination' => array('pageSize' => 100, 'pageVar'=>'page'),
			)
		);

		return $dataProvider;
	}
	
	/**
	 * Возвращает количество тендеров (В каталоге тендеров)
	 */
	public static function getTendersQuantity()
	{
		$key = 'Tender::getTendersQuantity';
		$value = Yii::app()->cache->get($key);
		if (!$value || true) {
			$command = Yii::app()->db->createCommand();
			$command->from('tender');
			$command->where('status IN (:stOpen,:stClosed,:stChange)',
				array(':stOpen' => self::STATUS_OPEN,  ':stClosed' => self::STATUS_CLOSED, ':stChange'=>self::STATUS_CHANGED));
			$command->select('count(id)');
			$value = $command->queryScalar();
			Yii::app()->cache->set($key, $value, Cache::DURATION_REAL_TIME);
		}
		return $value;
	}
	
	/**
	 * Увеличение числа просмотров тендера 
	 */
	public function incrementViews()
	{
                // Получаем ip пользователя, просматривающего страницу
		$ip = Yii::app()->request->userHostAddress;
		$ip = ip2long($ip);
		
                $command = Yii::app()->db->createCommand();

                /*
                 * Проверяем есть ли для текущего просматривающего профиль
                 * запись в БД о том, что он смотрел в профиль в течение ПОСЛЕДНИХ СУТОК.
                 * Если нет, то считаем его голос.
                 */
                $command->select('id')
                        ->from('tender_views')
                        ->where('time > :offset_time AND tender_id = :tender_id AND ip = :ip', array(
                                ':offset_time' => (int)(time() - 86400), // Время между засчитанными просмотрами в секундах
                                ':tender_id' => $this->id,
                                ':ip' => $ip,
                        )
                );
                $finded = $command->queryRow();

                // Если не найден голос.
                if (!$finded) {

                        // Увеличиваем количество просмотров профиля пользователя
                        $redis = Yii::app()->redis;
                        $redis->incr('tender_view_cnt:' . $this->id);

                        $command = Yii::app()->db->createCommand();
                        // Пишем голос в таблицу profile_views
                        $command->insert('tender_views', array(
                                'time' => time(),
                                'tender_id' => $this->id,
                                'ip' => $ip,
                        ));
                }
	}

	public function getAvailableStatusList()
	{
		$list = self::$statusNames;
		unset ($list[self::STATUS_MAKING]);
		if ($this->status != self::STATUS_MODERATING)
			unset ($list[self::STATUS_MODERATING]);

		if ($this->status != self::STATUS_CHANGED)
			unset ($list[self::STATUS_CHANGED]);
		if ($this->status == self::STATUS_DELETED) {
			$list = array(self::STATUS_DELETED => self::$statusNames[self::STATUS_DELETED]);
		}

		return $list;
	}
	
	/**
         * Получить количество просмотров тендера
         *
         * return integer Количество просмотров
         */
        public function getViews()
        {
                // Получаем количество просмотров профиля пользователя
                $redis = Yii::app()->redis;
                return (int)$redis->get('tender_view_cnt:' . $this->id);
        }

	// Mails

	/**
	 * Отправка уведомлений админу о новом тендере
	 */
	public function adminNotify()
	{
		foreach (Config::$adminEmails as $email) {
			Yii::app()->mail->create('tenderAdminNotify')
				->to($email)
				->params(array(
				'tender_name' => $this->name,
				'city_name' => $this->getCityName(),
				'admin_link' => CHtml::link('Просмотреть заказ', Yii::app()->homeUrl.$this->getAdminLink()),
				'author_name' => $this->getAuthorEmail(),
			))
			->send();
		}
	}

	/**
	 * Отправка уведомления о закрытии тендера
	 */
	public function closeNotify()
	{
		Yii::app()->mail->create('tenderClose')
			->to($this->getAuthorEmail())
			->params(array(
			'user_name' => $this->getAuthorName(),
			'tender_name' => CHtml::link( $this->name, Yii::app()->homeUrl.$this->getLink() ),
		 	'public_time' => Yii::app()->getDateFormatter()->format('d MMMM yyyy', $this->create_time),
			'close_time' => Yii::app()->getDateFormatter()->format('d MMMM yyyy в HH:ss', $this->expire),
			'sign_А' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
		))
		->send();
	}

	/**
	 * Уведомление об удалении тендера
	 */
	public function deleteNotify()
	{
		Yii::app()->mail->create('tenderDeleted')
			->to($this->getAuthorEmail())
			->params(array(
			'user_name' => $this->getAuthorName(),
			'public_time' => Yii::app()->getDateFormatter()->format('d MMMM yyyy', $this->create_time),
			'tender_name' => $this->name,
			'sign_А' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
		))
		->send();
	}

	/**
	 * Уведомление о том, что тендер открыт
	 * + рассылка спецам
	 */
	public function openNotify()
	{
		if ( $this->send_notify == self::NOTIFY_SEND && in_array($this->status, array(self::STATUS_OPEN, self::STATUS_CHANGED)) ) {
			$days = $this->getDaysToExpire();
			$closeDays = CFormatterEx::formatNumeral($days, array('дня', 'дней', 'дней'));
			if (empty($this->author_id)) { // GUEST

				Yii::app()->mail->create('tenderOpenGuest')
					->to($this->getAuthorEmail())
					->params(array(
					'user_name' => $this->getAuthorName(),
					'public_time' => Yii::app()->getDateFormatter()->format('d MMMM yyyy', $this->create_time),
					'tender_name' => CHtml::link( $this->name, Yii::app()->homeUrl.$this->getAccessLink() ),
					'close_days' => $closeDays,
					'sign_A' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
				))
				->send();

			} else { // user
				Yii::app()->mail->create('tenderOpenAuthor')
					->to($this->getAuthorEmail())
					->params(array(
					'user_name' => $this->getAuthorName(),
					'public_time' => Yii::app()->getDateFormatter()->format('d MMMM yyyy', $this->create_time),
					'tender_name' => CHtml::link( $this->name, Yii::app()->homeUrl.$this->getAccessLink() ),
					'close_days' => $closeDays,
					'edit_link' => CHtml::link('Редактировать заказ', Yii::app()->homeUrl.$this->getEditLink()),
					'sign_A' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
				))
				->send();
			}
			/** Постановка писем о новом тендере в очередь */
			Yii::app()->gearman->appendJob('mail:send_tender', $this->id);
		}
	}



        /**
         * Возвращает ссылку на текущий тендер
         * @return string
         */
        public function getLink()
        {
                return '/tenders/'.$this->id;
        }

	/**
	 * Ссылка на редактирование тендера
	 * @return string
	 */
	public function getEditLink()
	{
		return '/tenders/create/'.$this->id;
	}

	/**
	 * Имя автора (в том числе для гостей)
	 * @return string
	 */
	public function getAuthorName()
	{
		if (empty($this->author_id))
			return $this->author_name;

		$user = $this->getUser();
		return $user ? $user->name : '';
	}

	/**
	 * Email для работы тендера (в том числе для гостей)
	 * @return string
	 */
	public function getAuthorEmail()
	{
		if (empty($this->author_id))
			return $this->email;

		$user = $this->getUser();
		return $user ? $user->email : '';
	}

	/**
	 * Получение превью пользователя, оставившего тендер
	 * @param $config
	 * @return mixed
	 */
	public function getAuthorPreview($config)
	{
		if (empty($this->author_id) || !$user = $this->getUser()) {
			$name = $config[0].'x'.$config[1];
			return UploadedFile::getDefaultImage('user', $name);
		}

		return $user->getPreview($config);
	}

	/**
	 * получение url автора тендера (в том числе неавторизованного)
	 * @return string
	 */
	public function getAuthorUrl()
	{
		if (empty($this->author_id) || !$user = $this->getUser()) {
			return '#';
		}
		return "/users/{$user->login}";
	}

	/**
	 * @static
	 * генерация ключа доступа к тендеру
	 * @return string
	 */
	public static function generateToken()
	{
		return md5(uniqid(rand(), true));
	}

	/**
	 * Ссыдка для получения доступа к тендеру
	 * @return mixed
	 */
	public function getAccessLink($hash=null)
	{
		if (is_null($hash))
			return Yii::app()->getController()->createUrl('/tenders/tender/access/', array('id'=>$this->id, 'token'=>$this->token));
		else
			return Yii::app()->getController()->createUrl('/tenders/tender/access/', array('id'=>$this->id, 'token'=>$this->token, 'hash'=>$hash));
	}

	/**
	 * Ссыдка для получения доступа к тендеру и его закрытия
	 * @return mixed
	 */
	public function getCloseLink()
	{
		return Yii::app()->getController()->createUrl('/tenders/tender/close/', array('id'=>$this->id, 'token'=>$this->token));
	}

	/**
	 * Ссылка на страницу просмотра в админке
	 */
	public function getAdminLink()
	{
		return '/tenders/admin/tender/view/id/'.$this->id;
	}

	/**
	 * Проверка наличия доступа к тендеру (через привязку неавторизованного юзера)
	 * @return bool
	 */
	public function hasAccess()
	{
		/** Автор всегда имеет доступ */
		if (!is_null($this->author_id) && $this->author_id === Yii::app()->getUser()->getId())
			return true;

		$tenders = Yii::app()->cookieStorage->getValue('tenders', array());

		return isset($tenders[$this->id]);
	}

	/**
	 * Проверка, что юзер является автором (авторизованным) тендера
	 * @return bool
	 */
	public function getIsAuthor()
	{
		return !is_null($this->author_id) && $this->author_id === Yii::app()->getUser()->getId();
	}

	/** дает доступ к тендеру чз cookieStorage */
	public function appendAccess()
	{
		if ($this->getIsNewRecord())
			return;

		$tenders = Yii::app()->cookieStorage->getValue('tenders', array());
		$tenders[$this->id] = 1;
		Yii::app()->cookieStorage->setValue('tenders', $tenders);
	}

	/** Количество дней до завершения тендера */
	public function getDaysToExpire()
	{
		$dtime = $this->expire - time();
		return intval( ($dtime-1) / 86400 ) + 1;
	}
}