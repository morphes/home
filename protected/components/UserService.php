<?php

/**
 * @brief Атоподгружаемый класс, обеспечивающий пользовательские сервисы
 * @author Kuzakov Roman <roman.kuzakov@gmail.com>
 */
class UserService extends CComponent
{
    public $adminList = array(User::ROLE_ADMIN, User::ROLE_POWERADMIN, User::ROLE_JUNIORMODERATOR, User::ROLE_MODERATOR, User::ROLE_FREELANCEEDITOR, User::ROLE_SALEMANAGER, User::ROLE_SEO, User::ROLE_SENIORMODERATOR);

    public $enable = true;

    public function init()
    {
        if (!$this->enable)
            return false;

        $this->setLocale();
        $this->adminAccess();
        self::checkAuth();
        $this->checkIp();
        $this->checkGeoIp();
        $this->checkAccess();
        $this->getUnreadMsg();
        $this->changeRole();
    }

    /**
     * Проверка роли юзера и доп. проверка по ip и userAgent для админов
     */
    private function adminAccess()
    {
        $webUser = Yii::app()->getUser();
        $role = $webUser->getRole();
        if (empty(Yii::app()->params->disableCheck) && in_array($role, $this->adminList)) {
            $userData = $webUser->getState('userData');
            $ip = ip2long(Yii::app()->getRequest()->getUserHostAddress());
            $userAgent = md5(Yii::app()->request->getUserAgent());
            if (!isset($userData['ip']) || !isset($userData['userAgent']) || $userData['ip'] != $ip || $userData['userAgent'] != $userAgent) {
                Yii::app()->user->logout();
                Yii::app()->session->open();
            }
        }
    }

    /**
     * @brief Проверяет статус текущего пользователя и делает logout в случае его бана или удаления
     */
    private function checkAccess()
    {
        $user = Yii::app()->user->model;
        if (isset($user->status) && $user->status != User::STATUS_ACTIVE) {
            Yii::app()->user->logout();
            Yii::app()->session->open();
            Yii::app()->user->setFlash('login', 'Аккаунт не активен');
        }
    }

    /**
     * @brief Проверяет количество непрочитанных пользователем ЛС
     */
    private function getUnreadMsg()
    {
        if (Yii::app()->getUser()->getIsGuest())
            return;

        $count = MsgBody::model()->count('recipient_id = :user AND recipient_status = :status', array(
                ':user' => Yii::app()->user->id,
                ':status' => MsgBody::STATUS_UNREAD
            )
        );

        if ($count > 0)
            Yii::app()->user->setFlash('msg_count', $count);

        return $count;
    }

    /**
     * @brief Устанавливает локаль пользователя
     */
    private function setLocale()
    {
        setlocale(LC_TIME, 'ru_RU.utf8');
    }

    /**
     * @brief Проверяет доступность Ip для авторизации
     */
    private function checkIp()
    {
        $ip = ip2long(Yii::app()->request->userHostAddress);
        $data = IpBan::model()->findByPk($ip);
        if (is_null($data) || $data->expire < time())
            return true;

        Yii::app()->user->logout();
        throw new CHttpException(403, 'Ваш IP адрес забанен');
    }

    /**
     * Установка значения города пользователя по его ip
     * @return bool
     */
    private function checkGeoIp()
    {
        $webUser = Yii::app()->getUser();

        if ($webUser->isBot())
            return true;

        if ($webUser->getCookieVariable(Geoip::COOKIE_GEO_DETECTED) === null) {
            $detectedCity = Geoip::getCity();
            $webUser->setCookieVariable(Geoip::COOKIE_GEO_DETECTED, $detectedCity->id, 0);
            $webUser->setDetectedCity($detectedCity);
        }

        if (!$webUser->getCookieVariable('city_deleted') && $webUser->getDetectedCity() && !$webUser->getSelectedCity()) {
            $webUser->setCookieVariable(Geoip::COOKIE_GEO_SELECTED, $webUser->getDetectedCity()->id, 0);
            $webUser->setSelectedCity($webUser->getDetectedCity());
        }

        return true;
    }

    /**
     * Поддерживает состояние куки __uid, которое используется nginx
     * Если __uid == 0, то nginx отдает кешированную страницу, считая, что текущий клиент - гость
     * Если __uid != 0, то nginx обрабатывает запрос, как от юзера (передавая выполнение fpm)
     * @return bool
     */
    public static function checkAuth()
    {
        /**
         * @var WebUser $webUser
         */
        $webUser = Yii::app()->getUser();
        if ($webUser->isBot()) {
            return true;
        }

        if (Yii::app()->user->isGuest) {
            $webUser->setCookieVariable(User::cookieUID, 0, 0);
        } else {
            $webUser->setCookieVariable(User::cookieUID, $webUser->id, 0);
        }
    }

    /**
     * Подмена роли для POWER_ADMIN
     */
    private function changeRole()
    {
        $webUser = Yii::app()->getUser();
        $role = $webUser->getRole();
        if ($role == User::ROLE_POWERADMIN) {
            $chRole = function () {
                if (isset($_COOKIE['role'])) {
                    $role = intval($_COOKIE['role']);
                } else {
                    $role = intval(Yii::app()->session->get('REAL_ROLE', null));
                }
                if (!empty(User::$roleNames[$role])) {
                    Yii::app()->getUser()->setRole($role);
                    Yii::app()->getUser()->flushAccessCache();

                    $user = Cache::getInstance()->user;
                    if ($user instanceof User && $user->role == User::ROLE_POWERADMIN) {
                        $user->role = $role;
                        Cache::getInstance()->user = $user;
                    }
                    Yii::app()->session->add('REAL_ROLE', User::ROLE_POWERADMIN);
                }
            };

            Yii::app()->attachEventHandler('onBeforeController', $chRole);
        }
    }


}

