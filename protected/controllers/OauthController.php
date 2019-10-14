<?php

/**
 * @brief Авторизация через сторонние сервисы
 * @author Alexey Shvedov <alexsh@yandex.ru>
 */
class OauthController extends Controller
{

        public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

		return array(
			array(
				'allow',
				'actions' => array(
					'twitter',
					'vkontakte',
					'facebook',
					'odnoklassniki',
					'popupclose'
				),
				'users'   => array('*'),
			),
			array(
				'allow',
				'actions' => array(
					'bindfacebook',
					'bindtwitter',
					'bindvkontakte',
					'bindodkl',
					'unbindfacebook',
					'unbindtwitter',
					'unbindvkontakte',
					'unbindodkl',
					'setEmail'
				),
				'users'   => array('@'),
			),
			array(
				'deny',
				'users' => array('*'),
			),
		);
        }

        public function beforeAction($action)
        {
                parent::beforeAction($action);
                Yii::import('application.components.oauth.*');
                return true;
        }

	public function actionOdnoklassniki()
	{
		$this->layout = 'default';

		if (isset($_REQUEST['return']) && $_REQUEST['return'] == 'promo') {
			$returnUrl = '/oauth/odnoklassniki?return=promo';
		} else {
			$returnUrl = '/oauth/odnoklassniki';
		}

		$odkl = new Odnoklassniki($returnUrl);
		$odkl->dataAccess();

		$data = $odkl->getUserInfo();
		if (empty($data))
			throw new CHttpException(500);

		$oauth = Oauth::model()->findByPk(array('type_id' => Oauth::ODKL, 'uid' => $data['uid']));

		if (!is_null($oauth) && $oauth->login()) {
			$this->forward('/oauth/popupclose');
		}


		$socialData = Oauth::$defSocialData;
		$socialData['type_id'] = Oauth::ODKL;
		$socialData['first_name'] = $data['first_name'];
		$socialData['last_name'] = $data['last_name'];
		$socialData['login'] = Amputate::rus2translit($data['name']);
		$socialData['uid'] = $data['uid'];
		$socialData['account_name'] = $data['uid'];
		$socialData['social_name'] = $data['name'];


		$user = new User();

		$unsafePassword = Amputate::generatePassword();
		$user->password = md5($unsafePassword);
		$user->login = User::generateLogin($socialData['login'] . '@');
		$user->email = null;
		$user->firstname = $socialData['first_name'];
		$user->lastname = $socialData['last_name'];
		$user->status = User::STATUS_ACTIVE;
		$user->role = User::ROLE_USER;


		$user_transaction = $user->dbConnection->beginTransaction();

		if ($user->save())
		{
			// Сохраняем данные для пользователя
			$user->data->user_id = $user->id;
			$user->data->save();

			$user_transaction->commit();

			// После комита вызываем afterSave с флагом ручного запуска
			$user->afterSave(true);

			$oauth = new Oauth();
			$oauth->user_id = $user->id;
			$oauth->type_id = $socialData['type_id'];
			$oauth->uid = $socialData['uid'];
			$oauth->account_name = $socialData['account_name'];
			$oauth->social_name = $socialData['social_name'];
			$oauth->save(true);

			$user->login(true);


			/* -----------------------------------------------------
			 *  Редиректим на страницу указания Email'а
			 * -----------------------------------------------------
			 */
			if (isset($_REQUEST['return']) && $_REQUEST['return'] == 'promo') {
				$this->redirect('/oauth/setEmail?return=promo');
			} else {
				$this->redirect('/oauth/setEmail');
			}

		} else {

			Yii::log(
				'Ошибка регистрации через Одноклассники' . "\n" . print_r($user->getErrors(), true),
				CLogger::LEVEL_WARNING
			);


			$this->layout = '//layouts/simple';
			$this->render('//site/registrationFail');

		}

		// invalid data
		$this->forward('popupclose');
	}


	public function actionVkontakte()
        {
                $this->layout = 'default';

		if (isset($_REQUEST['return']) && $_REQUEST['return'] == 'promo') {
			$returnUrl = '/oauth/vkontakte?return=promo';
		} else {
			$returnUrl = '/oauth/vkontakte';
		}

                $vk = new Vkontakte($returnUrl);
                $vk->dataAccess();


		/** @var $oauth Oauth */
		$oauth = Oauth::model()->findByPk(array('type_id' => Oauth::VKONTAKTE, 'uid' => $vk->getUserId()));
                if (!is_null($oauth) && $oauth->login()) {
                        $this->forward('/oauth/popupclose');
                }


		$socialData = Oauth::$defSocialData;
		$data = $vk->execMethod('users.get', array('uids' => $vk->getUserId(), 'fields' => ''));
		if (isset($data['error'])) {
			echo print_r($data, true);
			exit();
		}

		if (!empty($data['response'])) {
			$data = reset($data['response']);

			$socialData['uid'] = $vk->getUserId();
			$socialData['type_id'] = Oauth::VKONTAKTE;
			$socialData['first_name'] = $data['first_name'];
			$socialData['last_name'] = $data['last_name'];
			$socialData['login'] = Amputate::rus2translit($data['first_name'] . '_' . $data['last_name']);
			$socialData['account_name'] = $vk->getUserId();
			$socialData['social_name'] = $data['first_name'] . ' ' . $data['last_name'];
		}

		$user = new User();

		$unsafePassword = Amputate::generatePassword();
		$user->password = md5($unsafePassword);
		$user->login = User::generateLogin($socialData['login'] . '@');
		$user->email = null;
		$user->firstname = $socialData['first_name'];
		$user->lastname = $socialData['last_name'];
		$user->status = User::STATUS_ACTIVE;
		$user->role = User::ROLE_USER;


		$user_transaction = $user->dbConnection->beginTransaction();

		if ($user->save())
		{
			// Сохраняем данные для пользователя
			$user->data->user_id = $user->id;
			$user->data->save();

			$user_transaction->commit();

			// После комита вызываем afterSave с флагом ручного запуска
			$user->afterSave(true);

			$oauth = new Oauth();
			$oauth->user_id = $user->id;
			$oauth->type_id = $socialData['type_id'];
			$oauth->uid = $socialData['uid'];
			$oauth->account_name = $socialData['account_name'];
			$oauth->social_name = $socialData['social_name'];
			$oauth->save(true);

			$user->login(true);

			/* -----------------------------------------------------
			 *  Редиректим на страницу указания Email'а
			 * -----------------------------------------------------
			 */
			if (isset($_REQUEST['return']) && $_REQUEST['return'] == 'promo') {
				$this->redirect('/oauth/setEmail?return=promo');
			} else {
				$this->redirect('/oauth/setEmail');
			}

		} else {

			Yii::log(
				'Ошибка регистрации через ВКонтакте' . "\n" . print_r($user->getErrors(), true),
				CLogger::LEVEL_WARNING
			);

			$this->layout = '//layouts/simple';
			$this->render('//site/registrationFail');
		}
        }

	public function actionSetEmail()
	{
		if (Yii::app()->user->getIsGuest()) {
			throw new CHttpException(404);
		}

		/** @var $user User */
		$user = Yii::app()->user->model;

		if (isset($_POST['email'])) {
			$user->setScenario('user-profile-edit');

			$user->email = $_POST['email'];

			if ($user->save()) {

				/* Отправляем письмо с удачной регистрацией.
					 * Отключаем лог, т.к. в письме будет не зашифрованный пароль.
					 */
				Yii::app()->mail
					->create('userRegistrationDone')
					->to($user->email)
					->params(array(
						'sign_B'   => Yii::app()->mail->create('sign_B')->useView(false)->prepare()->getMessage(),
					))
					->disableLog()
					->priority(EmailComponent::PRT_HIGHT)
					->send();


				if (isset($_REQUEST['return']) && $_REQUEST['return'] == 'promo') {
					return $this->actionPopupclose('/site/promo');
				} else {
					return $this->actionPopupclose('/site/registration');
				}
			}
		}


		$this->layout = '//layouts/simple';
		$this->render('//site/setEmail', array(
			'user'   => $user,
			'return' => (isset($_REQUEST['return']) && $_REQUEST['return'] == 'promo')
				? 'promo' : ''
		));
	}

        public function actionFacebook()
        {
                $this->layout = 'default';

		if (isset($_REQUEST['return']) && $_REQUEST['return'] == 'promo') {
			$returnUrl = '/oauth/facebook?return=promo';
		} else {
			$returnUrl = '/oauth/facebook';
		}

                $fb = new Facebook($returnUrl);
                $fb->dataAccess();

                // user data
                $data = $fb->execMethod('me', array('fields' => 'id, email, first_name, last_name, name'), true);
                $data = CJSON::decode($data);
                if (empty($data['id']))
                        $this->redirect($fb->getCodeUrl());

                $fbUid = $data['id'];

                // if auth link exists
                $oauth = Oauth::model()->findByPk(array('type_id' => Oauth::FACEBOOK, 'uid' => $fbUid));
                if (!is_null($oauth) && $oauth->login()) {
                        return $this->forward('/oauth/popupclose');
                }


		$socialData = Oauth::$defSocialData;

		$socialData['typeid'] = Oauth::FACEBOOK;
		$socialData['firstname'] = $data['first_name'];
		$socialData['lastname'] = $data['last_name'];
		$socialData['login'] = Amputate::rus2translit($data['first_name'] . $data['last_name']);
		$socialData['email'] = $data['email'];
		$socialData['uid'] = $fbUid;
		$socialData['account_name'] = $fbUid;
		$socialData['social_name'] = $socialData['login'];

		$user = new User();

		$unsafePassword = Amputate::generatePassword();
		$user->password = md5($unsafePassword);
		$user->login = User::generateLogin($socialData['login'].'@');
		$user->email = $socialData['email'];
		$user->firstname = $socialData['firstname'];
		$user->lastname = $socialData['lastname'];
		$user->status = User::STATUS_ACTIVE;
		$user->role = User::ROLE_USER;



		$user_transaction = $user->dbConnection->beginTransaction();


		/*
		 * Проверяем есть пользователь с указанным email адресом.
		 */

		$foundUser = User::model()->findByAttributes(array(
			'email' => $user->email
		));

		if ($foundUser) {

			Yii::log(
				'Ошибка регистрации через Facebook' . "\n" . print_r($user->getErrors(), true),
				CLogger::LEVEL_WARNING
			);

			$this->layout = '//layouts/simple';
			$this->render('//site/registrationFailFacebook', array(
				'email' => $user->email
			));

		}
		elseif ($user->save())
		{
			// Сохраняем данные для пользователя
			$user->data->user_id = $user->id;
			$user->data->save();

			$user_transaction->commit();

			// После комита вызываем afterSave с флагом ручного запуска
			$user->afterSave(true);

			$oauth = new Oauth();
			$oauth->user_id = $user->id;
			$oauth->type_id = $socialData['typeid'];
			$oauth->uid = $socialData['uid'];
			$oauth->account_name = $socialData['account_name'];
			$oauth->social_name = $socialData['social_name'];
			$oauth->save(true);

			/* Отправляем письмо с удачной регистрацией.
			 * Отключаем лог, т.к. в письме будет не зашифрованный пароль.
			 */
			Yii::app()->mail
				->create('userRegistrationDone')
				->to($user->email)
				->params(array(
					'sign_B'   => Yii::app()->mail->create('sign_B')->useView(false)->prepare()->getMessage(),
				))
				->disableLog()
				->priority(EmailComponent::PRT_HIGHT)
				->send();

			$user->login(true);


			if (isset($_REQUEST['return']) && $_REQUEST['return'] == 'promo') {
				return $this->actionPopupclose('/site/promo');
			} else {
				return $this->actionPopupclose('/site/registration');
			}

		} else {

			Yii::log(
				'Ошибка регистрации через Facebook' . "\n" . print_r($user->getErrors(), true),
				CLogger::LEVEL_WARNING
			);

			$this->layout = '//layouts/simple';
			$this->render('//site/registrationFail');
		}

        }

        public function actionTwitter()
        {
                $this->layout = 'default';

                $tw = new Twitter();
                if (!$tw->checkAccess()) {
                        $tw->setAccessToken($_GET);
                }
                $data = $tw->execMethod('1/account/verify_credentials.json');

                // incorrect data
                if (empty($data->id)) {
                        $this->redirect($tw->getRequestTokenUrl());
		}

                $twUid = $data->id;
                // find linked account
                $oauth = Oauth::model()->findByPk(array('type_id' => Oauth::TWITTER, 'uid' => $twUid));
                if (!is_null($oauth) && $oauth->login()) {
                        $this->forward('/oauth/popupclose');
                }

                // there is no link
                if (empty($_GET['type'])) { // type to link not found
                        return $this->render('authSwitch');
                } else if ($_GET['type'] == 'auth') { // auth in myhome
                        $userAuth = new User('login');
                        if (isset($_POST['authorize'])) { // link sevice to site account
                                return $this->auth($userAuth, Oauth::TWITTER, $twUid, $data->screen_name, $data->name);
                        }

                        return $this->render('auth', array('user' => $userAuth));
                } else if ($_GET['type'] == 'register') {
                        $socialData = Oauth::$defSocialData;

                        $names = explode(' ', $data->name, 2);
                        $socialData['typeid'] = Oauth::TWITTER;
                        $socialData['firstname'] = (isset($names[0])) ? $names[0] : '';
                        $socialData['lastname'] = (isset($names[1])) ? $names[1] : '';
                        $socialData['login'] = $data->screen_name;
                        $socialData['uid'] = $twUid;
                        $socialData['account_name'] = $data->screen_name;
                        $socialData['social_name'] = $data->name;

                        $sessionKey = Yii::app()->user->getStateKeyPrefix() . Oauth::SOC_SESSION_INFIX;
                        Yii::app()->session->add($sessionKey, $socialData);

                        return $this->actionPopupclose('/site/registration/#/designer');
                }
                // invalid data
                $this->forward('popupclose');
        }

        /**
         * Привязывает к текущему авторизованному пользователю
         * акканут Twitter'a и редиректит обратно на страницу
         * настроек персональных данных.
         * 
         * @author Sergey Seregin
         */
        public function actionBindtwitter()
        {

                // Если пользователь не авторизован, то ошибка.
                if (Yii::app()->user->isGuest)
                        throw new CHttpException(403);

                // Получаем данные об ID твиттера
                // В качестве параметра указываем адрес, который будет вызван твиттером
                // после подтверждения доступа.
                $tw = new Twitter('/oauth/bindtwitter');
                if (!$tw->checkAccess()) {
                        $tw->setAccessToken($_GET);
                }
                $data = $tw->execMethod('account/verify_credentials');

                // incorrect data
                if (empty($data->id))
                        $this->redirect($tw->getRequestTokenUrl());

                $twUid = $data->id;

                // Проверем не привязал ли пользователь уже к твиттеру
                $oauth = Oauth::model()->findByPk(array('type_id' => Oauth::TWITTER, 'uid' => $twUid));
                if (is_null($oauth)) {
                        // Сохраняем
                        $oauth = new Oauth();
                        $oauth->user_id = Yii::app()->user->id;
                        $oauth->type_id = Oauth::TWITTER;
                        $oauth->uid = $twUid;
                        $oauth->account_name = $data->screen_name;
                        $oauth->social_name = $data->name;

                        if ($oauth->validate()) {
                                $oauth->save(false);
                        } else {
                                throw new CHttpException(403);
                        }
                }

                $this->redirect('/member/profile/social#close');
        }

        /**
         * Привязывает к текущему авторизованному пользователю
         * акканут ВКонтакте и редиректит обратно на страницу
         * настроек персональных данных.
         * 
         * @author Sergey Seregin
         */
        public function actionBindvkontakte()
        {
                $vk = new Vkontakte('/oauth/bindvkontakte');
                $vk->dataAccess();

                // if auth link exists
                $oauth = Oauth::model()->findByPk(array('type_id' => Oauth::VKONTAKTE, 'uid' => $vk->getUserId()));
                if (is_null($oauth)) {
                        $data = $vk->execMethod('users.get', array('uids' => $vk->getUserId(), 'fields' => ''));

                        if (!empty($data['response'])) {
                                $data = reset($data['response']);

                                // Сохраняем
                                $oauth = new Oauth();
                                $oauth->user_id = Yii::app()->user->id;
                                $oauth->type_id = Oauth::VKONTAKTE;
                                $oauth->uid = $vk->getUserId();
                                $oauth->account_name = $data['uid'];
                                $oauth->social_name = $data['first_name'] . ' ' . $data['last_name'];

                                if ($oauth->validate()) {
                                        $oauth->save(false);
                                } else {
                                        throw new CHttpException(403);
                                }
                        }

			$this->redirect('/member/profile/social#close');

                } else {
			$this->layout = '//layouts/simple';
			$this->render('//site/accountBindFail', array(
				'socialName' => 'ВКонтакте'
			));
		}


        }

	/**
	 * Привязывает к текущему авторизованному пользователю
	 * акканут Одноклассников и редиректит обратно на страницу
	 * настроек персональных данных.
	 *
	 * @author Alexey Shvedov
	 */
	public function actionBindodkl()
	{

		$odkl = new Odnoklassniki('/oauth/bindodkl');
		$odkl->dataAccess();

		$data = $odkl->getUserInfo();
		if (empty($data))
			throw new CHttpException(500);

		$oauth = Oauth::model()->findByPk(array('type_id' => Oauth::ODKL, 'uid' => $data['uid']));

		if (is_null($oauth)) {
			// Сохраняем
			$oauth = new Oauth();
			$oauth->user_id = Yii::app()->getUser()->getId();
			$oauth->type_id = Oauth::ODKL;
			$oauth->uid = $data['uid'];
			$oauth->account_name = $data['uid'];
			$oauth->social_name = $data['name'];

			if ($oauth->validate()) {
				$oauth->save(false);
			} else {
				throw new CHttpException(403);
			}

			$this->redirect('/member/profile/social#close');
		} else {

			$this->layout = '//layouts/simple';
			$this->render('//site/accountBindFail', array(
				'socialName' => 'Одноклассники'
			));
		}

	}

        /**
         * Привязывает к текущему авторизованному пользователю
         * акканут Facebook и редиректит обратно на страницу
         * настроек персональных данных.
         * 
         * @author Sergey Seregin
         */
        public function actionBindfacebook()
        {
                $fb = new Facebook('/oauth/bindfacebook');
		$fb->dataAccess();

                // user data
                $data = $fb->execMethod('me', array('fields' => 'id, email, first_name, last_name, name'));
		$rr = array_keys($data);
                $data = CJSON::decode(reset($rr));
                if (empty($data['id']))
                        $this->redirect($fb->getCodeUrl());

                $fbUid = $data['id'];

                // if auth link exists
                $oauth = Oauth::model()->findByPk(array('type_id' => Oauth::FACEBOOK, 'uid' => $fbUid));
                if (is_null($oauth)) {
                        // Сохраняем
                        $oauth = new Oauth();
                        $oauth->user_id = Yii::app()->user->id;
                        $oauth->type_id = Oauth::FACEBOOK;
                        $oauth->uid = $fbUid;
                        $oauth->account_name = $fbUid;
                        $oauth->social_name = Amputate::rus2translit($data['first_name'] . $data['last_name']);

                        if ($oauth->validate()) {
                                $oauth->save(false);
                        } else {
                                throw new CHttpException(403);
                        }

			$this->redirect('/member/profile/social#close');

                } else {
			$this->layout = '//layouts/simple';
			$this->render('//site/accountBindFail', array(
				'socialName' => 'Facebook'
			));
		}

        }

        /**
         * Отзвязывает аккаунт твиттера у текущего авторизованного пользователя
         * и редиректит обратно на страницу редактирования персональных данных.
         * 
         * @author Sergey Seregin
         */
        public function actionUnbindtwitter()
        {
                Oauth::model()->deleteAllByAttributes(array('type_id' => Oauth::TWITTER, 'user_id' => Yii::app()->getUser()->getId()), array('limit'=>1));
                $this->redirect('/member/profile/social');
        }

        /**
         * Отзвязывает аккаунт ВКонтакте у текущего авторизованного пользователя
         * и редиректит обратно на страницу редактирования персональных данных.
         * 
         * @author Sergey Seregin
         */
        public function actionUnbindvkontakte()
        {
                Oauth::model()->deleteAllByAttributes(array('type_id' => Oauth::VKONTAKTE, 'user_id' => Yii::app()->getUser()->getId()), array('limit'=>1));
                $this->redirect('/member/profile/social');
        }

	/**
	 * Отзвязывает аккаунт Одноклассников у текущего авторизованного пользователя
	 * и редиректит обратно на страницу редактирования персональных данных.
	 *
	 * @author Alexey Shvedov
	 */
	public function actionUnbindodkl()
	{
		$tmp=Oauth::model()->deleteAllByAttributes(array('type_id' => Oauth::ODKL, 'user_id' => Yii::app()->getUser()->getId()), array('limit'=>1));
		$this->redirect('/member/profile/social');
	}

        /**
         * Отзвязывает аккаунт Facebook у текущего авторизованного пользователя
         * и редиректит обратно на страницу редактирования персональных данных.
         * 
         * @author Sergey Seregin
         */
        public function actionUnbindfacebook()
        {
                Oauth::model()->deleteAllByAttributes(array('type_id' => Oauth::FACEBOOK, 'user_id' => Yii::app()->getUser()->getId()), array('limit'=>1));
                $this->redirect('/member/profile/social');
        }

        private function auth($user, $type_id, $uid, $account_name = '', $socialName = '')
        {
                $this->layout = 'default';

                $user->attributes = $_POST['User'];

                // validate user input and redirect to the previous page if valid
                if ($user->validate('login') && $user->login()) {
                        $oauth = new Oauth();
                        $oauth->user_id = Yii::app()->user->id;
                        $oauth->type_id = $type_id;
                        $oauth->uid = $uid;
                        $oauth->account_name = $account_name;
                        $oauth->social_name = $socialName;
                        if ($oauth->validate()) {
                                $oauth->save(false);
                        } else {
                                throw new CHttpException(403);
                        }
                        return $this->actionPopupclose();
                }
                return $this->render('auth', array('user' => $user));
        }

        public function actionPopupclose($redirectUrl = '')
        {
                return $this->renderPartial('popupclose', array('redirectUrl' => $redirectUrl));
        }
}