<?php

/**
 * @brief This is the model class for table config.
 * @details The followings are the available columns in table 'config'
 */
class Config extends EActiveRecord
{
        // idea types
        const PORTFOLIO = 0;
        const INTERIOR = 1;
        const ARCHITECTURE = 2;
	const INTERIOR_PUBLIC = 3;
        // limits for interior
        const MAX_INTCONTENT_IMAGE = 15;
        const MAX_LAYOUT_IMAGE = 10;
        const MAX_PORTFOLIO_IMAGE = 30;
        const MAX_INTCONTENT_COUNT = 10;
        const MAX_INTCOAUTHORS = 3;
        
        public static $adminIps = array(
            '213.228.88.111', // office
	    '92.125.138.55', //mamykin
            '178.49.111.76', // melnikov
            '5.44.169.149', // mamykin
	    '176.51.79.167', // mamykin
	    '95.191.202.152', // mamykin
	    '37.193.24.74', // mamykin
	    '90.189.153.166', // mamykin
	    '5.129.78.68', // gromov
            '213.87.121.164', // vashenko
	    '91.221.199.113',
            '127.0.0.1',
            '192.168.56.1',
            '::1'
        ); // доверенные IP пользователей
        const MAX_TRY_AUTH_BEFORE_BAN = 10; // допустимое кол-во ошибок ввода временного пароля
        const BAN_TIME_BEFORE_NEXT_LOGIN_ATTEMP = 600; // время бана пользователя, допустившего MAX_TRY_AUTH_BEFORE_BAN ошибок ввода временного пароля
        
        // Директория для загрузки вложений личных сообщений
        const PM_UPLOAD_PATH = 'uploads/protected/attachments';
        const REG_UPLOAD_PATH = 'uploads/protected/attachments/reg';

	const UPLOAD_PATH_PRICE_LIST = 'uploads/public/price';

        // config for get preview (width, height, method, quality)
	public static $preview = array(
		'resize_1920x1080' => array(1920, 1080, 'resize', 90, 'watermark' => true, 'decrease' => true),
		'resize_710x475' => array(710, 475, 'resize', 90),
		'crop_700x450' => array(700, 450, 'crop', 90),
		'crop_220x175' => array(220, 175, 'crop', 90),
		'crop_460x365' => array(460, 365, 'crop', 90),
		'resize_190' => array(190, 190, 'resize', 80), // user avatar in admin panel
                'resize_140' => array(140, 140, 'resize', 80),
		'resize_120_border' => array(120, 120, 'resize', 80, 'border'=>true),
		'crop_230' => array(230, 230, 'crop', 80),
		'crop_220' => array(220, 220, 'crop', 80),
		'crop_210' => array(210, 210, 'crop', 80),
		'crop_180' => array(180, 180, 'crop', 80),
		'crop_150' => array(150, 150, 'crop', 80),
		'crop_130' => array(130, 130, 'crop', 80),
		'crop_80' => array(80, 80, 'crop', 80),
                'crop_78' => array(78, 78, 'crop', 80),
		'crop_60' => array(60, 60, 'crop', 80),
		'crop_45' => array(45, 45, 'crop', 80),
		'crop_23' => array(23, 23, 'crop', 80),
		'crop_25' => array(25, 25, 'crop', 80),
		'crop_398x344' => array(398, 344, 'crop', 80), // Для главной, модуль архитектуры
		'crop_380' => array(380, 380, 'crop', 80), // New user profile (for projects)
	);
        // available ideas page sizes
//        public static $ideasPageSizes = array(24 => '24' , 48 => '48', 60 => '60', 120 => '120');
        public static $ideasPageSizes = array(23 => '24' , 47 => '48', 59 => '60', 119 => '120');
        // columns count in ideas page
        public static $ideasPageColumns = 3;
        // available idea image page sizes
        public static $ideaImagePageSizes = array(20 => 20, 40 => 40, 60 => 60);
	// page sizes for user's list
        public static $userPageSizes = array(20 => '20', 50 => '50', 100 => '100');
	// Количество сообщений в ленте сообщений личного кабинета
        public static $messagePageSizes = array(20 => 20, 50 => 50, 100 => 100);
        // Количество отзывов о товаре на странице
        public static $productFeedbackPageSizes = array(10 => 10, 20 => 20, 50 => 50);
        // Количество отзывов о магазине на странице
        public static $storeFeedbackPageSizes = array(10 => 10, 20 => 20, 50 => 50);
        // Кол-во товаров на странице каталога
        public static $productFilterPageSizes = array(30 => 30, 60 => 60, 90 => 90, 120 => 120);
	// Количество статей на одной странице для раздела "Журнал"
	public static $mediaPageSizes = array(10 => 10, 20 => 20, 50 => 50);
	// Количество статей на одной странице для раздела "Форум"
	public static $forumPageSizes = array(10 => 10, 20 => 20, 50 => 50);
        // Количество элементов на странице результатов глобального поиска
        public static $searchPageSizes = array(10 => 10, 20 => 20, 50 => 50);
        // Типы идей
        public static $ideaTypes = array(
            self::INTERIOR => 'Interior',
            self::INTERIOR_PUBLIC => 'Interiorpublic',
            self::ARCHITECTURE => 'Architecture',
        );
        public static $ideaTypesName = array(
		self::INTERIOR => 'Интерьер',
		self::ARCHITECTURE => 'Архитектура',
		self::INTERIOR_PUBLIC => 'Общественный интерьер',
        );
        /**
         * Типы проектов 
         * если непонятно, зачем он похож на два предыдущих, то все вопросы ко мне - Рома 
         * @var type 
         */
        public static $projectTypes = array(
            	self::PORTFOLIO 	=> 'Portfolio',
            	self::INTERIOR => array(self::INTERIOR=>'Interior', self::INTERIOR_PUBLIC=>'Interiorpublic'),
		self::ARCHITECTURE => 'Architecture',
        );
        // autocomplete page size
        public static $ACompletePageSize = 5;
	
	// перечень ролей всех пользователей ( используется для рассылки)
	public static $userRoles = array(
	    '' => 'Все пользователи',
	    User::ROLE_ADMIN => 'Администраторы',
	    User::ROLE_SPEC_FIS => 'Дизайнеры',
	    User::ROLE_JUNIORMODERATOR => 'Младшие модераторы',
	    User::ROLE_MODERATOR => 'Модераторы',
	    User::ROLE_JOURNALIST => 'Журналист',
	    User::ROLE_POWERADMIN => 'Супер админы',
	    User::ROLE_USER => 'Пользователи',
	    User::ROLE_SPEC_JUR => 'Дизайнеры (юр. лицо)',
            User::ROLE_FREELANCE_IDEA => 'Фриланс Идеи',
            User::ROLE_FREELANCE_STORE => 'Фриланс Магазины',
            User::ROLE_FREELANCE_PRODUCT => 'Фриланс Товары',
            User::ROLE_SENIORMODERATOR => 'Старший модератор',
            User::ROLE_STORES_ADMIN => 'Администратор магазинов',
	    User::ROLE_MALL_ADMIN   => 'Администратор торгового ценра',
	);

	// Доступные роли для регистрации пользователя
        public static $rolesUserReg = array(
		User::ROLE_USER => 'Владелец квартиры', // ALWAYS FIRST, use in oauth register
		User::ROLE_SPEC_FIS => 'Специалист (физ. лицо)',
		User::ROLE_SPEC_JUR => 'Специалист (юр. лицо)',
                User::ROLE_STORES_ADMIN => 'Администратор магазинов',
                User::ROLE_STORES_MODERATOR => 'Модератор магазинов',
		User::ROLE_MALL_ADMIN   => 'Администратор торгового ценра',

        );
        // Роли администраторов сайта
        public static $rolesAdmin = array(
            User::ROLE_JUNIORMODERATOR => 'Младший модератор',
            User::ROLE_MODERATOR => 'Модератор',
            User::ROLE_SALEMANAGER => 'Менеджер продаж',
            User::ROLE_ADMIN => 'Администратор',
            User::ROLE_POWERADMIN => 'Суперадминистратор',
            User::ROLE_FREELANCE_STORE => 'Фриланс - магазины',
            User::ROLE_FREELANCE_PRODUCT => 'Фриланс - товары',
            User::ROLE_FREELANCE_IDEA => 'Фриланс - идеи',
	    User::ROLE_SEO => 'SEO человек',
	    User::ROLE_JOURNALIST => 'Журналист',
	    User::ROLE_SENIORMODERATOR => 'Старший модератор',
        );
        // Статусы пользователей
        public static $userStatus = array(
            User::STATUS_ACTIVE => 'Активен',
            User::STATUS_VERIFYING => 'На подтверждении',
            User::STATUS_NOT_ACTIVE => 'Отключен',
            User::STATUS_MODERATE => 'На модерации',
            User::STATUS_BANNED => 'Забанен',
            User::STATUS_ACTIVATE_REJECTED=>'Отклонен',
                //User::STATUS_DELETED => 'Удален',
        );
        public static $banTimes = array(
            24 => 'на 24 часа',
            120 => 'на 5 дней',
            240 => 'на 10 дней',
            480 => 'на 20 дней',
            87600 => 'навсегда',
        );

	// Доступные модели для комментирования
	public static $commentType = array(
		'Interior' => 'Интерьеры',
		'Interiorpublic' => 'Общественные интерьеры',
		'User' => 'Пользователи',
		'News' => 'Новости',
		'Portfolio' => 'Портфолио',
		'Architecture' => 'Архитектура',
		'MediaKnowledge' => 'Журнал знания',
		'MediaNew' => 'Журнал новости',
                'Product' => 'Товары',
		'MediaEvent' => 'Календарь событий',
		'StoreNews' => 'Новости магазинов'
	);

	// Доступные модели для добавления в Избранное
	public static $favoriteType = array(
		'Interior' => 'Интерьер',
		'Interiorpublic' => 'Общественный интерьер',
		'Architecture' => 'Архитектура',
		'InteriorContent' => 'Помещение',
		'Portfolio' => 'Портфолио',
		'User' => 'Пользователь',
		'MediaKnowledge' => 'Журнал. Знания',
		'MediaNew' => 'Журнал. Новости',
                'Product' => 'Товары',
                'StoreNews' => 'Новости Магазинов',
		'UploadedFile'=>'Изображения',
	);

	// Доступные модели для Лайков
	public static $likeType = array(
		'MediaKnowledge' => 'Журнал. Знания',
		'MediaNew' => 'Журнал. Новости',
	);
        
        // site errors
        static public $errors = array(
            102 => 'Документ на обработке',
            204 => 'Отсутствует содержимое',
            400 => 'Некорректный запрос',
            401 => 'Необходима авторизация',
            403 => 'Доступ запрещен',
            404 => 'Документ не найден',
            408 => 'Время ожидания истекло',
            410 => 'Документ удален',
            456 => 'Некорректируемая ошибка',
            500 => 'Внутренняя ошибка сервера',
        );

        // Доступные стажи работы в разных типах услуг
        public static $experienceType = array(
                0 => 'Не указан',
                1 => 'До 2-х лет',
                2 => '2-5 лет',
                3 => '5-10 лет',
                4 => 'Больше 10',
        );
	// Доступные сегменты для специалистов
	public static $segmentName = array(
	    0 => 'Не указан',
	    1 => 'Эконом',
	    2 => 'Средний',
	    3 => 'Премиум',
	);
	
	const IDEA_SORT_RELEVANCE = 1;
	const IDEA_SORT_DATE = 0;
	const IDEA_SORT_RATING = 2;
	public static $ideaSortNames = array(
	    self::IDEA_SORT_DATE => 'дате',
	    self::IDEA_SORT_RELEVANCE => 'релевантности',
	    self::IDEA_SORT_RATING => 'рейтингу',
	);

//	const SPEC_SORT_DEFAULT = 0;
	const SPEC_SORT_RATING = 1;
	const SPEC_SORT_PROJECTS = 2;
	public static $specSortNames = array(
//		self::SPEC_SORT_DEFAULT => 'по умолчанию',
		self::SPEC_SORT_RATING => 'по рейтингу',
		self::SPEC_SORT_PROJECTS => 'по числу проектов',
	);
	
	//const IDEA_STYLE_GROUP_
	public static $ideaStyleGroups = array(
	    '1' => 'Классический',
	    '2' => 'Современный',
	    '3' => 'Этнический',
	    '4' => 'Смешанный',
	);
	// для роутинга стилей
	public static $ideaEngStyles = array(
		'classic' => 1,
		'modern' => 2,
		'ethnitical' => 3,
		'mixed' => 4,
	);

	// Список групп стилей для фильтра Архитектуры
	public static $architectureStyleGroups = array(
		'1' => 'Классический',
		'2' => 'Современный',
		'3' => 'Этнический',
	);
	
	const SPEC_TYPE_ALL = 0;
	const SPEC_TYPE_PHYS = 1;
	const SPEC_TYPE_JUR = 2;
	public static $specTypeNames = array(
	    self::SPEC_TYPE_ALL => 'частные лица и компании',
	    self::SPEC_TYPE_PHYS => 'частные лица',
	    self::SPEC_TYPE_JUR => 'компании',
	);

	/**
         * Константы баллов для расчета рейтинга
         * @see http://pt.myhome.ru/rating/
         */
        const B_ABOUT = 2.50;
        const B_AVATAR = 2.50;
        const B_COMMENT = 0.01;
        const B_WINS = 15;
        const B_PROJECT = 0.85;
        const B_AVG_PROJECT_RATING = 1;
        const B_IDEA_PROJECT = 10;
	
        const B_INCREMENT = 5; // повышающий коэфициент (добавляется к общему баллу пользователя)


	/**
	 * Константы разделов сайта для ротации баннера
	 */
	const SECTION_ALL = 1;
	const SECTION_HOME = 2;
	const SECTION_CATALOG = 3;
	const SECTION_IDEA = 4;
	const SECTION_SPECIALIST = 5;
	const SECTION_JOURNAL = 6;
	const SECTION_FORUM = 7;
	const SECTION_NEWS = 8;
	const SECTION_KNOWLEDGE = 9;
	const SECTION_EVENT = 10;
    const SECTION_PROFILE = 11;
    const SECTION_TENDER_LIST = 12;
    const SECTION_TENDER_ITEM = 13;

	/**
	 * @var array секции для ротации баннеров
	 */
	static public $sections = array(
		self::SECTION_ALL,
		self::SECTION_HOME,
		self::SECTION_CATALOG,
		self::SECTION_IDEA,
		self::SECTION_SPECIALIST,
		self::SECTION_JOURNAL,
		self::SECTION_FORUM,
		self::SECTION_NEWS,
		self::SECTION_KNOWLEDGE,
		self::SECTION_EVENT,
        self::SECTION_PROFILE,
        self::SECTION_TENDER_LIST,
        self::SECTION_TENDER_ITEM,
	);

	/**
	 * @var array id категорий, товары которых выводятся в каталоге выше при сортировке "по-умолчанию"
	 */
	static public $prioritizedCategories = array(
		9, // диваны
		20, // кровати
		13, // шкафы
		26, // обеденные столы
		28, // стулья
		61, // кухонные гарнитуры
		49, // подвесные светильники
		50, // настенные светильники
		51, // потолочные светильники
		52, // настенно-потолочные светильники
		53, // настольные светильники
		11, // кресла
		96, // ковры
		74, // настеные часы
		24, // комоды
		47, // тумбы прикроватные
	);

	const SHARE_DEFAULT_MESSAGE = 'Подбор мебели и аксессуаров, оригинальные решения интерьеров со всего мира, поиск дизайнеров и специалистов по ремонту.  MyHome – нтернет-помощник на всех этапах создания домашнего уюта.';

	/**
         * Путь до картинки заглушки в email (для определения момента открытия письма)
         */
        public static $emailStubPic = 'img/emailstub.png';
	/** @var array массив email для отправки уведомлений (сейчас только тендеры) */
	public static $adminEmails = array('darver@myhome.ru', 'shas@myhome.ru', 'hra@myhome.ru', 'midmelnikov@gmail.com');

	public static $banners = array(
		array('src'=>'img/banner/01-sofas.png', 'url'=>'/catalog/sofas'),
		array('src'=>'img/banner/02-wpapers.png', 'url'=>'/catalog/wallpapers'),
		array('src'=>'img/banner/03-lights.png', 'url'=>'/catalog/pendants'),
		array('src'=>'img/banner/04-kitchen.png', 'url'=>'/catalog/kitchen_sets'),
		array('src'=>'img/banner/05-bed.png', 'url'=>'/catalog/beds'),
		array('src'=>'img/banner/06-tiles.png', 'url'=>'/catalog/ceramic_tile'),
		array('src'=>'img/banner/07-doors.png', 'url'=>'/catalog/interior_doors'),
		array('src'=>'img/banner/08-chair.png', 'url'=>'/catalog/armchairs'),
		array('src'=>'img/banner/09-bath.png', 'url'=>'/catalog/baths'),
		array('src'=>'img/banner/10-childroom.png', 'url'=>'/catalog/nurseries'),
		array('src'=>'img/banner/11-acc.png', 'url'=>'/catalog/decoration_acsessories'),
	);
	/**
         * Returns the static model of the specified AR class.
         * @return Config the static model class
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
                return 'config';
        }

        /**
         * @return array validation rules for model attributes.
         */
        public function rules()
        {
                // NOTE: you should only define rules for those attributes that
                // will receive user inputs.
                return array(
                    array('key, value', 'required'),
                    array('key', 'numerical', 'integerOnly' => true),
                    array('value', 'length', 'max' => 255),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('key, value', 'safe', 'on' => 'search'),
                );
        }

        /**
         * @return array customized attribute labels (name=>label)
         */
        public function attributeLabels()
        {
                return array(
                    'key' => 'Key',
                    'value' => 'Value',
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

                $criteria->compare('key', $this->key);
                $criteria->compare('value', $this->value, true);

                return new CActiveDataProvider($this, array(
                            'criteria' => $criteria,
                        ));
        }

	/**
	 * @static
	 * @return array Возвращает массив, полученный из Config::$projectTypes, только
	 * все массивные значения урезаны до одного элемента.
	 */
	public static function getProjectTypesPlain()
	{
		$configTypes = array();
		foreach (Config::$projectTypes as $id=>$type) {
			if (is_array($type))
				$configTypes[$id] = $type[self::INTERIOR];
			else
				$configTypes[$id] = $type;
		}

		return $configTypes;
	}

	/**
	 * Данные домена для установки cookie
	 * @return mixed
	 */
	public static function getCookieDomain()
	{
		$params = session_get_cookie_params();
		return $params['domain'];
	}

	public static function getBannerData()
	{
		$id = rand(0, count(self::$banners)-1);
		return self::$banners[$id];
	}
}
