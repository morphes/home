<?php

/**
 * Description of BaseUser
 *
 * @author alexsh
 */
class BaseUser extends EActiveRecord implements IUploadImage
{
	private $_data = null; // переменная для объекта доп. свойств пользователя.
	public $promo_code; // Промо код для дизайнеров
        public $promo_code_verify = false; // Статус промо-кода
	
	// Роли пользователей
	const ROLE_POWERADMIN 		= 1;  // 'Poweradmin'
	const ROLE_USER 		= 2;  // 'User'
	const ROLE_SPEC_FIS 		= 3;  // 'SpecFis'
	const ROLE_SPEC_JUR 		= 4;  // 'SpecJur'
        const ROLE_ADMIN 		= 5;  // 'Admin'
        const ROLE_GUEST 		= 6;  // 'Guest'
	const ROLE_MODERATOR 		= 7;  // 'Moderator'
        const ROLE_JUNIORMODERATOR 	= 8;  // 'Juniormoderator'
	const ROLE_FREELANCEEDITOR 	= 9;  // 'Freelanceeditor'  Внештатный редактор
	const ROLE_SALEMANAGER 		= 10; // 'Salemanager'
	const ROLE_SEO 			= 11; // 'Seo'
	const ROLE_JOURNALIST 		= 12; // 'Journalist'
	const ROLE_SENIORMODERATOR	= 13; // 'Seniormoderator'
        const ROLE_STORES_ADMIN         = 14; // 'Stores manager'
        const ROLE_STORES_MODERATOR     = 15; // 'Stores moderator'
        const ROLE_FREELANCE_STORE      = 16; // 'Фрилансер, добавляющий магазины'
        const ROLE_FREELANCE_PRODUCT    = 17; // 'Фрилансер, добавляющий товары'
        const ROLE_FREELANCE_IDEA     	= 18; // 'Фрилансер, добавляющий идеи с товарами'
	const ROLE_MALL_ADMIN		= 19; // 'Администратор торгового центра'

	public static $roleNames = array(
		self::ROLE_POWERADMIN       => 'Poweradmin',
		self::ROLE_USER             => 'User',
		self::ROLE_SPEC_FIS         => 'SpecFis',
		self::ROLE_SPEC_JUR         => 'SpecJur',
		self::ROLE_ADMIN            => 'Admin',
		self::ROLE_GUEST            => 'Guest',
		self::ROLE_MODERATOR        => 'Moderator',
		self::ROLE_JUNIORMODERATOR  => 'Juniormoderator',
		self::ROLE_SALEMANAGER      => 'Salemanager',
		self::ROLE_SEO              => 'Seo',
		self::ROLE_JOURNALIST       => 'Journalist',
		self::ROLE_SENIORMODERATOR  => 'Seniormoderator',
		self::ROLE_STORES_ADMIN     => 'Stores manager',
		self::ROLE_STORES_MODERATOR => 'Stores moderator',
		self::ROLE_FREELANCE_STORE => 'Freelance store',
		self::ROLE_FREELANCE_PRODUCT => 'Freelance product',
		self::ROLE_FREELANCE_IDEA => 'Freelance idea',
		self::ROLE_MALL_ADMIN	    => 'Mall admin',
	);

	// Статусы пользователей
        const STATUS_NOT_ACTIVE 		= 1; // не активен
        const STATUS_ACTIVE 		= 2; // активен
        const STATUS_BANNED 		= 3; // забанен
        const STATUS_ACTIVATE_REJECTED 	= 4; // запрос на активацию отклонен
        const STATUS_MODERATE 		= 5; // на модерации
        const STATUS_DELETED 		= 6; // удален
        const STATUS_VERIFYING 		= 7; // на подтверждении своего аккаунта

	const EXPERT_NONE = 0;
	const EXPERT = 1;
	const EXPERT_TOP = 2;

	public static $expertNames = array(
		self::EXPERT_NONE => 'не эксперт',
		self::EXPERT => 'эксперт',
		self::EXPERT_TOP => 'ведущий эксперт',
	);

	public static $preview = array(
		'crop_23' => array(23, 23, 'crop', 80),
		'crop_30' => array(30, 30, 'crop', 80),
		'crop_45' => array(45, 45, 'crop', 80),
		// В шапке сайта, всплывашка при наведении
		'crop_80' => array(80, 80, 'crop', 80),
		'crop_120' => array(120, 120, 'crop', 80), // Страница экспертов
		'crop_150' => array(150, 150, 'crop', 80),
		'crop_180' => array(180, 180, 'crop', 80),
		'resize_540' => array(540, 540, 'resize', 80), // Для админки
	);

	// Тип изображения для загрузки
	private $_imageType = null;

	/**
         * @return array validation rules for model attributes.
         */
        public function rules()
        {
                // NOTE: you should only define rules for those attributes that
                // will receive user inputs.
		$rules = array(
			// ==-- Сценарии регистрации и редактирования --==

			array('email, password', 'required',
				'on' => 'reg-' . self::ROLE_USER . ', reg-' . self::ROLE_SPEC_JUR . ', reg-' . self::ROLE_STORES_ADMIN . ', reg-' . self::ROLE_STORES_MODERATOR . ', reg-' . self::ROLE_SPEC_FIS . ', reg-Shop, reg-Admin, reg-Moderator'),
			array('firstname', 'required',
				'on' => 'reg-' . self::ROLE_SPEC_JUR . ', reg-' . self::ROLE_STORES_ADMIN . ', reg-' . self::ROLE_STORES_MODERATOR . ', reg-' . self::ROLE_SPEC_FIS . ', reg-Shop, reg-Admin, reg-Moderator'),
			array('lastname', 'required',
				'on' => 'reg-' . self::ROLE_STORES_ADMIN . ', reg-' . self::ROLE_STORES_MODERATOR . ', reg-Shop, reg-Admin, reg-Moderator, designer-profile-edit, user-profile-edit'),
			array('phone', 'required',
				'on' => 'reg-' . self::ROLE_SPEC_FIS . ', reg-' . self::ROLE_SPEC_JUR . ', reg-Shop'),
			array('password', 'length', 'min' => 4, 'tooShort' => 'Не менее четырех символов',
				'on' => 'restore, password-edit, reg-' . self::ROLE_USER . ', reg-' . self::ROLE_SPEC_JUR . ', reg-' . self::ROLE_STORES_ADMIN . ', reg-' . self::ROLE_STORES_MODERATOR . ', reg-' . self::ROLE_SPEC_FIS),
			array('role', 'required', 'message' => 'Выберите тип пользоватля'),
			array('address', 'required',
				'on' => 'reg-Shop'),
			array('attach', 'file', 'types' => 'jpg, bmp, png, zip', 'maxFiles' => 2, 'maxSize' => 104857600000, 'allowEmpty' => true,
				'on' => 'reg-' . self::ROLE_SPEC_FIS),
			array('login, password',
				'required',
				'on' => 'user-profile-edit'),
			array('firstname, email', 'required',
				'on' => 'designer-profile-edit, corporatedesigner-profile-edit, user-profile-edit'),
			array('city_id', 'exist', 'allowEmpty' => true, 'message' => 'Выберите город из списка', 'className' => 'City', 'attributeName' => 'id',
				'on' => 'reg-' . self::ROLE_USER . ', user-profile-edit'),
			array('city_id', 'exist', 'allowEmpty' => false, 'message' => 'Выберите город из списка', 'className' => 'City', 'attributeName' => 'id',
				'on' => 'reg-' . self::ROLE_SPEC_FIS . ', reg-' . self::ROLE_SPEC_JUR . ', designer-profile-edit, corporatedesigner-profile-edit'),
			array('login', 'length', 'min' => 1, 'max' => 45,
				'on' => 'reg-' . self::ROLE_USER . ', reg-' . self::ROLE_SPEC_JUR . ', reg-' . self::ROLE_SPEC_FIS . ', reg-' . self::ROLE_STORES_ADMIN . ', reg-' . self::ROLE_STORES_MODERATOR . ', reg-Shop, reg-Admin, reg-Moderator'),
			array('login',
				'unique',
				'attributeName' => 'login',
				'className'     => 'User',
				'message'       => 'Этот логин уже занят',
				'on'            => 'reg-' . self::ROLE_USER . ', reg-' . self::ROLE_SPEC_JUR . ', reg-' . self::ROLE_SPEC_FIS . ', reg-' . self::ROLE_STORES_ADMIN . ', reg-' . self::ROLE_STORES_MODERATOR . ', reg-Shop, user-profile-edit'),
			array('login',
				'match',
				'pattern' => '/^[_a-zA-Z0-9]+$/',
				'message' => 'Можно использовать только латинские буквы и цифры',
				'on'      => 'reg-' . self::ROLE_USER . ', reg-' . self::ROLE_SPEC_JUR . ', reg-' . self::ROLE_SPEC_FIS . ', reg-' . self::ROLE_STORES_ADMIN . ', reg-' . self::ROLE_STORES_MODERATOR . ', reg-Shop, user-profile-edit'),
			array('email',
				'unique',
				'attributeName' => 'email',
				'className'     => 'User',
				'message'       => 'Такой Email уже занят',
			),
			array('email',
				'specEmailValidator',
				'on' => 'reg-' . self::ROLE_SPEC_FIS . ', reg-' . self::ROLE_SPEC_JUR . ', reg-' . self::ROLE_USER . ', reg-' . self::ROLE_STORES_ADMIN . ', reg-' . self::ROLE_STORES_MODERATOR),
			array('promo_code', 'promoCodeCheck',
				'on' => 'reg-' . self::ROLE_SPEC_FIS . ', reg-' . self::ROLE_SPEC_JUR),
			array('lastname', 'length', 'min' => 2, 'max' => 45,
				'on'  => 'reg-' . self::ROLE_SPEC_FIS),
			//array('captcha', 'captcha', 'allowEmpty' => !extension_loaded('gd'), 'on' => 'reg-user, reg-shop, reg-designer, reg-contractor'),
			// ==-- Сценарий авторизации --==

			array('login, password', 'required', 'on' => 'login'),
			array('rememberMe', 'boolean', 'on' => 'login'),
			//array('password', 'authenticate',	'on' => 'login'),

			array('password', 'required', 'on' => 'restore'),
			array('old_password, password', 'required', 'on' => 'password-edit'),
			// ==-- Валидация полей --==

			array('status, expert_type', 'numerical', 'integerOnly' => true),
			array('referrer_id', 'numerical'),
			array('phone', 'length', 'min' => 4, 'max' => 45),
			array('secondname, address', 'length', 'min' => 2, 'max' => 45),
			array('firstname', 'length', 'min' => 2, 'max' => 75),
			array('password, activateKey, promo_code, tmpPass', 'length', 'max' => 32),
			array('email', 'length', 'max' => 50),
			array('email', 'email', 'message' => 'Некорректный формат'),
			// ==-- Поля, участвующие в поиске --==

			array('city_id, id, login, password, email, status, activateKey, firstname, lastname, secondname, referrer_id, image, address, phone, referrer, expert_type, service_city',
				'safe',
				'on' => 'search'),
                );

                return $rules;
        }

	/**
         * @return array relational rules.
         */
        public function relations()
        {
                // NOTE: you may need to adjust the relation name and the related
                // class name for the relations automatically generated below.
                return array(
                    'promocode' => array(self::HAS_ONE, 'Promocode', 'promocode_id'),
                    'city' => array(self::BELONGS_TO, 'City', 'city_id'),
                    'services' => array(self::MANY_MANY, 'Service', 'user_service(user_id, service_id)'),
                );
        }

        /**
         * @return array customized attribute labels (name=>label)
         */
        public function attributeLabels()
        {
                return array(
			'id'           => 'ID',
			'login'        => 'Логин',
			'password'     => 'Пароль',
			'old_password' => 'Старый пароль',
			'role'         => 'Тип пользователя',
			'captcha'      => 'Защитный код',
			'rememberMe'   => 'Запомнить меня',
			'email'        => 'Адрес электронной почты',
			'activateKey'  => 'Activate Key',
			'name'         => array('default' => 'Имя и фамилия', self::ROLE_SPEC_JUR => 'Название компании'),
			'city_id'      => 'Город',
			'image'        => 'Фотография',
			'attach'       => 'Загрузить файлы',
			'address'      => 'Адрес',
			'phone'        => 'Телефон',
			'enter_time'   => 'Был на сайте',
			'update_time'  => 'Обновлен',
			'create_time'  => 'Зарегистрирован',
			'group'        => 'Группа',
			'status'       => 'Статус',
			'promo_code'   => 'Промо код',
			'promocode_id' => 'Промо код',
			'firstname'    => array('default' => 'Имя', self::ROLE_SPEC_JUR => 'Название компании'),
			'lastname'     => 'Фамилия',
			'secondname'   => 'Отчество',
			'contact_face' => 'Контактное лицо',
			'tmpPass'      => 'Разовый пароль',
			'referrer'     => 'Кем приглашен',
			'expert_type'  => 'Вид эксперта',
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
                $criteria->compare('login', $this->login, true);
                $criteria->compare('password', $this->password, true);
                $criteria->compare('email', $this->email, true);
                $criteria->compare('status', $this->status);
                $criteria->compare('activateKey', $this->activateKey, true);
                $criteria->compare('firstname', $this->firstname, true);
                $criteria->compare('lastname', $this->lastname, true);
                $criteria->compare('secondname', $this->secondname, true);
                $criteria->compare('image', $this->image, true);
                $criteria->compare('address', $this->address, true);
                $criteria->compare('update_time', $this->update_time);
                $criteria->compare('create_time', $this->create_time);

                return new CActiveDataProvider($this, array(
                            'criteria' => $criteria,
                        ));
        }
	
	/**
         * @return string the associated database table name
         */
        public function tableName()
        {
                return 'user';
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
         * Метод, который возвращает экземпляр класса, хранящий дополнительные
         * свойство текущего пользователя.
         * @return UserData
         */
        public function getData()
        {
                if (is_null($this->_data)) {
                        $this->_data = UserData::model()->findByPk($this->id);
                        // Если получить объект не удалось, то возвращаем новый экземпляр.
                        if (!$this->_data)
                                $this->_data = new UserData();
                }
                return $this->_data;
        }

        /**
         * Метод, который возвращает экземпляр класса, хранящий дополнительные
         * свойство текущего пользователя.
         * @return UserData
         */
        public function setData()
        {
                if (is_null($this->_data)) {
                        $this->_data = UserData::model()->findByPk(Yii::app()->user->id);
                }

                return $this->_data;
        }
	
	/**
         * Валидатор. Проверка введеного промокода.
         * 
         * @param string $attribute
         * @param array $params 
         */
        public function promoCodeCheck($attribute, $params)
        {
                if (isset($this->promo_code) && !empty($this->promo_code)) {

                        // Ищем введенный промокод.
                        $model = Promocode::model()->findByAttributes(array('name' => Amputate::strtolower_cyr($this->promo_code)));

                        if (!$model)
                                $this->addError('promo_code', 'Некорректный ' . $this->getAttributeLabel('promo_code') . '. Оставьте это поле пустым, если ' . $this->getAttributeLabel('promo_code') . ' вам не известен');
                        else {
                                // Если есть то отмечаем, что промокод совпадает
                                // с одинм из списка и сохраняем у пользователя его ID
                                $this->promo_code_verify = true;
                                $this->promocode_id = $model->id;
                        }
                }
        }
	
	public function specEmailValidator($attribute, $params)
	{
		$user = User::model()->findByAttributes(array('email'=>$this->email)); 
		
		if (is_null($user) || !$this->getIsNewRecord()) {
			return;
		}
		
		// has errors
		if (is_null($user->referrer_id)) {
			$this->addError ('email', 'Такой Email уже занят');
			return;
		}
		
		if ($user->status != User::STATUS_VERIFYING) { // пользователь активирован
			$this->addError ('email', 'Этот адрес уже зарегистрирован. '.CHtml::link('Забыли пароль?', Yii::app()->homeUrl.'/password/remember'));
			return;
		}
		// пользователь не активирован
		$cacheKey = 'User_emailDateSend_'.$user->id;
		$time = Yii::app()->cache->get($cacheKey);
		if ( !$time || time()-$time > 86400 ) { 
			//4
			$this->addError ('email', 'Этот адрес уже зарегистрировал наш менеджер, когда отправлял вам приглашение. '
				.CHtml::link('Восстановить доступ', Yii::app()->homeUrl.'/site/activation'));
			Yii::app()->session->add('User_emailForActivate', $user->email);
			return;
		}
		$this->addError ('email', 'Этот адрес уже зарегистрировал наш менеджер, когда отправлял вам приглашение. Проверьте вашу почту.');
	}
	
	/**
	 * Проверка владения
	 * @return boolean 
	 */
	public function getIsOwner()
	{
		return true;
	}
	
	/**
	 * Для комментариев, получение id владельца
	 * @return type 
	 */
	public function getAuthor_id()
	{
		return $this->id;
	}


	// IUploadImage
	public function getImagePath()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'user': return 'user/'.intval($this->id / UploadedFile::PATH_SIZE + 1).'/'.$this->id;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getImageName()
	{
		if ( $this->getIsNewRecord())
			return false;

		switch ($this->_imageType) {
			case 'user': return time() . '_avatar_'.Amputate::rus2filename($this->name) .'_'. $this->id;
			default: throw new CException('Invalid upload image type');
		}
	}

	public function getAuthorId()
	{
		return $this->id;
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
		return !is_null($this->id) && $this->id == Yii::app()->user->id;
	}

	public function imageConfig()
	{
		switch ($this->_imageType) {
			case 'user': return array(
						'realtime' => array(
							self::$preview['crop_23'],
							self::$preview['crop_180'],
						),
						'background' => array(
							self::$preview['crop_45'],
							self::$preview['crop_150'],
						),
					);
			default: throw new CException('Invalid upload image type');
		}
	}
	// end of IUploadImage
}
