<?php

/**
 * @brief This is the model class for table "user".
 *
 * @details The followings are the available columns in table 'user':
 * @param integer $id
 * @param string $login
 * @param string $password
 * @param string $email
 * @param integer $status
 * @param integer $expert_type
 * @param string $activateKey
 * @param string $name
 * @param string $firstname
 * @param string $lastname
 * @param string $secondname
 * @param string $image
 * @param string $referrer_id
 * @param string $address
 * @param integer $role
 * @param integer $update_time
 * @param integer $create_time
 *
 */

Yii::import('application.models._base.BaseUser');

class User extends BaseUser
{

    const cookieUID = '__uid';

    private $_identity;
    private $_name; // Компонованное firstname и lastname
    private $_referrer; // Экземпляр модели пользователя, пригласившего текущего на сайт
    private $_oldStatus; // Старое значение статуса после установки нового значения
    private $_city; // Объект города пользователя
    private $_hasSocial = null; // Флаг привязанных соц аккаунтов

    public $captcha;
    public $old_password; // Используется на странице смены пароля
    public $tmpPass; // Временный пароль для входа администратора со "стороннего" IP
    public $rememberMe;
    // Количество работ, допущенных в идеи и кол-во коментов.
    // Оба значения возварщаются из sphinx'а
    public $count_interior;
    public $count_comment;
    public $rating;
    public $experience;
    public $service_expert; // Флаг о том, что пользователь эсксперт по конкретной услуге
    public $attach;
    public $service_city; // Ид города, в котором оказывает услуги юзер (для поиска)

    // Флаг, при выставлении которого выполнение события afterSave прерывается
    public $afterSaveCommit = false;

    // Список полей, которые должны быть за encode'ны при присваивании значения
    protected $encodedFields = array('firstname', 'secondname', 'phone', 'lastname');

    //Текс который выводиться при успешном завершение регистрации
    public static $message = 'На указанный вами адрес электронной почты было отправлено письмо с подтверждением регистрации. Пройдите по ссылке в письме для завершения регистрации.';


    public function init()
    {
        parent::init();

        Yii::import('application.modules.member.models.*');

        $this->onBeforeSave = array($this, 'setDate');
        $this->onBeforeSave = array($this, 'phoneSearch');
        $this->onAfterSave = array($this, 'logActivation');
        $this->onAfterSave = array($this, 'countActivityRating');
        $this->onAfterSave = array($this, 'updateSphinx');
        $this->onAfterSave = array($this, 'setSpecCity');
    }

    /**
     * Переопределяем метод, чтобы все события по afterSave запускались только вручную.
     * Это нужно для тех случаев, когда выполняется сохранение пользователя с транзакцией.
     * @param bool $manualRun
     */
    public function afterSave($manualRun = false)
    {
        if ($this->afterSaveCommit == false || ($this->afterSaveCommit == true && $manualRun == true))
            parent::afterSave();
    }

    public function updateSphinx()
    {
        Yii::app()->gearman->appendJob('sphinx:user_login', $this->id);
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

    public function __set($name, $value)
    {
        if ($name == 'firstname' || $name == 'lastname')
            $value = trim($value);

        $setter = 'set' . $name;
        if (method_exists($this, $setter))
            return $this->$setter($value);
        else
            return parent::__set($name, $value);
    }

    /**
     * Returns the static model of the specified AR class.
     * @return User the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Логирование даты активации пользователя (для всех пользователей, с пометкой кто зарегистрировал)
     */
    public function logActivation()
    {
        if (!is_null($this->_oldStatus)
            && $this->_oldStatus != $this->status
            && $this->status === self::STATUS_ACTIVE
        ) {
            $log = Yii::app()->db->createCommand()
                ->select('*')
                ->from('log_user_activation log')
                ->where('user_id=:id', array(':id' => $this->id))
                ->queryRow();

            if (empty($log)) {
                Yii::app()->db->createCommand()->insert('log_user_activation', array(
                        'user_id' => $this->id,
                        'referrer_id' => $this->referrer_id,
                        'activate_time' => time(),
                        'create_time' => $this->create_time,
                    )
                );
            }

        }
    }

    /**
     * Расширяет метод получения имени свойства модели.
     * Появилась возможность в качестве имени свойства указать массив key-value
     * array( 'default' => 'Имя по-умолчанию', <имя роли> = "Название поля для конкретной роли")
     *
     * @param string $attribute Название атрибута, для которого надо взять имя
     * @return string Название поля
     */
    public function getAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();

        if (isset($labels[$attribute]) && is_array($labels[$attribute])) {
            $role = Yii::app()->user->role;
            if (array_key_exists($role, $labels[$attribute])) {
                return $labels[$attribute][$role];
            } else {
                return $labels[$attribute]['default'];
            }
        } else
            return parent::getAttributeLabel($attribute);
    }

    /**
     * Logs in the user using the given username and password in the model.
     * @return boolean whether login is successful
     */
    public function login($useMD5 = false)
    {

        if ($this->_identity === null) {
            $this->_identity = new UserIdentity($this->login, $this->password, Yii::app()->request->getUserHostAddress(), $this->tmpPass);
            $this->_identity->authenticate($useMD5);
        }

        if ($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
            $duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days

            Yii::app()->user->login($this->_identity, $duration);
            // данные для сверки прав доступа (исп-ся для админов @see UserService)
            $userData = array(
                'role' => Yii::app()->getUser()->getRole(),
                'ip' => ip2long(Yii::app()->getRequest()->getUserHostAddress()),
                'userAgent' => md5(Yii::app()->request->getUserAgent()),
            );
            Yii::app()->getUser()->setState('userData', $userData);
            User::model()->updateByPk(Yii::app()->user->id, array('enter_time' => time()));
            return true;
        } elseif ($this->_identity->errorCode == UserIdentity::ERROR_BANNED) {
            // Получаем время бана

            $ban_time = $this->_identity->ban_time;
            $this->addError('login', 'Аккаунт забанен. Дата возможной авторизации ' . date('d.m.Y H:i', $ban_time) . '.');
            return false;
        } elseif ($this->_identity->errorCode == UserIdentity::ERROR_TMPPASS) {
            $this->addError('tmpPass', 'Разовый пароль введен неверно');
            return false;
        } elseif ($this->_identity->errorCode == UserIdentity::ERROR_TMPBANNED) {
            throw new CHttpException(403, 'Ваш IP адрес забанен на ' . Config::BAN_TIME_BEFORE_NEXT_LOGIN_ATTEMP / 60 . ' минут');
        } else {
            $this->addError('password', 'Неверный пользователь или пароль');
            return false;
        }
    }

    /**
     * Return an error code of login-script
     * @return integer
     */
    public function loginErrorCode()
    {
        if (!is_null($this->_identity))
            return $this->_identity->errorCode;
        else
            return UserIdentity::ERROR_UNKNOWN_IDENTITY;
    }

    /**
     * Генерит ключ активации для пользователя.
     *
     * @return string
     */
    public static function generateActivateKey()
    {
        return substr(md5(uniqid(rand(), true)), 0, rand(10, 15));
    }

    public static function regenActivateKey($user)
    {
        if (!$user instanceof User)
            return false;

        $user->activateKey = self::generateActivateKey();
        $user->save(false);

        return true;
    }

    /*
     * Вариант без кэширования пути
    public function getPreview($config)
    {
    if (!is_null($this->image_id)) {
        $uploadedFile = UploadedFile::model()->findByPk($this->image_id);
        if (!is_null($uploadedFile)) {
            $previewFile = $uploadedFile->getPreviewName($config, 'user');
            return $previewFile;
        }
    }
    $name = $config[0].'x'.$config[1];
    return UploadedFile::getDefaultImage('user', $name);
    }
    */

    /**
     * Get image preview path
     * @param array $config
     */
    public function getPreview($config)
    {
        if (is_null($this->image_id)) {
            $name = $config[0] . 'x' . $config[1];
            return UploadedFile::getDefaultImage('user', $name);
        }

        $key = 'User:preview:' . $this->image_id . ':' . serialize($config);
        $preview = Yii::app()->cache->get($key);
        if (!$preview) {
            $uploadedFile = UploadedFile::model()->findByPk($this->image_id);
            if (!is_null($uploadedFile)) {
                $preview = $uploadedFile->getPreviewName($config, 'user');
            } else {
                $name = $config[0] . 'x' . $config[1];
                $preview = UploadedFile::getDefaultImage('user', $name);
            }
            Yii::app()->cache->set($key, $preview, Cache::DURATION_IMAGE_PATH);
        }
        return $preview;
    }

    /**
     * Returns the link to profile user.
     * @return url
     */
    public function getLinkProfile()
    {
        return Yii::app()->createUrl('/users', array('login' => $this->login));
    }

    /**
     * Return count of accepted interiors for selected user
     */
    public function getInteriorCount()
    {
        Yii::import('application.modules.idea.models.Interior');
        return Interior::model()->countByAttributes(array('author_id' => $this->id, 'status' => Interior::STATUS_ACCEPTED));
    }

    /**
     * Выборка пользователей по роли и статусу
     */
    public static function getUsersByRoles($roles = array(), $status = self::STATUS_ACTIVE, $limit = 40)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 't.*';

        foreach ($roles as $role) {
            $criteria->compare('t.role', $role, false, 'OR');
        }

        $criteria->compare('status', $status);
        $criteria->order = 'firstname';
        $criteria->limit = $limit;

        return self::model()->findAll($criteria);
    }

    /**
     * @brief Send mail with activation code for new user
     */
    public function activateKey($role)
    {


        // Отправляем любому пользователю сообщение о подтверждении регистрации.
        Yii::app()->mail->create('activateKey')
            ->to($this->email)
            ->params(array(
                'user_name' => $this->name,
                'activate_link' => CHtml::link(Yii::app()->homeUrl . '/site/activation/key/' . $this->activateKey, Yii::app()->homeUrl . '/site/activation/key/' . $this->activateKey),
                'sign_A' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage()
            ))
            ->priority(EmailComponent::PRT_HIGHT)
            ->send();


        return self::$message;
    }

    /**
     * Метод информирует пользователя о смене его статуса администратором (по Email)
     * @param integer $previous_status Предыдущий статус
     */
    public function notification($previous_status = null)
    {
        // Если пользователя активировали после его модерации, то отправляется письмо с кодом подтверждения email
        if ($previous_status == self::STATUS_VERIFYING && $this->status == self::STATUS_ACTIVE) {
            Yii::app()->mail->create('activateKey')
                ->to($this->email)
                ->params(array(
                    'site_name' => Yii::app()->name,
                    'activate_link' => CHtml::link(Yii::app()->homeUrl . '/site/activation/key/' . $this->activateKey, Yii::app()->homeUrl . '/site/activation/key/' . $this->activateKey),
                    'username' => $this->name,
                    'user_login' => $this->login,
                ))
                ->send();
            return true;
        }

        // Выбор шаблона сообщения для соответствующего статуса, установленного пользователю
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                $unsafe_pass = unserialize(Yii::app()->redis->get($this->id . '_pass'));
                Yii::app()->redis->delete($this->id . '_pass');
                Yii::app()->mail->create('userActivate')
                    ->to($this->email)
                    ->params(array(
                        'user_login' => $this->login,
                        'user_pass' => $unsafe_pass,
                    ))
                    ->send();

                break;
            case self::STATUS_NOT_ACTIVE:
                Yii::app()->mail->create('userDeactivate')
                    ->to($this->email)
                    ->params(array(
                        'username' => $this->name,
                        'user_login' => $this->login,
                        'sign_A' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
                    ))
                    ->send();
                break;
            case self::STATUS_BANNED:
                Yii::app()->mail->create('userBanned')
                    ->to($this->email)
                    ->params(array(
                        'username' => $this->name,
                        'user_login' => $this->login,
                        'time' => isset($_POST['ban-time']) ? (int)$_POST['ban-time'] . ' часов' : '87000 часов',
                        'sign_A' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
                    ))
                    ->send();
                break;
            case self::STATUS_MODERATE:
                Yii::app()->mail->create('userModerate')
                    ->to($this->email)
                    ->params(array(
                        'username' => $this->name,
                    ))
                    ->send();
                break;
            case self::STATUS_ACTIVATE_REJECTED:
                Yii::app()->mail->create('userActivateReject')
                    ->to($this->email)
                    ->params(array(
                        'username' => $this->name,
                    ))
                    ->send();
                break;
            case self::STATUS_DELETED:
                Yii::app()->mail->create('userDeleted')
                    ->to($this->email)
                    ->params(array(
                        'username' => $this->name,
                        'user_login' => $this->login,
                        'sign_A' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
                    ))
                    ->send();
                break;
        }

        return true;
    }

    /**
     * Возвращает скомпонованое firstname и lastname
     * @return string
     */
    public function getName()
    {
        if (is_null($this->_name)) {
            $this->_name = $this->firstname . ' ' . $this->lastname;
        }
        return $this->_name;
    }

    public function getCityObj()
    {
        if (is_null($this->_city) && !is_null($this->city_id)) {
            $this->_city = City::model()->findByPk($this->city_id);
        }
        return $this->_city;
    }

    /**
     * Возвращает название города или пустую строку.
     * @return type
     */
    public function getCity()
    {
        $city = $this->getCityObj();
        if ($city)
            return $city->name;
        else
            return '';
    }

    /**
     * Выдает город, область и страну пользователя в виде
     * Город (Область, Страна) или пустую строку.
     *
     * @return string
     */
    public function getCityFull()
    {
        $city = $this->getCityObj();
        if (!is_null($city))
            return $city->name . ' (' . $city->region->name . ', ' . $city->country->name . ')';
        else
            return '';
    }

    /**
     * Возвращает объект пользователя, являющегося referrer'ом для текущего
     * Если referrer не установлен, то возвращается новый объект класса User
     *
     * @return User
     */
    public function getReferrer()
    {
        if (is_null($this->_referrer) && !empty($this->referrer_id)) {
            $referrer = self::model()->findByPk($this->referrer_id);
            if ($referrer)
                $this->_referrer = $referrer;
        }
        return $this->_referrer;
    }

    public function setReferrer($value)
    {
        if (is_null($this->_referrer)) {
            $this->_referrer = self::model()->findByAttributes(array('login' => $value));
        }
        return $this->_referrer;
    }

    /**
     * Сохранение предыдущего значения статуса пользователя
     * перед установкой нового
     * @param integer $value
     * @return integer
     */
    public function setStatus($value)
    {
        $this->_oldStatus = $this->status;
        return parent::__set('status', $value);
    }

    /**
     * Установка бана на пользователя
     * @param int $time - целое значение (часы), указывающее срок бана
     * @return boolean
     */
    public function setBan($time)
    {
        if ($this->getIsNewRecord())
            return false;

        $data = $this->data;

        $data->ban_end_time = time() + ($time * 60 * 60);

        if ($data->save(false)) {
            return true;
        }
        return false;
    }

    /**
     * Возвращает список услуг пользователя без позиции в каталоге
     * @return array
     */
    public function getServiceListLite()
    {
        $command = Yii::app()->db->createCommand();

        $command->select('us.service_id, s.name as service_name, s.type as service_type, s.user_quantity as uq, us.experience as experience, s.url as url');
        $command->from('user_service us');
        $command->join('service s', 's.id = us.service_id');
        $command->where('us.user_id=:uid AND s.parent_id<>0', array(':uid' => $this->id));
        $command->limit(200);
        return $command->queryAll();
    }

    /**
     * Последовательный массив id услуг, оказываемых пользователем
     * @return array
     */
    public function getSimpleServiceList()
    {
        $sql = 'SELECT service_id FROM user_service WHERE user_id=:uid';
        $uid = intval($this->id);
        /** @var $command CDbCommand */
        $command = Yii::app()->db->createCommand($sql)->bindParam(':uid', $uid);
        return $command->queryColumn();
    }

    /**
     * Возвращает список услуг пользователя с позицией в каталоге
     * @return array
     */
    public function getServiceList()
    {
        $sql = 'SELECT us.service_id, s.url as service_url, s.name as service_name, s.type as service_type, '
            . 's.user_quantity as uq, us.experience as experience, usd.rating as rating, usd.project_qt as project_qt, IF(ISNULL(tmp.rating_pos),1,tmp.rating_pos+1) as rating_pos '
            . 'FROM user_service as us '
            . 'JOIN service as s ON s.id = us.service_id '
            . 'JOIN user_service_data as usd ON usd.user_id=us.user_id AND usd.service_id=us.service_id '
            . 'LEFT JOIN ( '
            . 'select count(DISTINCT usd2.rating) as rating_pos, us2.service_id '
            . 'FROM user_service as us2 '
            . 'INNER JOIN user_service_data as usd2 ON usd2.user_id = us2.user_id AND usd2.service_id=us2.service_id '
            . 'INNER JOIN ( '
            . 'select rating, service_id from user_service_data WHERE user_id=:uid '
            . ') as tmp1 ON tmp1.service_id=us2.service_id '
            . 'WHERE usd2.rating > tmp1.rating '
            . 'GROUP BY us2.service_id '
            . ') as tmp ON tmp.service_id=us.service_id '
            . 'WHERE us.user_id=:uid AND s.parent_id<>0 '
            . 'LIMIT 200';

        $uid = $this->id;
        /** @var $command CDbCommand */
        $command = Yii::app()->db->createCommand($sql)->bindParam(':uid', $uid);
        return $command->queryAll();
    }

    public function getUsedServiceList()
    {
        $command = Yii::app()->db->createCommand();

        $command->select('us.service_id, s.name as service_name, s.type as service_type, s.user_quantity as uq, us.experience as experience, usd.rating as rating, usd.project_qt as project_qt');
        $command->from('user_service us');
        $command->join('service s', 's.id = us.service_id');
        $command->join('user_service_data as usd', 'usd.user_id=us.user_id AND usd.service_id=us.service_id');
        $command->where('us.user_id=:uid AND s.parent_id<>0 AND usd.project_qt<>0', array(':uid' => $this->id));
        $command->limit(200);
        return $command->queryAll();
    }

    public function getServiceListWithParent()
    {
        $sql = 'SELECT s.id, s.url as service_url, us.segment, us.segment_supp, us.service_id, s.`name` AS service_name, '
            . 's.type as service_type, s.user_quantity as uq, s.parent_id as parent_id, us.experience AS experience, '
            . 'usd.rating AS rating, usd.project_qt AS project_qt, IF(ISNULL(tmp.rating_pos),1,tmp.rating_pos+1) as rating_pos '
            . 'FROM service as s '
            . 'LEFT JOIN `user_service` `us` ON us.service_id = s.id AND us.user_id=:uid '
            . 'LEFT JOIN `user_service_data` `usd` ON usd.service_id=s.id AND usd.user_id=:uid '
            . 'LEFT JOIN ( '
            . 'select count(DISTINCT usd2.rating) as rating_pos, us2.service_id '
            . 'FROM user_service as us2 '
            . 'INNER JOIN user_service_data as usd2 ON usd2.user_id = us2.user_id AND usd2.service_id=us2.service_id '
            . 'INNER JOIN ( '
            . 'select rating, service_id from user_service_data WHERE user_id=:uid '
            . ') as tmp1 ON tmp1.service_id=us2.service_id '
            . 'WHERE usd2.rating > tmp1.rating '
            . 'GROUP BY us2.service_id '
            . ') as tmp ON tmp.service_id=us.service_id '
            . 'WHERE us.user_id=:uid '
            . 'ORDER BY id '
            . 'LIMIT 200';
        $uid = $this->id;
        $command = Yii::app()->db->createCommand($sql)->bindParam(':uid', $uid);
        return $command->queryAll();
    }

    public function getProfileViews()
    {
        return (int)Yii::app()->redis->get('profile_view_cnt::' . $this->id);
    }

    /**
     * Расчет рейтинга активности пользователя
     * Вызывается после сохранения User и Comment
     * @return bool
     */
    public function countActivityRating()
    {
        /** пересчет рейтинга услуг */
        Yii::app()->gearman->appendJob('userService', array('userId' => $this->id));

        return true;
    }


    /**
     * Возвращает количество специалистов
     *
     * @param null $city_id идентификатор города. При выставлении значения,
     *            возвращает кол-во спецов из указанного города.
     * @param bool $round флаг при выставлении которого значение округляется
     * @param bool $format Флаг при выставлении которого значение форматируется.
     *
     * @return mixed
     */
    public static function getSpecialistsQuantity($city_id = null, $round = false, $format = false)
    {
        $key = 'User::getSpecialistsQuantity' . ($city_id) ? ('_' . $city_id) : '';
        $value = Yii::app()->cache->get($key);
        if (!$value) {
            $command = Yii::app()->db->createCommand();
            $command->select('count(DISTINCT user.id)');
            $command->from('user_service');
            $command->join('user', 'user_service.user_id=user.id');

            if ($city_id) {
                $command->join('user_servicecity usc', 'user_service.user_id=usc.user_id');
                $command->where('user.status=:status AND usc.city_id=:cid',
                    array(':status' => User::STATUS_ACTIVE, ':cid' => (int)$city_id));
            } else {
                $command->where('user.status=:status',
                    array(':status' => User::STATUS_ACTIVE));
            }

            $value = $command->queryScalar();
            Yii::app()->cache->set($key, $value, Cache::DURATION_REAL_TIME);
        }

        if ($round == true) {
            $value = intval($value / 1000) * 1000;
        }

        if ($format == true) {
            $value = number_format($value, 0, '.', ' ');
        }

        return $value;
    }

    /**
     * Получение связующей записи из user_service
     * @param type $serviceId
     * @return type
     */
    public function getUserService($serviceId)
    {
        return ServiceUser::model()->findByPk(array('user_id' => $this->id, 'service_id' => intval($serviceId)));
    }


    /**
     * Возвращает для текущего неавторизованного пользователя уникальный идентификатор,
     * который хранится у него в cookie
     * @static
     *
     */
    public static function getCookieId()
    {
        if (!Yii::app()->user->getIsGuest())
            throw new CHttpException(500, 'Only for guest user');


        if (!isset(Yii::app()->request->cookies['favoriteId']))
            User::setCookieId();

        $favoriteId = Yii::app()->request->cookies['favoriteId']->value;

        return (int)$favoriteId;
    }

    /**
     * Устанавливает для текущего неавторизованного пользователя уникальный
     * идентификатор и записывает его в cookie
     *
     * @static
     *
     */
    public static function setCookieId()
    {
        if (!Yii::app()->user->getIsGuest())
            throw new CHttpException(500, 'Only for guest user');


        // Генерим случайный идентификатор для списков "Избранное"
        $favoriteId = time() . rand(1000, 9999);

        // Удаляем на всякий случай саторе значение
        unset(Yii::app()->request->cookies['favoriteId']);

        // Ставим новое значение
        $params = Yii::app()->session->getCookieParams();
        $cookie = new CHttpCookie('favoriteId', $favoriteId);
        $cookie->domain = isset($params['domain']) ? $params['domain'] : '';
        Yii::app()->request->cookies['favoriteId'] = $cookie;
    }

    /**
     * Возвращает телефон, если разрешен вывод
     * иначе - пустую строку
     */
    public function getUserPhone()
    {
        if (!$this->data->hide_phone)
            return $this->phone;
        return '';
    }

    /**
     * Метод вызывается перед сохранением, указывает значение телефона для поиска.
     * Из телефона удаляются все символы кроме цифр.
     */
    public function phoneSearch()
    {
        $this->phone_search = preg_replace('#[^\d]#', '', $this->phone);
    }

    /**
     * Проверка привязанных соц. сетей
     * (учитываются fb, vk, odkl)
     */
    public function hasSocial()
    {
        // Отключена проверка соц сетей для рекомендаций
        return true;

        if (is_null($this->_hasSocial)) {
            $this->_hasSocial = (bool)Oauth::model()->countByAttributes(array('user_id' => $this->id), 'type_id IN (:t1, :t2, :t3)', array(':t1' => Oauth::VKONTAKTE, ':t2' => Oauth::FACEBOOK, ':t3' => Oauth::ODKL));
        }
        return $this->_hasSocial;
    }

    /**
     * Возвращает несколько случайных экспертов
     * @param int $qnt Количество возвращаемых специалистов.
     */
    static public function getRandomExpert($qnt = 5)
    {
        $user_ids = Yii::app()->db->createCommand()
            ->select('id')
            ->from(User::model()->tableName())
            ->where(
                '(expert_type = :et1 OR expert_type = :et2) AND status = :st AND (role = :r1 OR role = :r2)',
                array(
                    ':et1' => User::EXPERT,
                    ':et2' => User::EXPERT_TOP,
                    ':st' => User::STATUS_ACTIVE,
                    ':r1' => User::ROLE_SPEC_FIS,
                    ':r2' => User::ROLE_SPEC_JUR
                )
            )
            ->queryColumn();

        if ($user_ids) {
            if (count($user_ids) > $qnt)
                $keys = array_rand($user_ids, $qnt);
            else
                $keys = range(0, count($user_ids) - 1);
        } else {
            $keys = array();
        }


        $users = array();
        foreach ($keys as $key) {
            $model = User::model()->findByPk($user_ids[$key]);
            if ($model) {
                $users[] = $model;
            }
        }

        return $users;
    }

    /**
     * Проверка на принадлежность к "Редакции MyHome"
     */
    public function getIsWriter()
    {
        return in_array($this->role, array(self::ROLE_POWERADMIN, self::ROLE_ADMIN, self::ROLE_SENIORMODERATOR, self::ROLE_MODERATOR, self::ROLE_JUNIORMODERATOR));
    }


    /**
     * Возвращает ключ для Redis'а, в котором хранится количество оставленных
     * пользователю $user_id отзывов, которые он еще не прочитал.
     *
     * @param $user_id Идентификатор пользователя, которому наращиваем счетчик
     *
     * @return string Строка с ключем.
     */
    static public function getRedisKeyUnreadReview($user_id)
    {
        return 'USER:UNREAD_COUNT_REVIEW:' . intval($user_id);
    }


    /**
     * Генерирует логин для пользователя на основе указанного Email адреса.
     *
     * @param $email Электронный ящик пользователя
     */
    static public function generateLogin($email)
    {
        if (($atPos = strpos($email, '@')) === false) {
            throw new CHttpException(500, 'В переданном Email нет символа «@»');
        }

        /*
         * Берем в качевстве логина часть email'а до симовла «@».
         * Если такого еще нет, то возвращаем его. Если есть повторение,
         * то дописываем по очереди целые числа с конца и проверяем на
         * существование. Выходим из цикла если нашли свободный логин или
         * если сделали 15 попыток.
         */
        $preLogin = mb_substr($email, 0, $atPos, 'UTF-8');
        $preLogin = preg_replace('/[^a-zA-Z0-9_]/', '', $preLogin);

        $login = $preLogin;
        $foundLogin = false;
        $suffix = 1;
        do {
            if (User::model()->exists('login = :l', array(':l' => $login))) {
                $login = $preLogin . $suffix;
            } else {
                $foundLogin = true;
                break;
            }

            if (++$suffix > 15) {
                break;
            }

        } while (1);

        if (!$foundLogin) {
            throw new CHttpException(500, 'Во время регистрации произошла ошибка');
        }

        return $login;
    }

    public function getLastProjects($serviceId = null, $limit = 5)
    {
        $st1 = Interior::STATUS_MODERATING;
        $st2 = Interior::STATUS_ACCEPTED;
        $st3 = Interior::STATUS_REJECTED;
        $st4 = Interior::STATUS_CHANGED;
        $st5 = Interior::STATUS_VERIFIED;
        $st6 = Architecture::STATUS_TEMP_IMPORT;
        if ($serviceId === null) {
            $result = Yii::app()->db->createCommand("
                        (SELECT id, service_id, create_time, 'interior' as tname
                                FROM interior
                                WHERE author_id={$this->id} AND (status = {$st1} OR status = {$st2} OR status = {$st3} OR status = {$st4} OR status = {$st5})
                                ORDER BY create_time DESC LIMIT {$limit})

                        UNION
                        (SELECT id, service_id, create_time, 'portfolio' as tname
                                FROM portfolio
                                WHERE author_id={$this->id} AND (status = {$st1} OR status = {$st2} OR status = {$st3} OR status = {$st4} OR status = {$st5})
                                ORDER BY create_time DESC LIMIT {$limit})

                        UNION
                	   (SELECT id, service_id, create_time, 'architecture' as tname
                                FROM architecture
                                WHERE author_id={$this->id} AND (status = {$st1} OR status = {$st2} OR status = {$st3} OR status = {$st4} OR status = {$st5} OR status = {$st6})
                                ORDER BY create_time DESC LIMIT {$limit})

                        UNION
                	   (SELECT id, service_id, create_time, 'interiorpublic' as tname
                                FROM interiorpublic
                                WHERE author_id={$this->id} AND (status = {$st1} OR status = {$st2} OR status = {$st3} OR status = {$st4} OR status = {$st5})
                                ORDER BY create_time DESC LIMIT {$limit})
                        ORDER BY create_time DESC LIMIT {$limit};
                ")->queryAll();

            $projects = array();
            foreach ($result as $item) {
                $tmp = null;
                if ($item['service_id'] == Interior::SERVICE_ID) {
                    if ($item['tname'] == 'interior')
                        $tmp = Interior::model()->findByPk($item['id']);
                    elseif ($item['tname'] == 'interiorpublic')
                        $tmp = Interiorpublic::model()->findByPk($item['id']);
                } elseif ($item['service_id'] == Architecture::SERVICE_ID) {
                    $tmp = Architecture::model()->findByPk($item['id']);
                } else {
                    $tmp = Portfolio::model()->findByPk($item['id']);
                }

                if ($tmp) {
                    $projects[] = $tmp;
                }
            }
            return $projects;
        }

        if ($serviceId == Interior::SERVICE_ID) {
            $result = Yii::app()->db->createCommand("
                        (SELECT i.id, i.service_id, i.create_time, 'interior' as tname, ps.position
                                FROM interior as i
                                INNER JOIN portfolio_sort as ps ON ps.item_id=i.id AND ps.idea_type_id=" . Config::INTERIOR . "
                                WHERE i.author_id={$this->id} AND (i.status = {$st1} OR i.status = {$st2} OR i.status = {$st3} OR i.status = {$st4} OR i.status = {$st5})
                                ORDER BY ps.position ASC LIMIT {$limit})

                        UNION
                        (SELECT ip.id, ip.service_id, ip.create_time, 'interiorpublic' as tname, ps.position
                                FROM interiorpublic as ip
                                INNER JOIN portfolio_sort as ps ON ps.item_id=ip.id AND ps.idea_type_id=" . Config::INTERIOR_PUBLIC . "
                                WHERE ip.author_id={$this->id} AND (ip.status = {$st1} OR ip.status = {$st2} OR ip.status = {$st3} OR ip.status = {$st4} OR ip.status = {$st5})
                                ORDER BY ps.position ASC LIMIT {$limit})
                        ORDER BY position ASC
                        LIMIT {$limit}
               		")->queryAll();

            $projects = array();
            foreach ($result as $item) {
                $tmp = null;
                if ($item['tname'] == 'interior')
                    $tmp = Interior::model()->findByPk($item['id']);
                elseif ($item['tname'] == 'interiorpublic')
                    $tmp = Interiorpublic::model()->findByPk($item['id']);
                if ($tmp) {
                    $projects[] = $tmp;
                }
            }
            return $projects;
        }

        if ($serviceId == Architecture::SERVICE_ID) {
            $criteria = new CDbCriteria(array(
                'condition' => 'status IN (:st1, :st2, :st3, :st4, :st5, :st6) AND author_id=:uid',
                'join' => 'INNER JOIN portfolio_sort as ps ON ps.item_id=t.id AND ps.idea_type_id=' . Config::ARCHITECTURE,
                'order' => 'ps.position ASC',
                'limit' => $limit,
                'params' => array(':st1' => $st1, ':st2' => $st2, ':st3' => $st3, ':st4' => $st4, ':st5' => $st5, ':st6' => $st6, ':uid' => $this->id),
            ));
            return Architecture::model()->findAll($criteria);
        }

        $criteria = new CDbCriteria(array(
            'condition' => 'status IN (:st1, :st2, :st3, :st4, :st5, :st6) AND author_id=:uid AND t.service_id=:sid',
            'join' => 'INNER JOIN portfolio_sort as ps ON ps.item_id=t.id AND ps.idea_type_id=' . Config::PORTFOLIO,
            'order' => 'ps.position ASC',
            'limit' => $limit,
            'params' => array(':st1' => $st1, ':st2' => $st2, ':st3' => $st3, ':st4' => $st4, ':st5' => $st5, ':st6' => $st6, ':uid' => $this->id, ':sid' => $serviceId),
        ));
        return Portfolio::model()->findAll($criteria);
    }


    /**
     * Возвращает из sphinx'а Общий рейтинг специалиста.
     *
     * @return int
     */
    public function getTotalRating()
    {
        $sql = 'SELECT total_rating FROM {{user_service}} WHERE user_id = ' . $this->id . ' LIMIT 1';

        $res = Yii::app()->sphinx->createCommand($sql)->queryScalar();

        if ($res) {
            $rating = $res;
        } else {
            $rating = 0;
        }

        return $rating;
    }


    /**
     * Устанавливает родной город спеца в список городов, в которых спец
     * оказывает услуги. Вызывается событием afterSave модели пользователя
     */
    protected function setSpecCity()
    {
        /*
         * Метод актуален только для специалистов
         */
        if (!in_array($this->role, array(self::ROLE_SPEC_FIS, self::ROLE_SPEC_JUR)))
            return false;

        if (!$this->city_id)
            return false;

        /*
         * Проверка наличия пользовательского города в списке городов оказания услуг
         */
        $cityExist = UserServicecity::model()->exists('user_id=:uid and city_id=:cid', array(
            ':uid' => $this->id,
            ':cid' => $this->city_id,
        ));

        if ($cityExist)
            return true;

        if (!$this->city)
            return false;

        /*
         * Запись города пользователя в список городов оказания услуг
         */
        $serviceCity = new UserServicecity();
        $serviceCity->user_id = $this->id;
        $serviceCity->city_id = $this->city->id;
        $serviceCity->region_id = $this->city->region->id;
        $serviceCity->country_id = $this->city->country->id;
        return $serviceCity->save();
    }

    public function getIsPremium()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $this->id);
        $criteria->compare('date_end', '>' . time());
        $criteria->compare('status', UserServicePriority::STATUS_PAY_SUCCESS);
        return UserServicePriority::model()->exists($criteria);
    }
}