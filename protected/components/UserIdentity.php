<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{

    private $_id;
    public $ban_time = null; // Время окончания бана
    public $tmpPass;
    public $clientIp;
    public $record;

    const NOTICE_TMPPASS_REQUIRED = 3;
    const ERROR_TMPPASS = 4;
    const ERROR_TMPBANNED = 5;
    const ERROR_BANNED = 6;
    const ERROR_NOT_ACTIVE = 7;

    /**
     * Constructor.
     * @param string $username username
     * @param string $password password
     */
    public function __construct($username, $password, $clientIp = null, $tmpPass = null)
    {
        $this->tmpPass = $tmpPass;
        $this->clientIp = $clientIp;
        parent::__construct($username, $password);
    }

    public function authenticate($useMD5 = false)
    {
        $this->record = User::model()->findByAttributes(array(), 'login=:login OR email=:email', array(':login' => $this->username, ':email' => $this->username));

        if ($this->record === null)
            $this->errorCode = self::ERROR_USERNAME_INVALID;

        else if ($this->record->password !== md5($this->password) && !($useMD5 && $this->record->password === $this->password))
            $this->errorCode = self::ERROR_PASSWORD_INVALID;

        else if ($this->checkBan() != self::ERROR_NONE)
            return $this->errorCode;

        else if ($this->record->status != User::STATUS_ACTIVE)
            $this->errorCode = self::ERROR_NOT_ACTIVE;

        else if ($this->checkIp() != self::ERROR_NONE)
            return $this->errorCode;

        else {
            $this->_id = $this->record->id;
            $this->errorCode = self::ERROR_NONE;
        }

        return $this->errorCode;
    }

    /**
     * Проверка IP и временного бана для пользователя
     * @return type
     */
    private function checkIp()
    {
        if (is_null($this->clientIp))
            $this->clientIp = Yii::app()->request->getUserHostAddress();

        // роль авторизующегося входит в группу администраторов и вход совершается не с доверенного IP
        if (!empty(Config::$rolesAdmin[$this->record->role]) && !in_array($this->clientIp, Config::$adminIps)) {

            $auth_attemps = Yii::app()->cache->get('auth_request_' . $this->clientIp . '_' . $this->record->login);

            // первая попытка входа с "чужого" IP, инициализация базовых значений
            if (!$auth_attemps) {
                $configuration = array('tmp_pass' => Amputate::generatePassword(6), 'start_time' => time(), 'count' => '0');
                Yii::app()->cache->set('auth_request_' . $this->clientIp . '_' . $this->record->login, $configuration);

                Yii::app()->mail->create('tmpPass')
                    ->to($this->record->email)
                    ->priority(EmailComponent::PRT_HIGHT)
                    ->params(array(
                        'tmpPass' => $configuration['tmp_pass']
                    ))
                    ->send();

                $this->errorCode = self::NOTICE_TMPPASS_REQUIRED;
                return $this->errorCode;
            }

            // наращивание кол-ва попыток входа с данного IP под данным login
            $auth_attemps['count'] += 1;
            Yii::app()->cache->set('auth_request_' . $this->clientIp . '_' . $this->record->login, $auth_attemps);

            // кол-во попыток входа превысило максимально допустимое
            if ($auth_attemps['count'] >= Config::MAX_TRY_AUTH_BEFORE_BAN) {
                IpBan::createBan($this->clientIp, Config::BAN_TIME_BEFORE_NEXT_LOGIN_ATTEMP);
                Yii::app()->cache->delete('auth_request_' . $this->clientIp . '_' . $this->record->login);
                $this->errorCode = self::ERROR_TMPBANNED;
                return $this->errorCode;
            }

            // проверка временного пароля
            if ($this->tmpPass && $this->tmpPass === $auth_attemps['tmp_pass']) {
                Yii::app()->cache->delete('auth_request_' . $this->clientIp . '_' . $this->record->login);
                return self::ERROR_NONE;
            } else {
                $this->errorCode = self::ERROR_TMPPASS;
                return $this->errorCode;
            }
        }
        return self::ERROR_NONE;
    }

    /**
     * Проверка бана пользователя
     * @return integer
     */
    private function checkBan()
    {
        if ($this->record->status == User::STATUS_BANNED) {
            $userData = $this->record->data;
            $banEndTime = $userData->ban_end_time;

            if (is_null($banEndTime) || $banEndTime < time()) {
                $userData->ban_end_time = null;
                $userData->save(false);
                $this->record->status = User::STATUS_ACTIVE;
                $this->record->save(false);
            } else {
                $this->ban_time = $banEndTime;
                $this->errorCode = self::ERROR_BANNED;
                return self::ERROR_BANNED;
            }

        }
        return self::ERROR_NONE;
    }

    public function getId()
    {
        return $this->_id;
    }

}
