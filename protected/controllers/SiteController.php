<?php

/**
 * @brief Главный контроллер сайта
 * @details Главная страница, регистрация/вход/выход/активация пользователя
 * @see FrontController
 * @author Kuzakov Roman <roman.kuzakov@gmail.com>
 * @author Sergey Seregin <sss@medvediza.ru>
 */
class SiteController extends FrontController
{

    public function filters()
    {
        return array('accessControl');
    }

    /**
     * @brief Разрешает доступ всем пользователям
     * @return array
     */
    public function accessRules()
    {
        return array(
            // Доступ на выход только авторизованым
            array(
                'allow',
                'actions' => array('logout'),
                'users' => array('@'),
            ),
            // Доступ на регистрацию и активацию только не авторизованым
            array(
                'allow',
                'actions' => array(
                    'registration',
                    'promo',
                    'registrationStepsForm',
                    'RegistrationOk',
                    'feedbackmessage',
                    'activation',
                    'login',
                    'loginTest',
                    'ajaxlogin',
                    'ajaxRegistration',
                    'restore',
                    'forgot',
                    'autocompletecity',
                    'geoContent',
                    'planner',
                    'bmAbout',
                    'AjaxTabPromo',
                    'StoreOffer',
                    'email',
                    'AjaxSaveServices'
                ),
                'users' => array('*'),
            ),
            // Доступ к индексу, капче и ошибкам всем пользователям
            array(
                'allow',
                'actions' => array('index', 'captcha', 'captchaWhite', 'error', 'callback'),
                'users' => array('*'),
            ),
            // Ограничение доступа к функциям контроллера
            array(
                'deny',
                'users' => array('*'),
            ),
        );
    }

    public function beforeAction($action)
    {
        $this->class_wrapper = '';

        return parent::beforeAction($action);
    }


public function actionCallback()
{

if(!isset($_POST['Callback']) || $_POST['Callback']['accept'] == 0){
  throw new CHttpException(404, "Страница не найдена");
}
$call = $_POST['Callback'];

$message = <<<EOD

<table>
<tr>
<td>Имя</td><td>{$call['name']}</td>
</tr>
<tr>
<td>Телефон</td><td>{$call['phone']}</td>
</tr>
<tr>
<td colspan="2">Согласен на обработку</td>
</tr>
</table>
EOD;

$to      = 'to@mail.ru';
$subject = 'Сообщение с сайта: Обратный звонок';
$headers = 'From: from@mail.ru' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
mail($to, $subject, $message, $headers);

$this->redirect('/');
Yii::app()->end();

}

    /**
     * @brief Declares class-based actions.
     */
    public function actions()
    {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xf3f3eb,
                'maxLength' => 5,
                'minLength' => 5,
                'testLimit' => 0,
                'padding' => 1,
                'offset' => 0,

            ),
            'captchaWhite' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xffffff,
                'maxLength' => 5,
                'minLength' => 5,
                'testLimit' => 0,
                'padding' => 1,
                'offset' => 0,

            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
        );
    }

    /**
     * @brief Главная страница сайта
     */
    public function actionIndex()
    {
//var_dump(get_class(Yii::app()));
//die();
        $this->bodyClass = 'index';

        // Определяем промоблок
        $this->additionalContent = $this->renderPartial('gridIndexPromo', array(), true);
        $this->render('gridIndex');
    }

    public function actionAjaxTabPromo($id)
    {
        Yii::import('application.modules.admin.models.IndexProductTab');

        $tab = IndexProductTab::model()->findByPk((int)$id);
        if (!$tab) {
            throw new CHttpException(404, 'Страница не найдена');
        }

        $html = $this->renderPartial(
            'gridIndexPromo',
            array('activeTabId' => $tab->id),
            true
        );

        exit(json_encode(array(
            'success' => true,
            'html' => $html
        )));
    }

    /**
     * @brief Кастомизованный вывод ошибок сайта
     */
    public function actionError()
    {
        $error = Yii::app()->errorHandler->error;

        if ($error) {
            if (empty($error['message'])) {
                if (array_key_exists($error['code'], Config::$errors))
                    $error['message'] = Config::$errors[$error['code']];
            }

            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else {
                $this->hide_div_content = true;
                $this->spec_div_class = 'e404';

                if ($error['code'] == 404) {
                    $error['title'] = "Страница не найдена";
                    $error['message'] = 'Возможно неправильно'
                        . ' набран адрес или такой страницы не существует.<br>'
                        . ' Попробуйте начать с <a href="/">главной страницы</a>.';
                } else {
                    $error['title'] = 'Ошибка ' . $error['code'];
                }

                $this->render('//site/error', $error);
            }
        }
    }


    /**
     * Страница регистрации пользователей на сайте
     */
    public function actionRegistration()
    {
        $this->layout = 'grid_main';
        $this->bodyClass = 'auth registration';

        if (!Yii::app()->user->isGuest) {
            $this->redirect(Yii::app()->homeUrl);
        }

        $user = new User();

        $this->render('//site/registration', array(
            'user' => $user
        ));
    }


    /**
     * Страница, на которую попадает пользователь после регистрации.
     */
    public function actionPromo()
    {
        $this->layout = 'grid_main';
        $this->bodyClass = 'auth promo';

        if (Yii::app()->user->isGuest) {
            $this->redirect(Yii::app()->homeUrl);
        }

        $this->render('//site/registrationPromo', array());
    }


    /**
     * Рендерит расширенную форму регистрации для попапа и для страницы.
     */
    public function actionRegistrationStepsForm()
    {
        $this->layout = false;

        $user = new User();

        $this->renderPartial('//site/registrationStepsForm', array(
            'user' => $user
        ));
    }


    /**
     * Метод регистрации пользователя как специалиста.
     *
     * @throws CHttpException
     */
    public function actionAjaxRegistration()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(403);
        }

        // Инициализация параметров для ответа по JSON
        $success = false;
        $errorFields = array();

        // Список доступных ролей для регистрации
        $available_roles = Config::$rolesUserReg;
        unset($available_roles[User::ROLE_STORES_ADMIN]);
        unset($available_roles[User::ROLE_STORES_MODERATOR]);
        unset($available_roles[User::ROLE_MALL_ADMIN]);


        if (Yii::app()->user->isGuest) {
            $user = new User();
        } else {
            $user = Yii::app()->user->model;
        }


        if (!empty($_POST['User'])) {
            // ПЕРВЫМ ДЕЛОМ ОПРЕДЕЛЯЕМ ДОСТУПНОСТЬ РОЛИ
            if (
                isset($_POST['User']['role'])
                && array_key_exists($_POST['User']['role'], $available_roles)
            ) {
                $userRole = $_POST['User']['role'];
            } else {
                $userRole = User::ROLE_USER;
            }

            if (isset($_POST['User']['email'])) {
                $_POST['User']['email'] = trim($_POST['User']['email']);
            }


            // Затем ставим сценарий...
            $user->setScenario('reg-' . $userRole);

            //... и только потом получаем все данные пользователя
            $user->setAttributes($_POST['User']);


            if ($user->validate()) {
                $unsafe_pass = $user->password;

                $user->password = md5($user->password);
                $user->login = User::generateLogin($user->email);
                $user->status = User::STATUS_ACTIVE;

                // Перед транзакцией, выставляем флаг, чтобы методы afterSave не выполнялись
                $user->afterSaveCommit = true;

                $user_transaction = $user->dbConnection->beginTransaction();

                if ($user->save()) {
                    // Сохраняем данные для пользователя
                    $user->data->user_id = $user->id;
                    $user->data->save();

                    $user_transaction->commit();

                    // После комита вызываем afterSave с флагом ручного запуска
                    $user->afterSave(true);

                    /* Отправляем письмо с удачной регистрацией.
                     * Отключаем лог, т.к. в письме будет не зашифрованный пароль.
                     */
                    Yii::app()->mail
                        ->create('userRegistrationDone')
                        ->to($user->email)
                        ->params(array(
                            'sign_B' => Yii::app()->mail->create('sign_B')->useView(false)->prepare()->getMessage(),
                        ))
                        ->disableLog()
                        ->priority(EmailComponent::PRT_HIGHT)
                        ->send();

                    $success = true;

                    $user->login(true);
                }

            }


            $errors = $user->getErrors();
            foreach ($errors as $name => $value) {
                $errorFields['User_' . $name] = $value[0];
            }
        }

        exit(json_encode(array(
            'success' => $success,
            'errorFields' => $errorFields,
        )));
    }

    /**
     * @brief Регистрация пользоватлеей
     */
    public function actionRegistration_old()
    {
        $this->hide_div_content = true;
        $this->spec_div_class = 'registration';

        $available_roles = Config::$rolesUserReg;
        unset($available_roles[User::ROLE_STORES_ADMIN]);
        unset($available_roles[User::ROLE_STORES_MODERATOR]);
        unset($available_roles[User::ROLE_MALL_ADMIN]);

        if (!Yii::app()->user->isGuest) {
            $this->redirect('/site/index');
        } else {
            $user = new User();

            if (!empty($_POST['User'])) {
                // ПЕРВЫМ ДЕЛОМ ОПРЕДЕЛЯЕМ ДОСТУПНОСТЬ РОЛИ
                if (isset($_POST['User']['role'])
                    && array_key_exists($_POST['User']['role'], $available_roles)
                )
                    $userRole = $_POST['User']['role'];
                else
                    $userRole = User::ROLE_USER;

                $_POST['User']['email'] = trim($_POST['User']['email']);

                // Затем ставим сценарий...
                $user->setScenario('reg-' . $userRole);

                //... и только потом получаем все данные пользователя
                $user->setAttributes($_POST['User']);


                if ($user->role == User::ROLE_SPEC_JUR)
                    $user->data->setScenario('reg-' . User::ROLE_SPEC_JUR);


                // Ajax валидация
                if (isset($_POST['ajax'])) {
                    $this->performAjaxValidation($user, $user->data);
                }

                // end ouath


                if (!empty($_POST['UserData']))
                    $user->data->attributes = $_POST['UserData'];

                $user_validate = ($user->validate()) ? true : false;
                $userdata_validate = ($user->data->validate()) ? true : false;

                if ($user_validate && $userdata_validate) {

                    $unsafe_pass = $user->password;
                    $user->password = md5($user->password);
                    $user->password2 = md5($user->password2);

                    $user->activateKey = User::generateActivateKey();
                    $user->status = User::STATUS_VERIFYING;

                    // Перед транзакцией, выставляем флаг, чтобы методы afterSaver не выполнялись
                    $user->afterSaveCommit = true;

                    $user_transaction = $user->dbConnection->beginTransaction();

                    if ($user->save()) {
                        // Сохраняем данные для пользователя
                        $user->data->user_id = $user->id;
                        $user->data->save();

                        // установка города предоставления услуг для специалистов
                        if (in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR))) {
                            $city = City::model()->findByPk($user->city_id);
                            if (!is_null($city)) {
                                Yii::app()->db->createCommand()->insert('user_servicecity', array(
                                    'user_id' => $user->id,
                                    'city_id' => $user->city_id,
                                    'region_id' => $city->region_id,
                                    'country_id' => $city->country_id,
                                ));
                            }
                        }

                        // for oauth register
                        $sessionKey = Yii::app()->user->getStateKeyPrefix() . Oauth::SOC_SESSION_INFIX;
                        $socialData = Yii::app()->session->itemAt($sessionKey);

                        try {
                            if (!empty($socialData)) {
                                $oauth = new Oauth();
                                $oauth->user_id = $user->id;
                                $oauth->type_id = $socialData['typeid'];
                                $oauth->uid = $socialData['uid'];
                                $oauth->account_name = $socialData['account_name'];
                                $oauth->social_name = $socialData['social_name'];
                                $oauth->save(true);
                            }

                            $socialData = Yii::app()->session->add($sessionKey, array()); // remove after save
                            $user_transaction->commit();

                            // После комита запускаем afterSave с флагом ручного запуска
                            $user->afterSave(true);

                        } catch (Exception $e) {
                            $user_transaction->rollBack();
                            throw new CHttpException(403);
                        }

                        Yii::app()->redis->set($user->id . '_pass', serialize($unsafe_pass));
                        $message = $user->activateKey($user->role);

                        $this->spec_div_class = 'registration registration-ok';

                        //Сделали подобное для SEO так как им надо отслеживать
                        //Регистрации именно по URL
                        if (in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR))) {
                            $this->redirect('/site/registrationOk/specialist');
                        } else {
                            $this->redirect('/site/registrationOk/user');
                        }

                    } else {
                        throw new CHttpException(403);
                    }
                }
            } else {
                // for oauth register
                $sessionKey = Yii::app()->user->getStateKeyPrefix() . Oauth::SOC_SESSION_INFIX;
                $socialData = Yii::app()->session->itemAt($sessionKey);
                if (!empty($socialData)) {
                    $user->firstname = $socialData['firstname'];
                    $user->lastname = $socialData['lastname'];
                    $user->login = $socialData['login'];
                    $user->email = $socialData['email'];
                }
                // end ouath
            }

            $this->render('//site/registration', array(
                'city_name' => Yii::app()->request->getParam('city_name'),
                'available_roles' => $available_roles,
                'model' => $user
            ));
        }
    }


    /**
     * Action просто рендерит страничку
     * с сообщением об успешной регистрации
     */
    public function actionRegistrationOk()
    {
        $this->render("registrationOk", array('message' => User::$message));
        Yii::app()->end();
    }

    protected function performAjaxValidation($model, $user_data)
    {
        if (isset($_POST['ajax']) && ($_POST['ajax'] === 'registration-form' || $_POST['ajax'] === 'registration-form-designer')) {

            echo CActiveForm::validate(array($model, $user_data));
            Yii::app()->end();
        }
    }

    /**
     * Выводит кастомизированные ошибки модели.
     * @param CActiveRecord $model
     * @return Значение не возваращает. В месте вызова выводи строку ошибок.
     */
    public function showErrors($model)
    {
        if ($model->getErrors())
            echo $str_errors = CHtml::tag('p', array('class' => 'error'), 'Для завершения регистрации необходимо корректно заполнить все поля', true);
        else
            return;
    }

    /**
     * @brief Автокомплит для Городов в личном кабинете пользователя
     * @deprecated использовать в /utility/autocompletecity
     * TODO: delete this action
     */
    public function actionAutocompletecity($term)
    {
        $this->layout = false;

        if (Yii::app()->request->isAjaxRequest && !empty($term)) {

            $sphinxClient = Yii::app()->search;
            $term = $sphinxClient->escapeString($term);

            $cityProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'city_name',
                'modelClass' => 'City',
                'query' => $term . '*',
                'sortMode' => SPH_SORT_EXTENDED,
                'sortExpr' => 'rating asc',
                'matchMode' => 'SPH_MATCH_ANY',
                'pagination' => array('pageSize' => 10),
            ));
            $cities = $cityProvider->getData();
            $arr = array();

            foreach ($cities as $city) {
                $arr[] = array(
                    'label' => $city->name . ' (' . $city->region->name . ', ' . $city->country->name . ')',
                    'value' => $city->name . ' (' . $city->region->name . ', ' . $city->country->name . ')',
                    'id' => $city->id, // return value from autocomplete
                    'country_id' => $city->country_id,
                );
            }
            echo CJSON::encode($arr);
        }
    }

    /**
     * @brief Sends mail for restore user's password
     *
     * @param type $model
     */
    protected function sendRestoreMail($model)
    {
        Yii::app()->mail->create('restorePassword')
            ->to($model->email)
            ->priority(EmailComponent::PRT_HIGHT)
            ->params(array(
                'username' => $model->name,
                'restore_link' => 'site/restore/key/' . $model->activateKey,
                'sign_A' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
            ))
            ->send();
    }

    /*
     * @brief Activation key check
     */

    public function actionActivation()
    {
        if (!Yii::app()->user->isGuest) {
            $this->redirect('/site/index');
        }

        Yii::import('application.modules.content.models.Content');

        if (!empty($_GET['key'])) {
            $user = User::model()->find('activateKey = :activateKey', array(':activateKey' => $_GET['key']));

            //  Find user with this activation code
            if (!empty($user)) {
                if ($user->status == User::STATUS_ACTIVE) {
                    $this->render('registrationActivate', array('breadcrumb' => 'Активация аккаунта',
                        'messageTitle' => 'Активация аккаунта',
                        'messageText' => 'Аккаунт уже активирован!'));
                } else {
                    $user->status = User::STATUS_ACTIVE;
                    $user->save();


                    $unsafe_pass = unserialize(Yii::app()->redis->get($user->id . '_pass'));
                    Yii::app()->redis->delete($user->id . '_pass');

                    // Отправляем разные письма пользователям в зависимости от роли
                    if ($user->role == User::ROLE_SPEC_FIS || $user->role == User::ROLE_SPEC_JUR) {
                        $mailTemplate = 'userActivateSpecialist';
                        $options = array(
                            'user_name' => $user->name,
                            'user_login' => $user->login,
                            'user_pass' => $unsafe_pass,
                            'login' => $user->login, // используется для формирования ссылок
                            'sign_B' => Yii::app()->mail->create('sign_B')->useView(false)->prepare()->getMessage(),
                        );
                    } else {
                        $mailTemplate = 'userActivate';
                        $options = array(
                            'user_name' => $user->name,
                            'user_login' => $user->login,
                            'user_pass' => $unsafe_pass,
                            'sign_B' => Yii::app()->mail->create('sign_B')->useView(false)->prepare()->getMessage(),
                        );
                    }

                    Yii::app()->mail->create($mailTemplate)
                        ->to($user->email)
                        ->params($options)
                        ->send();

                    //MsgBody::newMessage($user->id, $message);
                    // Перегенериваем активационный код
                    User::regenActivateKey($user);
                    $user->login(true);

                    if (in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR))) {
                        $content = Content::model()->findByAttributes(array('alias' => 'activationSpecialist'));
                    } else {
                        $content = Content::model()->findByAttributes(array('alias' => 'activationUser'));
                    }
                    if (is_null($content))
                        $data = '';
                    else
                        $data = $content->content;

                    $data = str_replace(':user_name:', $user->firstname, $data);
                    $data = str_replace(':service_link:', "/users/{$user->login}/services/", $data);
                    $data = str_replace(':portfolio_link:', "/users/{$user->login}/portfolio/", $data);

                    $this->render('customRegistrationActivate', array(
                        'breadcrumb' => 'Активация аккаунта',
                        'data' => $data,
                    ));
                }
            } else {

                throw new CHttpException(403);
            }
        } else {
            // Заплатка для зареганых менеджерами дизайнеров
            $key = 'User_emailForActivate';
            $email = Yii::app()->session->get($key);
            if (!is_null($email)) {
                $user = User::model()->findByAttributes(array('email' => $email));
                if ($user) {
                    $user->activateKey($user->role);
                    $cacheKey = 'User_emailDateSend_' . $user->id;

                    Yii::app()->session->remove($key);
                    Yii::app()->cache->set($cacheKey, time(), 86400);
                    $this->hide_div_content = true;
                    $this->spec_div_class = 'e404';
                    return $this->render('activationMail');
                }
            }
            $this->redirect(Yii::app()->user->returnUrl);
        }
    }

    /**
     * @brief Displays the login page
     */
    public function actionLogin()
    {
        $this->layout = '//layouts/grid_main';
        $this->bodyClass = array('auth login_old');

        if (!Yii::app()->user->isGuest) {
            $this->redirect('/site/index');
        }

        $user = new User('login');

        // collect user input data
        if (isset($_POST['User'])) {
            $user->attributes = $_POST['User'];

            // validate user input and redirect to the previous page if valid
            if ($user->validate('login') && $user->login()) {
                $returnUrl = '/';
                UserService::checkAuth();
                $this->redirect($returnUrl);
            }
        }

        // display the login form
        $this->render('//site/login', array('user' => $user));
    }

    public function actionAjaxlogin()
    {
        if (!Yii::app()->getUser()->getIsGuest()) {
            $this->redirect('/site/index');
        }

        $success = false;
        $tmpPassRequired = false;
        $message = '';

        if (Yii::app()->getRequest()->getIsAjaxRequest() && isset($_POST['User'])) {
            $model = new User('login');
            $model->attributes = $_POST['User'];

            if ($model->validate('login') && $model->login()) {
                UserService::checkAuth();
                $success = true;
            } else {
                $success = false;

                if (
                    $model->loginErrorCode() === UserIdentity::NOTICE_TMPPASS_REQUIRED
                    || $model->loginErrorCode() === UserIdentity::ERROR_TMPPASS
                ) {

                    $tmpPassRequired = true;
                }
            }
//echo "<pre>";
//var_dump(get_class($model));
//die();
//print_r($model->getErrors());
//print_r(get_class_methods($model));
//die();
            if ($model->getError('login')) {
//die('1');
                $message['User[login]'] = $model->getError('login');
            } else {
//die('2');
                $message['User[password]'] = 'Неверно указан логин или пароль';
            }

        }
//var_dump('adsfasdfasdf');
        die(CJSON::encode(array(
            'success' => $success,
            'tmpPassRequired' => $tmpPassRequired,
            'message' => $message
        )));
    }

    /**
     * @brief Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        UserService::checkAuth();
        $this->redirect(Yii::app()->homeUrl);
    }

    /**
     * @brief Action restores a forgotten password.
     */
    public function actionForgot()
    {
        $this->layout = '//layouts/grid_main';
        $this->bodyClass = array('auth', 'remind');


        if (!Yii::app()->user->isGuest) {
            $this->redirect('/site/index');
        }

        $model = new User();

        $userRequest = Yii::app()->request->getParam('User');
        if ($userRequest && $userRequest['email'] != '') {
            $model->attributes = $userRequest;

            /** @var $user User */
            $user = User::model()->findByAttributes(array('email' => $model->email, 'status' => User::STATUS_ACTIVE));

            if ($user) {

                User::regenActivateKey($user);

                // Отправляем письмо со ссылкой на смену
                $this->sendRestoreMail($user);

                return $this->render('forgotSendOk');
            } else
                $model->addError('email', 'Указанный адрес не соответствует ни одной учетной записи');
        }

        $this->render('//site/forgot', array(
            'model' => $model
        ));
    }

    /**
     * @brief Восстановление пароля
     * @param string $key
     * @return boolean
     */
    public function actionRestore($key)
    {
        $this->hide_div_content = true;
        $this->spec_div_class = 'login-box';

        $model = User::model()->find('activateKey = :activateKey', array(':activateKey' => $key));


        if (!$model)
            throw new CHttpException(404);


        $model->setScenario('restore');

        if (isset($_POST['User'])) {
            $model->password = $_POST['User']['password'];
            $model->validate();

            if (isset($_POST['password_2']) && $model->password != $_POST['password_2']) {
                $model->addError('password', 'Необходимо правильно повторить пароль');
            } elseif (!$model->getError('password')) {
                $unsafe_pass = $model->password;
                $model->password = md5($model->password);
                $model->save(false);

                // Перегенериваем активационных ключ
                User::regenActivateKey($model);

                Yii::app()->mail->create('paswordRestored')
                    ->to($model->email)
                    ->params(array(
                        'username' => $model->name,
                        'user_login' => $model->login,
                        'user_pass' => $unsafe_pass,
                        'sign_A' => Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
                    ))
                    ->send();

                Yii::app()->user->setFlash('login', 'Вы можете авторизоваться, используя свой новый пароль.');
                Yii::app()->request->redirect('/site/login');
                return false;
            }
        } else {
            $model->password = '';
        }

        return $this->render('//site/restore', array(
            'model' => $model
        ));
    }

    /**
     * Метод отправки сообщения от пользователя в форме
     * обратной связи.
     */
    public function actionFeedbackmessage()
    {
        Yii::import('admin.models.*');

        if (!Yii::app()->getRequest()->getIsPostRequest())
            throw new CHttpException(404);

        /** @var $feedback Feedback */
        $feedback = new Feedback();
        if (isset($_POST['Feedback'])) {
            $feedback->attributes = $_POST['Feedback'];

            if ($feedback->validate()) {
                if (ini_get('browscap')) {
                    $info = get_browser(null, true);
                    $userAgent = 'Browser: ' . $info['browser'] . ' Version: ' . $info['version'] . ' Platform: ' . $info['platform'];
                } else {
                    $userAgent = CHtml::encode($_SERVER['HTTP_USER_AGENT']);
                }

                $feedback->user_ip = $_SERVER['REMOTE_ADDR'];
                $feedback->user_agent = $userAgent;
                $feedback->save(false);
                $files = CUploadedFile::getInstancesByName('UploadedFile');
                if (!empty($files)) {
                    $cnt = 0;
                    foreach ($files as $file) {
                        if ($cnt > 2)
                            continue;
                        $feedback->saveFile($file);
                        $cnt++;
                    }
                }
                $feedback->sendMail();
                die(
                CJSON::encode(array('success' => true, 'message' => 'Ваше сообщение успешно отправлено, спасибо.'))
                );

            } else {
                $errors = array();
                foreach (array('name', 'email', 'message') as $key) {
                    if ($feedback->hasErrors($key))
                        $errors[$key] = $feedback->getError($key);
                }
                die (CJSON::encode(array('error' => true, 'description' => $errors)));
            }
        }

        die(
        CJSON::encode(array('error' => true, 'description' => array('message' => 'Некорректные параметры')))
        );
    }

    /**
     * Включение/отключение гео-контента для пользователя.
     * Значение задается посредством ajax-запроса
     * @param bool $enabled
     * @throws CHttpException
     */
    public function actionGeoContent($city_id = null)
    {
        if (!Yii::app()->request->isAjaxRequest)
            throw new CHttpException(400);

        if ($city_id)
            Yii::app()->getUser()->setCookieVariable(Geoip::COOKIE_GEO_SELECTED, (int)$city_id);
        else
            Yii::app()->getUser()->setCookieVariable(Geoip::COOKIE_GEO_SELECTED, (int)$city_id);
    }

    /**
     * Страница с подключенным 5D Планером
     */
    public function actionPlanner()
    {
        $this->render('//site/planner');
    }

    /**
     * Страница Торгового Центра для Большой Медведицы
     */
    public function actionBmAbout()
    {
        Yii::import('application.modules.catalog.models.MallService');
        Yii::import('application.modules.catalog.models.Category');
        $this->bodyClass = 'bm-promo';
        $this->layout = '//layouts/layoutBm';

        $this->hide_div_content = true;

        $mall = Cache::getInstance()->mallBuild;

        $services = MallService::model()->findAll(array(
            'join' => 'INNER JOIN mall_build_service mbs ON mbs.mall_service_id = t.id',
            'condition' => 'mbs.mall_build_id = :mid',
            'params' => array(':mid' => $mall->id),
            'order' => 't.pos ASC'
        ));


        $this->render('//site/bmAbout', array(
            'mall' => $mall,
            'services' => $services
        ));
    }

    public function actionStoreOffer()
    {
        Yii::import('application.modules.catalog.models.StoreOffer');

        $model = new StoreOffer();

        // флаг удачного сохранения
        $goodSave = false;

        if (isset($_POST['StoreOffer'])) {
            if (!isset($_POST['StoreOffer']['accept_rule'])) {
                $_POST['StoreOffer']['accept_rule'] = 0;
            }

            $model->attributes = $_POST['StoreOffer'];
            if ($model->save()) {
                // Формируем данные из формы для письма.
                $data_form = '';
                $data_form .= '<p><strong>Компания</strong>: ' . $model->company . '</p>';
                $data_form .= '<p><strong>Город</strong>: ' . $model->city_name . '</p>';
                $data_form .= '<p><strong>Телефон</strong>: ' . $model->company_phone . '</p>';
                $data_form .= '<p><strong>Email</strong>: ' . $model->company_phone . '</p>';
                $data_form .= '<p><strong>ФИО</strong>: ' . $model->name . '</p>';
                $data_form .= '<p><strong>Должность</strong>: ' . $model->job . '</p>';
                $data_form .= '<p><strong>Сайт</strong>: ' . $model->site . '</p>';
                $data_form .= '<p><strong>Комментарий</strong>: ' . $model->comment . '</p>';


                Yii::app()->mail->create('shopAdvertising')
                    ->to('sales@myhome.ru')
                    ->priority(EmailComponent::PRT_NORMAL)
                    ->params(array(
                        'user_ip' => $_SERVER['REMOTE_ADDR'],
                        'user_agent' => CHtml::encode($_SERVER['HTTP_USER_AGENT']),
                        'data_form' => $data_form,
                        'user_email' => Yii::app()->user->model->email,
                    ))
                    ->send();

                $model->unsetAttributes();
                $goodSave = true;
            }
        }

        $this->render('//site/gridStoreOffer', array(
            'model' => $model,
            'goodSave' => $goodSave,
        ));
    }


    /**
     * Метод для редиректа на главную страницу.
     * Нужен для того, чтобы отследить количество переходов на эту
     * страницу bm.myhome.ru/email
     */
    public function actionEmail()
    {
        $this->redirect('/');
    }


    /**
     * Сохраняет услуги для пользователя на втором шаге в расширенной регистрации.
     *
     * @throws CHttpException
     */
    public function actionAjaxSaveServices()
    {

        $user = Yii::app()->user->model;

        if (!$user) {
            throw new CHttpException(404);
        }

        /** Список выбранных пользователем услуг для сохранения */
        $newUserServices = empty($_POST['User']['services']) ? null : $_POST['User']['services'];

        /** Формирование данных на сохранение в базу с переносом старых значений */
        $sql_values = array();
        $sqlValues2 = array();
        if (!empty($newUserServices)) {

            foreach ($newUserServices as $service_id => $value) {

                $service_id = (int)$service_id;
                // validators

                /** Формирование массива данных для сохранение */
                $sql_values[] = "( '{$user->id}', '{$service_id}')";
                $sqlValues2[] = "( '{$user->id}', '{$service_id}', 0, 0)";

            }

            $transaction = Yii::app()->db->beginTransaction();
            try {
                /** Вставка новых пользовательских услуг (в случае, если пользователь их выбрал) */
                if (!empty($sql_values)) {
                    $sql = 'insert into user_service (`user_id`, `service_id`) values ' . implode(',', $sql_values);
                    Yii::app()->db->createCommand($sql)->execute();

                    $sql = 'insert ignore into user_service_data (`user_id`, `service_id`, `rating`, `project_qt`) values ' . implode(',', $sqlValues2);
                    Yii::app()->db->createCommand($sql)->execute();
                }

                /** Обновление рейтинга */
                Yii::app()->gearman->appendJob('userService', array('userId' => $user->id));

                $transaction->commit();

                exit(json_encode(array(
                    'success' => true
                )));

            } catch (Exception $e) {

                $transaction->rollback();
                throw new CHttpException(500);
            }

        }

        exit(json_encode(array(
            'success' => false,
        )));
    }
}
