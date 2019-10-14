<?php

class WebUser extends CWebUser
{
    private $_model = null;
    private $_fileApiSupport = null;
    private $_access = array();
    private $_selectedCity = null;
    private $_detectedCity = null;
    private $_flashData = null;

    const FLASH_KEY_PREFIX = 'user:flash:';

    public function getRole()
    {
        if ($user = $this->getModel()) {
            return $user->role;
        }
    }

    public function setRole($role)
    {
        $this->getModel();
        if ($this->_model)
            $this->_model->role = $role;
    }

    /**
     * @return User
     */
    public function getModel()
    {
        if (!$this->isGuest && $this->_model === null) {
            $this->_model = User::model()->findByPk((int)$this->id);
        }
        return $this->_model;
    }

    /**
     * Проверка поддержки fileApi у клиента
     * @return boolean
     */
    public function getFileApiSupport()
    {
        if (is_null($this->_fileApiSupport)) {
            if (isset(Yii::app()->request->cookies['fileApiSupport']))
                $this->_fileApiSupport = Yii::app()->request->cookies['fileApiSupport']->value == 'true' ? true : false;
            else
                $this->_fileApiSupport = false;
        }
        return $this->_fileApiSupport;
    }


    public function getSelectedCity()
    {
        if (!$this->_selectedCity)
            $this->_selectedCity = City::model()->findByPk($this->getCookieVariable(Geoip::COOKIE_GEO_SELECTED));

        return $this->_selectedCity;
    }

    public function getDetectedCity()
    {
        if (!$this->_detectedCity) {
            $detectedCity = City::model()->findByPk($this->getCookieVariable(Geoip::COOKIE_GEO_DETECTED));
            if (!$detectedCity || !($detectedCity instanceof City))
                $detectedCity = City::model()->findByPk(City::ID_MOSCOW);

            $this->_detectedCity = $detectedCity;
        }


        return $this->_detectedCity;
    }

    public function setSelectedCity($city)
    {
        if ($city instanceof City)
            $this->_selectedCity = $city;
    }

    public function setDetectedCity($city)
    {
        if ($city instanceof City)
            $this->_detectedCity = $city;
    }


    /**
     * Метод модифицирован таким образом, что проверку доступа осуществляет по роли,
     * которая хранится в пользовательском свойстве user->role
     * Проверку можно осуществлять по нескольким ролям сразу. Для этого нужно первым
     * параметром передать массив ролей. Доступ считается разрешенным, если текущий
     * пользователь имеет хотябы одну роль, указанную в $operations
     *
     * @param string $operations Роль или массив ролей, которыми дложен обладать пользователь,
     *    чтобы получить доступ.
     * @param array $params
     * @param bool $allowCaching
     * @return bool
     */
    public function checkAccess($operations, $params = array(), $allowCaching = true)
    {
        if (!is_array($operations))
            $operations = array($operations);

        $valid = false;
        foreach ($operations as $operation) {
            if ($allowCaching && $params === array() && isset($this->_access[$operation]))
                $valid = $valid || $this->_access[$operation];
            else
                $valid = $valid || ($this->_access[$operation] = ($this->role == $operation));
        }

        return $valid;
    }

    /**
     * Сброс кэша доступа (для подмены ролей)
     */
    public function flushAccessCache()
    {
        $this->_access = array();
    }

    /**
     * Получение данных юзера
     * @param $key
     * @param null $defaultValue
     * @param bool $delete
     * @return null
     */
    public function getDbFlash($key, $defaultValue = null, $delete = false)
    {
        $data = $this->getFlashData();
        $result = isset($data[$key]) ? $data[$key] : $defaultValue;
        if ($delete) {
            $this->setDbFlash($key, null);
        }
        return $result;
    }

    /**
     * Проверка существования данных юзера
     * @param $key
     * @return bool
     */
    public function hasDbFlash($key)
    {
        return $this->getDbFlash($key, null, false) !== null;
    }

    /**
     * Установка значений данных юзера
     * @param $key
     * @param $value
     * @param null $defaultValue
     */
    public function setDbFlash($key, $value, $defaultValue = null)
    {
        if ($this->getIsGuest())
            return;
        $data = $this->getFlashData();

        if ($value === $defaultValue)
            unset($data[$key]);
        else
            $data[$key] = $value;

        $this->_flashData = $data;
        $uid = $this->getId();
        Yii::app()->redis->set(self::FLASH_KEY_PREFIX . $uid, $data);
    }

    /**
     * Получение данных юзера из redis
     * @return array|null
     */
    private function getFlashData()
    {
        if ($this->_flashData !== null)
            return $this->_flashData;

        if ($this->getIsGuest())
            return array();

        $uid = $this->getId();
        $this->_flashData = Yii::app()->redis->get(self::FLASH_KEY_PREFIX . $uid);
        return is_array($this->_flashData) ? $this->_flashData : array();
    }

    /**
     * Сохраняет значение в cookie
     * @param string $varName - ключ
     * @param any $value - значение
     * @param integer|null $expire
     */
    public function setCookieVariable($varName, $value, $expire = null)
    {
        $cookie = new CHttpCookie($varName, $value);
        $cookie->expire = (int)$expire;
        $cookie->domain = Config::getCookieDomain();

        Yii::app()->request->cookies[$varName] = $cookie;
    }

    /**
     * Получает значение из cookie
     * @param $varName - ключ
     *
     * @return any
     */
    public function getCookieVariable($varName)
    {
        return Yii::app()->request->cookies->contains($varName) ?
            Yii::app()->request->cookies[$varName]->value : null;
    }


    /**
     * Определяет принадлежность пользователя к ботам
     * @return bool
     */
    public function isBot()
    {
        $bots = array(
            'rambler', 'googlebot', 'aport', 'yahoo', 'msnbot', 'turtle', 'mail<a href="http://webrelease.ru" style="text-decoration:none;border:none">.</a>ru', 'omsktele',
            'yetibot', 'picsearch', 'sape.bot', 'sape_context', 'gigabot', 'snapbot', 'alexa.com',
            'megadownload.net', 'askpeter.info', 'igde<a href="http://webrelease.ru" style="text-decoration:none;border:none">.</a>ru', 'ask.com', 'qwartabot', 'yanga.co.uk',
            'scoutjet', 'similarpages', 'oozbot', 'shrinktheweb.com', 'aboutusbot', 'followsite.com',
            'dataparksearch', 'google-sitemaps', 'appEngine-google', 'feedfetcher-google',
            'liveinternet<a href="http://webrelease.ru" style="text-decoration:none;border:none">.</a>ru', 'xml-sitemaps.com', 'agama', 'metadatalabs.com', 'h1.hrn.ru',
            'googlealert.com', 'seo-rus.com', 'yaDirectBot', 'yandeG', 'yandex',
            'yandexSomething', 'Copyscape.com', 'AdsBot-Google', 'domaintools.com',
            'Nigma<a href="http://webrelease.ru" style="text-decoration:none;border:none">.</a>ru', 'bing.com', 'dotnetdotcom'
        );

        $botIdentifier = $this->getCookieVariable('is_bot');

        if ($botIdentifier == null) {

            foreach ($bots as $bot) {

                if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false) {
                    $this->setCookieVariable('is_bot', '1', 0);
                    return true;
                }
            }

            $this->setCookieVariable('is_bot', '0', 0);
            return false;
        }

        if ($botIdentifier == 1)
            return true;
        else
            return false;
    }
}