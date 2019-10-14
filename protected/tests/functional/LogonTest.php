<?php
class LogonTest extends WebTestCase
{
    protected function setUp(){
        parent::setUp();
        $this->setElements();
    }   

    public function setElements()
    {
        return array_merge(parent::setElements(), array(
                'login_field'       =>  'id=p-login-name',
                'pass_field'        =>  'id=p-login-pass',
                'error_title'       =>  'css=p.error-title',
                'submit'            =>  'css=button.btn_grey'
            )
        );
    }

    /**
    *   User login data provider
    */
    public function loginDatas()
    {
        return array(
            array('', '', 'Необходимо заполнить поле Логин'),
            array('logonarium', '', 'Такого пользователя не существует или пароль введен неверно'),
            array('', '1', 'Необходимо заполнить поле Логин'),
            array('logonarium', '1', ''),
            array('zotov', '', 'Такого пользователя не существует или пароль введен неверно'),
            array('', '1', 'Необходимо заполнить поле Логин'),
            array('zotov', '1', '')
        );
    }

    /**
     *  @dataProvider loginDatas
     */
    public function testLogin($login, $password, $error_message)
    {
        // Стартуем
        $this->startAction('/');
        // Вызываем лайтбокс авторизации
        $this->click($this->getElement('authorize'));
        // Ждем появления лайтбокса
        $this->waitForElementPresent($this->getElement('popup_login'));
        // Проверяем, появился ли лайтбокс
        $this->verifyTextPresent('Авторизация');
        // Заполняем поля некорректными данными и каждый раз сабмитим форму
        $this->type($this->getElement('popup_login_name'), $login);
        $this->type($this->getElement('popup_login_pass'), $password);
        // После сабмита, отлавливаем alert и чекаем текст в нем
        $this->click($this->getElement('submit'));
        if($error_message) {
            // Ждем появления ошибки
            $this->waitForElementPresent($this->getElement('error_title'));
            // Проверяем текст ошибки
            $this->verifyTextPresent($error_message);
        } else {
            // Сабмитим форму
            $this->click($this->getElement('submit'));
            $this->waitForPageToLoad('2000');
            // Чекаем, что пользователь залогинен
            $this->verifyTextPresent('Мой профиль');
        }
    }
}
?>