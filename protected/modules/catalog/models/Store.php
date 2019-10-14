<?php

/**
 * This is the model class for table "cat_store".
 *
 * The followings are the available columns in table 'cat_store':
 * @property integer $id
 * @property integer $type
 * @property integer $status
 * @property integer $image_id
 * @property integer $tariff_id
 * @property integer $tariff_id_new
 * @property integer $tariff_enable_date
 * @property string $tariff_enable - геттер и сеттер для $tariff_enable_date
 * @property integer $tariff_expire_date
 * @property string $tariff_expire - геттер и сеттер для $tariff_expire_date
 * @property integer $user_id
 * @property integer $contractor_id
 * @property integer $mall_build_id
 * @property integer $floor_id
 * @property string $sect_name
 * @property string $name
 * @property string $activity
 * @property string $email
 * @property string $site
 * @property string $phone
 * @property string $admin_id
 * @property integer $subdomain
 * @property string $showcase
 * @property string $address
 * @property string $geocode
 * @property string $time
 * @property string $about
 * @property string $bg_class
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $bindedToProduct
 * @property string $anchor
 */
class Store extends EActiveRecord implements IUploadImage
{
        private $_imageType = null;
        private $_timeArray;
        private $_moderators;
        private $_owners = null;

        private $_checkFeedback = null;
	private $_oldTarif = null;
	private $_oldStatus = null;

	private $_city = false;
	public $city_id; // заплатка для старого кода

        // тарифные планы
        const TARIF_FREE = 1;
        const TARIF_VITRINA = 2;
        const TARIF_MINI_SITE = 3;

        /**
         * @var array лейблы для тарифов
         */
        static public $tariffs = array(
		self::TARIF_FREE      => 'Визитка',
		self::TARIF_VITRINA   => 'Витрина',
		self::TARIF_MINI_SITE => 'Минисайт',
        );

	const STATUS_ACTIVE = 1;
	const STATUS_DISABLED = 2;

	static public $statusNames = array(
		self::STATUS_ACTIVE => 'Активен',
		self::STATUS_DISABLED => 'Выключен',
	);

        public $logo;
        public $prod_qt;

	/* Флаг, использующийся в сфинкс для обозначения принадлежности
	   магазина к сетям магазина. */
	public $isChain;

	// Идентификатор сети магазина, к которой относится магазин.
	public $chainId;
	// Количество магазинов, принадлежащих сети
	public $chainQt;
	// Количество товаров, продающихся магазинов


	/* -------------------------------------------------------------
	 *  Определяем массивы букв
	 * -------------------------------------------------------------
	 */
	public static $spec = array('#', '@', '!', '$', '%', '^', '&', '*', '(', ')');

	public static $num = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

	public static $rus = array(
		'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'З', 'И',
		'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф',
		'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Э', 'Ю', 'Я',
	);

	public static $eng = array(
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
		'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'W',
		'V', 'X', 'Y', 'Z'
	);

	/**
	 * @var array Список классов для фона страницы магазинов с тарифом "Минисайт"
	 */
	public static $bgClasses = array(
		'bg-1', 'bg-2', 'bg-3', 'bg-4', 'bg-5', 'bg-6',
		'bg-7', 'bg-8', 'bg-9', 'bg-10', 'bg-11', 'bg-12'
	);

	/**
	 * @var integer Id товара, к которому прикручен магазин
	 * Используется в методе search() модели Store для поиска тех магазинов,
	 * которые привязаны к указанному в $bindedToProduct товару
	 */
	public $bindedToProduct;


	/**
	 * @var array $preview Массив с конфигурацией на ресайз
	 */
	public static $preview = array(
		'resize_280'        => array(280, 0, 'resize', 80, false),
		'resize_width_1000' => array(1000, 0, 'resize', 80, false),
		'crop_1000_230'     => array(1000, 230, 'crop', 80),
	);


	// константы типов магазинов
	const TYPE_OFFLINE = 1;
	const TYPE_ONLINE = 2;

	/**
	 * @var array лейблы для типов магазинов
	 */
	static public $types = array(
		self::TYPE_OFFLINE => 'Магазин',
		self::TYPE_ONLINE => 'Интернет-магазин',
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Store the static model class
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
		return 'cat_store';
	}

        public function init()
        {
                parent::init();
		$this->onAfterFind = array($this, 'initData');
                $this->onBeforeSave = array($this, 'setDate');
		$this->onAfterSave = array($this, '_resetCity');
		$this->onAfterSave = array($this, 'updateSphinx');
        }

	public function initData()
	{
		$this->_oldTarif = $this->tariff_id;
		$this->_oldStatus = $this->status;
	}

	public function updateSphinx()
	{
		// Если только удалили
		if ($this->status == self::STATUS_DISABLED) {
			if ($this->_oldStatus != self::STATUS_DISABLED) {
				Yii::app()->gearman->appendJob('sphinx:storeDelete', $this->id);
				$this->updateProducts();
			}
			return;
		}

		Yii::app()->gearman->appendJob('sphinx:store', $this->id);
		// Обновление товаров при изменении тарифа
		if ($this->tariff_id != $this->_oldTarif) {
			$this->updateProducts();
		}
	}

	/**
	 * Обновление связанных продуктов
	 */
	private function updateProducts()
	{
		$sql = 'SELECT p.id FROM cat_product as p '
			.'INNER JOIN cat_store_price as csp ON csp.product_id=p.id '
			.'WHERE csp.by_vendor=0 AND csp.store_id=:sid AND p.status=:st';
		$sid = $this->id;
		$status = Product::STATUS_ACTIVE;
		$data = Yii::app()->db->createCommand($sql)->bindParam(':sid', $sid)
			->bindParam(':st', $status)->queryColumn();
		foreach ($data as $productId) {
			Yii::app()->gearman->appendJob('sphinx:product',
				array('product_id' => $productId, 'action' => 'update')
			);
		}
	}


        public function setDate()
        {
                if ($this->isNewRecord)
                        $this->create_time = $this->update_time = time();
                else
                        $this->update_time = time();
        }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('user_id, name, tariff_id', 'required', 'except'=>'search'),
                        array('address, time', 'required', 'on'=>'offline', 'except'=>'search'),
                        array('site', 'required', 'on'=>'online', 'except'=>'search'),
                        array('showcase', 'required', 'on'=>'showcase-update'),
			array('image_id, city_id, user_id, admin_id, subdomain_id, tariff_id, tariff_id_new, tariff_expire_date, mall_build_id, floor_id, bindedToProduct', 'numerical', 'integerOnly'=>true),
			array('tariff_enable_date', 'checkEnableDate'),
			array('mall_build_id', 'mallCheck'),	// Сначала проверяем на ТЦ
			array('sect_name', 'length', 'max'=>10),
			array('bg_class', 'length', 'max'=>50),
			array('name, activity, phone, geocode, tariff_enable, tariff_expire, showcase', 'length', 'max'=>255),
			array('address, time', 'length', 'max'=>1000),
			array('about, anchor', 'length', 'max'=>3000),
                        array('admin_id', 'checkAdmin'),
                        array('email', 'email'),
                 //       array('site', 'url', 'allowEmpty' => true,
                   //             'message' => 'Неправильный URL сайта',
//                                'pattern'=>'/^(http(s?)\:\/\/)?(([0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)(\.[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)+(\/[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_-]*)*(\/?(\?([0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]]*(=[-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]\,\'\\\+%\$#]*){0,1}(&[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]]*(=[-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]\,\'\\\+%\$#]*){0,1})*){0,1})?))$/i',
                    //   ),
                        array('logo', 'file', 'types' => 'jpg, bmp, png, jpeg', 'maxFiles'=> 1, 'maxSize' => 104857600000, 'allowEmpty' => true),
			array('id, type, contractor_id, bindedToProduct, mall_build_id, floor_id, sect_name, tariff_id, tariff_expire_date, tariff_expire, image_id, city_id, name, activity, email, site, admin_id, phone, address, geocode, time, about, bg_class, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'uploadedFile' => array(self::BELONGS_TO, 'UploadedFile', 'image_id'),
			'author'       => array(self::BELONGS_TO, 'User', 'user_id'),
			'admin'        => array(self::BELONGS_TO, 'User', 'admin_id'),
			'headImage'    => array(self::BELONGS_TO, 'UploadedFile', 'head_image_id'),
			'subdomain'    => array(self::BELONGS_TO, 'Subdomain', 'subdomain_id'),
		);
	}

	/**
	 * Временный метод, для оффлайн магазинов
	 * (фикс страниц)
	 */
	public function getCity()
	{
		if ($this->_city === false) {
			if (empty($this->city_id)) {
				// ищем для оффлайн магазинов. Для online не используем
				if ($this->type == self::TYPE_OFFLINE) {
					$criteria = new CDbCriteria();
					$criteria->join = 'INNER JOIN cat_store_city as c ON c.city_id=t.id';
					$criteria->condition = 'c.store_id=:sid';
					$criteria->params = array(':sid'=>$this->id);

					$this->_city = City::model()->find($criteria);
				} else {
					$this->_city = null;
				}
			} else {
				$this->_city = City::model()->findByPk($this->city_id);
			}

		}

		return $this->_city;
	}

	public function _resetCity()
	{
		if ($this->type != self::TYPE_OFFLINE || empty($this->city_id))
			return;

		$city = StoreGeo::model()->findByAttributes(array(
						'store_id' => $this->id,
						'type' => StoreGeo::TYPE_CITY,
		));
		if ($city === null) {
			$city = new StoreGeo();
			$city->geo_id = $this->city_id;
			$city->type = StoreGeo::TYPE_CITY;
			$city->store_id = $this->id;
		} else {
			$city->geo_id = $this->city_id;
		}
		$city->save(false);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'              => 'ID',
			'image_id'        => 'Логотип',
			'logo'            => 'Логотип',
			'city_id'         => 'Город',
			'name'            => 'Название',
			'activity'        => 'Вид деятельности',
			'subdomain_id'    => 'Поддомен',
			'head_image_id'   => 'Фото для шапки Минисайта',
			'user_id'         => 'Автор',
			'email'           => 'Электронная почта',
			'site'            => 'Адрес сайта',
			'phone'           => 'Телефон',
			'address'         => 'Адрес',
			'time'            => 'Время работы',
			'admin_id'        => 'Администратор',
			'about'           => 'Описание',
			'bg_class'        => 'CSS класс для фона страницы',
			'showcase_data'   => 'Витрина товаров',
			'create_time'     => 'Дата создания',
			'update_time'     => 'Дата обновления',
			'contractor_id'   => 'Контрагент',
			'tariff_id'       => 'Тарифный план',
			'tariff_id_new'   => 'Новый тарифный план',
			'tariff_enable'   => 'Дата включения нового тарифа',
			'tariff_expire'   => 'Дата окончания тарифа',
			'mall_build_id'   => 'ТЦ',
			'floor_id'        => 'Этаж',
			'sect_name'       => 'Секция',
			'bindedToProduct' => 'С ценами на товар',
			'type'		  => 'Тип магазина',
            'anchor'      => "Анкор ссылки"
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($limit = null, $productId = null)
	{
		$criteria=new CDbCriteria;

		$criteria->select = 't.*';
		$criteria->compare('id',$this->id);
		if ($this->city_id) {
			$criteria->join = 'INNER JOIN cat_store_city as c ON c.store_id=t.id';
			$criteria->addCondition('c.city_id=:cid');
			$criteria->params[':cid'] = $this->city_id;
		}
		$criteria->compare('name',$this->name,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('tariff_id', $this->tariff_id);

		if ($limit)
			$criteria->limit = (int) $limit;

		if ($this->bindedToProduct == 'fake') {

			$criteria->join .= ' INNER JOIN cat_store_price sp ON sp.store_id = t.id';
			$criteria->group = 'sp.store_id';
			$criteria->having = 'MIN(sp.by_vendor) = 1';
			$criteria->addCondition('sp.product_id = :pid');
			$criteria->params[':pid'] = (int)$productId;

		} elseif ($this->bindedToProduct) {

			$criteria->join .= ' inner join cat_store_price csp'
				. ' on csp.store_id=t.id and by_vendor=0';
			$criteria->compare('csp.product_id', (int) $this->bindedToProduct);
		}

		$criteria->order = 'id asc';

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>$criteria->limit,
			),
		));
	}

        /**
         * Получение пути для сохранения файла, в формате для uploadedFile
         * @return string | false для новых записей
         * @throws CException
         */
        public function getImagePath()
        {
                if ( $this->getIsNewRecord())
                        return false;
                switch ($this->_imageType) {
                        case 'logo':
				return 'catalog/store/'.Yii::app()->user->id.'/'.$this->id;
				break;
			case 'headImage':
				return 'catalog/store/'.Yii::app()->user->id.'/'.$this->id;
				break;
                        default: throw new CException('Invalid upload image type');
                }
        }

        /**
         * Получение имени для сохранения файла, в формате для uploadedFile
         * @return string | false для новых записей
         * @throws CException
         */
        public function getImageName()
        {
                if ( $this->getIsNewRecord())
                        return false;
                switch ($this->_imageType) {
                        case 'logo':
				return 'store' . $this->id . '_' . mt_rand(1, 99) . '_'. time();
				break;
			case 'headImage':
				return 'headImage' . $this->id . '_' . mt_rand(1, 99) . '_' . time();
				break;
                        default: throw new CException('Invalid upload image type');
                }
        }

        /**
         * Получение ID владельца модели
         */
        public function getAuthorId()
        {
                return Yii::app()->user->id;
        }

        /**
         * Проверка доступа к объекту пользователем
         * @return bool true-имеет доступ
         */
        public function checkAccess()
        {
                return in_array(Yii::app()->user->model->role, array(BaseUser::ROLE_POWERADMIN, BaseUser::ROLE_ADMIN));
        }

        /**
         * @param name
         * Установка типа загружаемого изображения для модели
         */
        public function setImageType($name)
        {
                $this->_imageType = $name;
        }

        /**
         * Сброс установленного типа изображения
         * @return mixed
         */
        public function flushImageType()
        {
                $this->_imageType = null;
                return true;
        }

        /**
         * Формирует массив $this->time из данных, отправленных формой
         * Используется в форме магазина в админке
         */
        public function setTimeFromForm()
        {
                $workingTime = array(
                        'weekdays'=>array(
                                'work_from'=>CHtml::encode(Yii::app()->request->getParam('weekdays_work_from', 0)),
                                'work_to'=>CHtml::encode(Yii::app()->request->getParam('weekdays_work_to', 0)),
                                'dinner_enabled'=>CHtml::encode(Yii::app()->request->getParam('weekdays_dinner_enabled', false)),
                                'dinner_from'=>CHtml::encode(Yii::app()->request->getParam('weekdays_dinner_from', 0)),
                                'dinner_to'=>CHtml::encode(Yii::app()->request->getParam('weekdays_dinner_to', 0)),
                        ),
                        'saturday'=>array(
                                'work_from'=>CHtml::encode(Yii::app()->request->getParam('saturday_work_from', 0)),
                                'work_to'=>CHtml::encode(Yii::app()->request->getParam('saturday_work_to', 0)),
                                'dinner_enabled'=>CHtml::encode(Yii::app()->request->getParam('saturday_dinner_enabled', false)),
                                'dinner_from'=>CHtml::encode(Yii::app()->request->getParam('saturday_dinner_from', 0)),
                                'dinner_to'=>CHtml::encode(Yii::app()->request->getParam('saturday_dinner_to', 0)),
                        ),
                        'sunday'=>array(
                                'work_from'=>CHtml::encode(Yii::app()->request->getParam('sunday_work_from', 0)),
                                'work_to'=>CHtml::encode(Yii::app()->request->getParam('sunday_work_to', 0)),
                                'dinner_enabled'=>CHtml::encode(Yii::app()->request->getParam('sunday_dinner_enabled', false)),
                                'dinner_from'=>CHtml::encode(Yii::app()->request->getParam('sunday_dinner_from', 0)),
                                'dinner_to'=>CHtml::encode(Yii::app()->request->getParam('sunday_dinner_to', 0)),
                        ),
                );
                $this->time = serialize($workingTime);
        }

        /**
         * Формирует массив $this->time из данных, отправленных формой
         * Используется в форме магазина в личном кабинете магазина
         */
        public function setTimeFromProfileForm()
        {
                $workingTime = array();
                $this->time = serialize($workingTime);
        }

        /**
         * @return mixed Массив настроек времени работы
         */
        public function getTimeArray()
        {
		// если текущий объект - интернет-магазин, то
		// он не может иметь времени работы
		if ( $this->type == self::TYPE_ONLINE )
			return null;

                if(!$this->_timeArray) {
                        $time = unserialize($this->time);

                        if(!is_array($time))
                                $this->_timeArray = array(
                                                'weekdays'=>array('work_from'=>0,'work_to'=>0,'dinner_enabled'=>false,'dinner_from'=>0,'dinner_to'=>0,),
                                                'saturday'=>array('work_from'=>0,'work_to'=>0,'dinner_enabled'=>false,'dinner_from'=>0,'dinner_to'=>0,),
                                                'sunday'=>array('work_from'=>0,'work_to'=>0,'dinner_enabled'=>false,'dinner_from'=>0,'dinner_to'=>0,),
                                        );
                        else
                                $this->_timeArray = $time;
                }

                return $this->_timeArray;
        }

        /**
         * Возвращает список производителей, товары которых продаются в магазине
         * @param bool $asDataProvider
         * @param bool $onlyQuery
         * @param bool $byStoreVendor флаг включает в результат только производителей, привязанных по cat_store_vendor
         * @return array|CActiveDataProvider|int|string
         */
        public function getVendors($asDataProvider = false, $onlyQuery = false, $byStoreVendor=false)
        {
                if (!$byStoreVendor)
                        $vendors_list = Yii::app()->db->createCommand()
                                ->selectDistinct('p.vendor_id')
                                ->from('cat_store_price sp')
                                ->join('cat_product p', 'p.id=sp.product_id')
                                ->where('sp.store_id=:sid and p.status=:status and sp.by_vendor=0', array(':sid'=>$this->id, ':status'=>Product::STATUS_ACTIVE))
                                ->queryColumn();
                else
                        $vendors_list = Yii::app()->db->createCommand()
                                ->select('vendor_id')
                                ->from('cat_store_vendor sv')
                                ->where('sv.store_id=:sid', array(':sid'=>$this->id))
                                ->queryColumn();

                $vendor_ids = implode(',', $vendors_list);

                if(!$vendor_ids)
                        $vendor_ids = 0;

                if($onlyQuery)
                        return $vendor_ids;

                if(!$asDataProvider) {
                        $vendors = Vendor::model()->findAll("id in ({$vendor_ids})");
                        return $vendors;
                } else {
                        $vendors=new CActiveDataProvider('Vendor', array(
                                'criteria'=>array(
                                        'condition'=>"id in ({$vendor_ids})",
                                ),
				'pagination'=>array(
					'pageSize'=>100
				),
                        ));
                        return $vendors;
                }
        }

        public function imageConfig()
        {
                return array();
        }

        /**
         * Возвращает координаты текущего магазина
         * @param string $type
         * @return string
         */
        public function getCoordinates($type = 'all')
        {
		// если текущий объект - интернет-магазин, то
		// он не может иметь точных координат
		if ( $this->type == self::TYPE_ONLINE )
			return null;

                $geocode = unserialize($this->geocode);

                if(!is_array($geocode))
                        return '';

                $lat = isset($geocode[0]) ? $geocode[0] : '';
                $lng = isset($geocode[1]) ? $geocode[1] : '';

                switch($type) {
                        case 'all' :
                                return $lat.','.$lng;
                        case 'lat' :
                                return $lat;
                        case 'lng' :
                                return $lng;
                }
        }

        /**
         * Возвращает объект страны магазина
         * @return CActiveRecord|null
         */
        public function getCountry()
        {
		// если текущий объект - интернет-магазин, то не возвращаем
		// его страну, т.к. их может быть много
		if ( $this->type == self::TYPE_ONLINE)
			return null;

                if ($this->city)
                        return Country::model()->findByPk($this->city->country_id);
                else
                        return null;
        }

        /**
         * Возвращает объект сети, в которую входит магазин
         * @return CActiveRecord
         */
        public function getChain()
        {
                $chain_id = Yii::app()->db->createCommand()->select('chain_id')->from('cat_chain_store')
                        ->where('store_id=:id', array(':id'=>$this->id))->queryScalar();

                return Chain::model()->findByPk($chain_id);
        }

        /**
         * Возвращает текст балуна для текущего магазина при отображении его на YandexMap
         * @return string
         */
        public function getBaloonContent()
        {
		// если текущий объект - интернет-магазин, то
		// он не может иметь текст балуна, т.к. он не имеет и точки на карте
		// из-за отсутствия точного адреса
		if ( $this->type == self::TYPE_ONLINE )
			return null;

                $html = '';
                $html.= CHtml::link($this->name, Yii::app()->createUrl('/catalog/store/index', array('id'=>$this->id)));
                $html.= CHtml::openTag('p');

                $html.= $this->phone . '<br />' . $this->address . '<br />';

                $time = $this->getTimeArray();
                $html.= 'Пн-Пт: ' . $time['weekdays']['work_from'] . '–' . $time['weekdays']['work_to'];

		if ($time['saturday']['work_from'] == $time['sunday']['work_from'] && $time['saturday']['work_to'] == $time['sunday']['work_to']) {
			$html.= ', Сб-Вс: ' . $time['saturday']['work_from'] . '–' . $time['saturday']['work_to'];
		} else {
			$html.= ', Сб: ' . $time['saturday']['work_from'] . '–' . $time['saturday']['work_to'];
			$html.= ', Вс: ' . $time['sunday']['work_from'] . '–' . $time['sunday']['work_to'];
		}

                $html.= CHtml::closeTag('p');
                return $html;
        }

        /**
         * Валидатор проверяет возможность выставления указанного пользователя администратором
         * (проверяет роль указанного пользователя на принадлежность к админам и модерам магазинов)
         * @param $attribute
         * @param $params
         * @return bool
         */
        public function checkAdmin($attribute, $params)
        {
                if(!$this->$attribute)
                        return true;

                $user = User::model()->findByAttributes(array('id'=>$this->$attribute, 'status'=>User::STATUS_ACTIVE));

                if(!in_array($user->role, array(User::ROLE_POWERADMIN, User::ROLE_STORES_ADMIN)))
                        $this->addError($attribute, 'Указанный в качестве администратора пользователь не имеет привилегий администрирования магазинов');

        }


	/**
	 * Проверяет корректность ввода времи активации нового тарифа.
	 * А также проводит подменю основного тарифа, в случае необходимости.
	 *
	 * @param $attribute Имя атрибута. В частном случае tariff_enable_date
	 * @param $param Массив параметров.
	 */
	public function checkEnableDate($attribute, $param)
	{
		// Если указано время включения тарифа, но не выбран новый тариф
		if ($this->{$attribute} && !$this->tariff_id_new) {
			$this->addError($attribute, 'Не выбран новый тариф');
		}

		/*
		 * Если выбран Тариф, а также время указано прошедшее или
		 * вообще не указано, то сразу применяем новый тариф.
		 */
		if ($this->tariff_id_new && ($this->{$attribute} <= time() || !$this->{$attribute})) {

			$this->tariff_id = $this->tariff_id_new;
			$this->tariff_id_new = null;
			$this->tariff_enable_date = null;
		}
	}

	public function mallCheck($attribute, $params)
	{
		// если ТЦ не указан или текущая модель - интернет-магазин, то
		// валидация не проводится и значение атрибута сбрасывается
		if ( !$this->$attribute
			|| $this->type == self::TYPE_ONLINE
			|| $this->getScenario() === 'online'
		) {

			$this->$attribute = null;
			return true;
		}

		// Ищем указанный ТЦ
		$mall = MallBuild::model()->findByPk($this->$attribute);
		if ($mall)
		{
			// Ищем в указанном ТЦ указанный этаж
			$floor = MallFloor::model()->findByPk($this->floor_id, 'mall_build_id = :mid', array(':mid' => $mall->id));
			if ($floor) {
				// Проверяем заполнение секции
				if (empty($this->sect_name)) {
					$this->addError('sect_name', 'Название секции должно быть заполнено');
				}
			} else {
				$this->addError('floor_id', 'Указанный этаж не существует');
			}
		} else {
			$this->addError($attribute, 'Указанный торговый центр не существует');
		}

	}

        /**
         * Возвращает объекты модераторов
         * @return mixed
         */
        public function getModerators()
        {
                if(!$this->_moderators) {
                        $moderators = Yii::app()->db->createCommand()->select('moderator_id')
                                ->from('cat_store_moderator')
                                ->where('store_id=:sid', array(':sid'=>$this->id))->queryColumn();

                        $criteria = new CDbCriteria();
                        $criteria->addInCondition('id', $moderators);
                        $criteria->compare('role', User::ROLE_STORES_MODERATOR);
                        $criteria->compare('status', '<>'.User::STATUS_DELETED);

                        $this->_moderators = new CActiveDataProvider('User', array(
                                'criteria'=>$criteria,
                                'pagination' => array('pageSize' => 25)
                        ));

                        return $this->_moderators;

                } else {
                        return $this->_moderators;
                }
        }

        /**
         * Возвращает магазины, которые администрирует или модерирует указанный пользователь
         * @param $user_id
         * @param $onlyIDs вернуть только id
         * @return array Store
         */
        static public function getStoresForOwner($user_id, $onlyIDs=false)
        {
                $criteria = new CDbCriteria();
                $criteria->join = 'LEFT JOIN cat_store_moderator sm ON sm.store_id=t.id';
                $criteria->condition = '(t.admin_id=:uid OR sm.moderator_id=:uid)';
                $criteria->params = array(':uid'=>(int)$user_id);
                $criteria->group = 't.id';
                $criteria->index = 'id';
                $criteria->limit = 500;

                if ($onlyIDs) {
                        $criteria->select = 't.id';
                        $builder = new CDbCommandBuilder(Yii::app()->db->getSchema());
                        $command = $builder->createFindCommand('cat_store', $criteria);
                        return $command->queryColumn();
                } else {
                        return Store::model()->findAll($criteria);
                }
        }

        /**
         * Возвращает цену на товар в данном магазине
         * @param $pid
         * @return CActiveRecord
         */
        public function getProductPrice($pid)
        {
                return StorePrice::model()->findByAttributes(array('product_id'=>(int) $pid, 'store_id'=>$this->id, 'by_vendor'=>0));
        }

        /**
         * Возвращает кол-во товаров в магазине
         * @return string
         */
        public function getProductQt()
        {
                $sql = "SELECT product_qt FROM {{store}} WHERE id = :id";
		$id = intval($this->id);
		$qt = Yii::app()->sphinx->createCommand($sql)->bindParam(':id', $id)->queryScalar();

                return (int)$qt;
        }

        /**
         * Возвращает название магазина с его адресом
         * @return string
         */
        public function getFullName()
        {
		// полное название интернет-магазина
		if ( $this->type == self::TYPE_ONLINE )
			return $this->name;

		// полное название оффлайн-магазина
		if ($this->type == self::TYPE_OFFLINE ) {
			// проверка наличия указанного города для магазина
			$city = $this->city ? (', г. ' . $this->city->name . ', ') : ', ';
			// формирование массива
			return "«{$this->name}»{$city}{$this->address}";
		}

		return null;
        }

        /**
         * Возвращает ссылку для магазина по его id
         * @param $id
         * @return string
         */
        static public function getLink($id, $type = '') {

		$url = '#';

		$store = Store::model()->findByPk((int)$id);
		if ($store) {
			switch ($type) {
				case 'moneyAbout':
					if ($store->tariff_id == Store::TARIF_MINI_SITE) {
						$url = Yii::app()->createAbsoluteUrl(
							'catalog/store/moneyIndex',
							array('sub' => $store->subdomain->domain)
						);
					} else {
						$url = Yii::app()->createUrl(
							'catalog/store/index',
							array('id' => $store->id)
						);
					}
					break;

				case 'moneyProducts':
					if ($store->tariff_id == Store::TARIF_MINI_SITE) {
						$url = Yii::app()->createAbsoluteUrl(
							'catalog/store/moneyProducts',
							array('sub' => $store->subdomain->domain)
						);
					}
					break;

				case 'moneyFeedback':
					if ($store->tariff_id == Store::TARIF_MINI_SITE) {
						$url = Yii::app()->createAbsoluteUrl(
							'catalog/store/moneyFeedback',
							array('sub' => $store->subdomain->domain)
						);
					}
					break;

				case 'moneyFotos':
					if ($store->tariff_id == Store::TARIF_MINI_SITE) {
						$url = Yii::app()->createAbsoluteUrl(
							'catalog/store/moneyGallery',
							array('sub' => $store->subdomain->domain)
						);
					}
					break;

				default:
					// Ссылка на магазин
					$url = Yii::app()->createUrl(
						'/catalog/store/index',
						array('id' => $store->id)
					);
			}
		}


                return $url;
        }


	/**
	 * Формирует ссылку на страницу списка магазинов, с подстановкой переданных
	 * параметров (город, категория)
	 *
	 * @param array $params (map, city, cid)
	 *
	 * @return string
	 */
	static public function getLinkList($params = array())
	{
		// Английское название города
		if (isset($params['city']) && $params['city'] instanceof City) {
			$params['city'] = $params['city']->eng_name;
		} else {
			unset($params['city']);
		}

		// Идентификатор выбранной категории
		if (isset($params['cid']) && (int)$params['cid'] > 0) {
			$params['cid'] = (int)$params['cid'];
		} else {
			unset($params['cid']);
		}

		$url = Yii::app()->controller->createUrl('/catalog/stores', $params);

		return $url;
	}


	/**
	 * Вычисляет общее количество магазинов
	 * @param bool $use_cache
	 * @return int
	 */
	static public function countAll($use_cache = true)
	{
		$qnt = Yii::app()->cache->get('countTotalStores');

		if ( ! $use_cache)
			$qnt = null;

		if ( ! $qnt) {
			$qnt = Store::model()->count();

			Yii::app()->cache->set('countTotalStores', $qnt, Cache::DURATION_MAIN_PAGE);
		}

		return (int) $qnt;
	}

	static public function getQuantity($categoryId = 0, $cityId = 0)
	{
		$categoryId = (int)$categoryId;
		$cityId = (int)$cityId;

		$whereCondition = array();


		$sql = "SELECT COUNT(*) as qt "
			. " FROM {{store}}";



		if ($categoryId > 0) {


			$listIds = '';

			Yii::import('application.modules.catalog.models.Category');

			/** @var $cat Category */
			$cat = Category::model()->findByPk((int)$categoryId);
			if ($cat && $cat->isLeaf()) {
				$listIds = $cat->id;

			} elseif ($cat) {
				/* Если категория не листьевая, то собираем ID
				   все конечных категорий. */
				$res = $cat->getLastDescendants();
				if ($res) {
					foreach ($res as $item) {
						if ($listIds != '') {
							$listIds .= ',';
						}
						$listIds .= $item['id'];
					}
				}
			}

			if ($listIds != '') {
				$whereCondition[] = 'category_ids IN (' . $listIds . ')';
			}

		}
		if ($cityId > 0) {
			$storeList = StoreGeo::getStoreList($cityId);

			$whereCondition[] = 'id IN (' . implode(',', $storeList) . ')';
		}

		if (!empty($whereCondition)) {
			$sql .= ' WHERE ' . implode(' AND ', $whereCondition);
		}

		$sql .= " GROUP BY first_letter"
			. " LIMIT 100;";

		$res = Yii::app()->sphinx->createCommand($sql)->queryColumn();

		$qt = array_sum($res);

		return $qt;
	}


	/**
	 * Возвращает данные по ТЦ, если к магазину есть привязка.
	 *
	 * @return array|null
	 */
	public function getMallData()
	{
		// Если нет привязки к ТЦ или текущая модель - интернет-магазин,
		// то возвращаем пустое значение
		if ( ! $this->mall_build_id || $this->type == self::TYPE_ONLINE)
			return null;

		// Получаем данные по ТЦ
		$mall = MallBuild::model()->findByPk($this->mall_build_id);
		if ( ! $mall)
			return null;

		// Получаем фотку этажа из ТЦ
		$floor = MallFloor::model()->findByPk($this->floor_id);
		if ( ! $floor)
			return null;

		return array(
			'mall_name'  => $mall->name,
			'floor_img'  => $floor->image,
			'floor_name' => $floor->name,
			'sect_name'  => $this->sect_name,
		);
	}

        /**
         * Устанавливает tariff_expire_date в формат unix timestamp
         * @param $value дата в текстовом формате
         */
        public function setTariff_expire($value)
        {
                $this->tariff_expire_date = strtotime($value);
        }

        /**
         * Возвращает значение tariff_expire_date в текстовом формате
         * @return string
         */
        public function getTariff_expire()
        {
                $expire_date = $this->tariff_expire_date ? $this->tariff_expire_date : time();
                return date('d.m.Y', $expire_date);
        }

	/**
	 * Устанавливает tariff_enable_date в формат unix timestamp
	 * @param $value дата в текстовом формате
	 */
	public function setTariff_enable($value)
	{
		if ($value) {
			$this->tariff_enable_date = strtotime($value);
		} else {
			$this->tariff_enable_date = null;
		}

	}

	/**
	 * Возвращает значение tariff_enable_date в текстовом формате
	 * @return string
	 */
	public function getTariff_enable()
	{
		if ($this->tariff_enable_date) {
			return date('d.m.Y', $this->tariff_enable_date);
		} else {
			return null;
		}
	}

        /**
         * Возвращает массив данных витрины
         * @return array
         */
        public function getShowcase_data()
        {
                $data = @unserialize($this->showcase);
                if ( is_array($data) )
                        return $data;
                else
                        return array(1=>null, 2=>null, 3=>null, 4=>null, 5=>null, 6=>null, 7=>null, 8=>null);
        }

        /**
         * Устанавливает значение данных витрины в виде сериализованного массива
         * для сохранения в базу
         * @param $value
         */
        public function setShowcase_data($value)
        {
                if ( is_array($value) )
                        $this->showcase = serialize($value);
                else
                        $this->showcase = serialize(array(1=>null, 2=>null, 3=>null, 4=>null, 5=>null, 6=>null, 7=>null, 8=>null));
        }

        /**
         * Проверка витрины на пустоту
         * Если хотя-бы один элемент указан, витрина считается не пустой
         */
        public function checkEmptyShowcase()
        {
                foreach($this->getShowcase_data() as $data) {
                        if ($data)
                                return false;
                }
                return true;
        }

	/**
	 * Возвращает список категорий, в которых продаются товары текущего магазина
	 *
	 * @param bool $useSphinx
	 * Если параметр useSphinx = true то для поиска используется Sphinx
	 *
	 * @return mixed
	 */
	public function getUsedCategory($useSphinx = false)
	{
		if ($useSphinx) {
			$sql = "SELECT category_ids FROM {{store}} WHERE  id=:storeId LIMIT 10";
			$storeId = (int)$this->id;

			$useCategories = Yii::app()->sphinx->createCommand($sql)
				->bindParam(":storeId", $storeId)
				->queryColumn();
		} else {
			$useCategories = Yii::app()->db->createCommand()->selectDistinct('p.category_id')
				->from('cat_store_price sp')->leftJoin('cat_product p', 'sp.product_id = p.id')->limit(10)
				->where('sp.store_id=:sid and p.status=:st and sp.by_vendor=0', array(':sid' => $this->id, ':st' => Product::STATUS_ACTIVE))->queryColumn();
		}

		return $useCategories;
	}

        /**
         * Проверяет наличие фидбека у текущего пользователя для текущего магазина
         * @return bool
         */
        public function getCheckFeedback()
        {
                if (is_null($this->_checkFeedback))
                        $this->_checkFeedback = StoreFeedback::model()->exists('store_id=:sid and user_id=:uid and parent_id=0', array(':sid' => $this->id, ':uid' => Yii::app()->user->id));
                return $this->_checkFeedback;
        }

        /**
         * Возвращает кол-во отзывов магазина
         * @return mixed
         */
        public function getFeedbackQt()
        {
		return Yii::app()->db
			->createCommand()
			->select('count(id)')
			->from(StoreFeedback::model()->tableName())
			->where('store_id=:sid and parent_id=0', array(':sid' => $this->id))
			->queryScalar();
        }

        /**
         * Проверяет указанного пользователя
         * @param $user_id
         * @return boolean
         */
        public function isOwner($user_id)
        {
                if (!$this->_owners) {
                        $this->_owners = Yii::app()->db->createCommand()->select('moderator_id')
                                ->from('cat_store_moderator')
                                ->where('store_id=:sid', array(':sid'=>$this->id))->queryColumn();
                        if ($this->admin_id)
                                $this->_owners[] = $this->admin_id;
                }

                if (in_array($user_id, $this->_owners))
                        return true;

                return false;
        }

	/**
	 * Наращивает счетчик просмотра магазина на единицу
	 */
	public function incrementView()
	{
		if ( ! $this->getIsNewRecord()) {
			// Наращиваем счетчик просмотра товара
			Yii::app()->redis->incr('Store:View:'.$this->id);
		}
	}

	/**
	 * Отвязывает от текущего магазина все товары, которые были
	 * привязаны мнимо (через Производителя).
	 *
	 * @param $vendorId integer Идентификатор Производителя
	 */
	public function unbindVendorFromCatStorePrice($vendorId)
	{

		$sql = 'DELETE FROM cat_store_price'
			. ' WHERE by_vendor = 1 '
			. ' AND store_id = :sid'
			. ' AND product_id in (SELECT id FROM cat_product WHERE vendor_id = :vid)';

		Yii::app()->db->createCommand($sql)->bindValues(array(
			':sid' => $this->id,
			':vid' => $vendorId
		))->execute();
	}


	/**
	 * Метод возвращает похожие магазины
	 * Приоритет на платные магазины. Если платных меньше чем лимит
	 * Дополняются бесплатными
	 * @param     $categoryId
	 * @param     $cityId
	 * @param     $explodeIdStore
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function getRelatedShops($categoryId, $cityId, $explodeIdStore, $limit = 6)
	{
		$relatedShopsData = array();
		$countPaidShops = 0;

		$storesList = StoreGeo::getStoreList($cityId);
		$storesStr = implode(',', $storesList);

		if (!empty($storesList)) {
			//Получаем список платных магазинов в конкретной категории в конкретном городе с платным тарифом
			$sql = "SELECT id FROM {{store}} WHERE category_ids=:catId AND id IN (:stores) AND id!=:storeId  AND tariff_id>:tariffId ORDER by rand() LIMIT :limit";
			$sql = str_replace(':stores', $storesStr, $sql);

			$catId = (int)$categoryId;
			$storeId = (int)$explodeIdStore;
			$tariffId = (int)self::TARIFF_VIZITKA;
			$limit = (int)$limit;

			$dataPaidShops = Yii::app()->sphinx->createCommand($sql)
				->bindParam(":catId", $catId)
				->bindParam(":storeId", $storeId)
				->bindParam(":tariffId", $tariffId)
				->bindParam(":limit", $limit)
				->queryColumn();

			$countPaidShops = count($dataPaidShops);

			foreach ($dataPaidShops as $dps) {
				$relatedShopsData[] = Store::model()->findByPk($dps);
			}
		}

		//Если количество платных магазинов не достает до лимита, то добиваем бесплатными.
		if ($countPaidShops < $limit && !empty($storesList)) {
			$sql = "SELECT id FROM {{store}} WHERE category_ids=:catId AND id IN (:stores) AND id!=:storeId AND tariff_id=:freeTariff GROUP BY chain_id LIMIT 100";
			$sql = str_replace(':stores', $storesStr, $sql);

			$catId = (int)$categoryId;
			$storeId = (int)$explodeIdStore;
			$freeTariff = (int)self::TARIF_FREE;

			$dataFreeShops = Yii::app()->sphinx->createCommand($sql)
				->bindParam(":catId", $catId)
				->bindParam(":storeId", $storeId)
				->bindParam(":freeTariff",$freeTariff)
				->queryColumn();

			shuffle($dataFreeShops);

			if (!empty($dataFreeShops)) {
				for ($i = $countPaidShops; $i < $limit; $i++) {
					if (isset($dataFreeShops[$i])) {
						$relatedShopsData[] = Store::model()->findByPk($dataFreeShops[$i]);
					}
				}
			}

		}

		($relatedShopsData)
			? $result = $relatedShopsData
			: $result = array();

		return $result;
	}


	/**
	 * Возвращает все объекты географии охвата интернет-магазина
	 *
	 * @return array OnlineStoreGeo
	 */
	public function getGeos()
	{
		if ( $this->type != self::TYPE_ONLINE )
			return array();

		return StoreGeo::model()->findAllByAttributes(array('store_id'=>$this->id));
	}


	/**
	 * Возвращает объекты магазинов, которыми управляет пользователь
	 * @param $userId
	 *
	 * @return array
	 */
	static public function getOwnedStores($userId)
	{
		$criteria = new CDbCriteria();
		$criteria->join = 'LEFT JOIN cat_store_moderator sm ON sm.store_id=t.id ';
		$criteria->condition = 't.admin_id=:uid OR sm.moderator_id=:uid';
		$criteria->params = array(':uid'=>$userId);
		$criteria->group = 't.id';
		return Store::model()->findAll($criteria);
	}


	/**
	 * Возвращает объект City для офлайновго магазина, в случае его наличия
	 * в связанной таблице cat_store_geo
	 *
	 * @return City|null
	 */
	public function getCityOfflineStore()
	{
		$city = null;

		if ($this->type == Store::TYPE_ONLINE) {
			return null;
		}

		$storeGeo = StoreGeo::model()->findByAttributes(array(
			'store_id' => $this->id,
			'type' => StoreGeo::TYPE_CITY
		));

		if ($storeGeo) {
			$city = City::model()->findByPk($storeGeo->geo_id);
		}


		return $city;
	}

    public static function getStoreInCity($ids, $city_id) {
        $sql = "SELECT store_id FROM cat_store_city WHERE store_id IN (".$ids.") AND city_id = :city_id";
        $rez = Yii::app()->db->createCommand($sql)
            ->bindParam(":city_id", $city_id)
            ->queryColumn();
     $stores = Store::model()->findAll("id IN(".implode(',',$rez).")");
     return $stores;
    }
}