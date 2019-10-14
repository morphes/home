<?php

/**
 * @brief Обработка личного кабинета
 * @author Sergey Seregin <sss@medvediza.ru>
 */
class ProfileController extends FrontController
{

        public function filters()
        {

                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
                        array('allow',
                                'actions' => array('index', 'settings', 'deleteprice', 'deleteavatar', 'loadchildcitys',
					'updatecity','password', 'options', 'social', 'add_other_social', 'delete_other_social',
					'social_update', 'copyright', 'axhideflash','StatHitAjax'),
                                'roles' => array(
					User::ROLE_ADMIN,
					User::ROLE_GUEST,
					User::ROLE_JUNIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
                                        User::ROLE_STORES_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_SPEC_FIS,
					User::ROLE_SPEC_JUR,
					User::ROLE_USER
				),
                                'users' => array('@'),
                        ),
                        array('allow',
                                'actions' => array('index', 'user', 'contacts', 'services', 'activity', 'portfolio', 'project'),
                                'users' => array('*'),
                        ),
                        array('allow',
                                'actions'=>array('draft', 'moveitem', 'statistic'),
                                'roles'=>array(
					User::ROLE_SPEC_FIS,
					User::ROLE_SPEC_JUR,
					User::ROLE_POWERADMIN
				),
                        ),
                        array('deny',
                                'users' => array('*'),
                        ),
                );
        }

	public function beforeAction($action)
        {
		if (parent::beforeAction($action)) {
			Yii::import('application.modules.idea.models.*');
			$this->menuIsActiveLink = true;
			return true;
		}
        }

        /**
         * Главная страница профиля пользователя
         * @param integer $uid
         */
        public function actionIndex($id = null)
        {

		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile', 'spec');

                if($id) {
                        $user = User::model()->findByPk((int)$id);
                        if(!$user)
				$user = Cache::getInstance()->user;
                } else {
			$user = Cache::getInstance()->user;
                }
		
		if (! $user instanceof User)
			throw new CHttpException(404);
		
		// Если смотрим свой профиль, запоминаем это
		if ($user->id == Yii::app()->user->id)
			$owner = true;
		else
			$owner = false;
		
		$message = trim(Yii::app()->request->getParam('message'));
		if (!$owner && !empty($message)) {
			MsgBody::newMessage($user->id, $message);
			$this->refresh(true, '?send=ok');
		}
		if (Yii::app()->request->getParam('send') == 'ok')
			Yii::app()->user->setFlash('sendGood', 'Ваше сообщение отправлено');
			
                $this->incrementProfileViews($user->id);

		// Специалисты
                if (in_array($user->role, array( User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR ))) {

			//Если профиль открыл не владелец
			//профиля то наращиваем счетчич
			if(!$owner)
			{
				StatSpecialist::hit($user->id,StatSpecialist::TYPE_HIT_PROFILE);
				$urlReferrer = Yii::app()->request->getUrlReferrer();

				if($urlReferrer)
				{
					$urlParse = parse_url($urlReferrer);
				}

				$parseUrlHost = parse_url(Yii::app()->request->getHostInfo());

				/**
				 * Если переход со списков специалистов то наращиваем счетчик
				 */
				if(isset($urlParse) && $parseUrlHost['host'] == $urlParse['host'])
				{
					$urlPath = $urlParse['path'];
					$stringArray = explode("/", $urlPath);
					if(isset($stringArray[1]) && isset($stringArray[2]) && $stringArray[1] == 'specialist')
					{
						StatSpecialist::hit($user->id, StatSpecialist::TYPE_CLICK_PROFILE_IN_LIST);
					}
				}
			}

			$this->menuActiveKey = 'designer';
			$lastReviews = Review::model()->findAllByAttributes(array('type'=>Review::TYPE_REVIEW, 'status'=>Review::STATUS_SHOW, 'spec_id'=>$user->id), array('order'=>'id DESC', 'limit'=>3));


			return $this->render(
				'//member/profile/specialist/index',
				array(
					'user'         => $user,
					'projects'     => $this->_getLatestProjects($user, time()),
					'lastReviews'  => $lastReviews,
					'serviceList'  => $user->getServiceList(),
					'owner'        => $owner
				),
				false,
				array('profileSpecialist', array('user' => $user))
			);

                // Администраторы магазинов
                } elseif($user->role == User::ROLE_STORES_ADMIN) {

			$this->bodyClass = 'profile user';

                        $social = UserSocial::model()->findAllByAttributes(array('user_id' => $user->id));
                        return $this->render(
                                '//member/profile/store/admin/index',
                                array(
                                        'user' => $user,
                                        'social' => $social,
                                        'owner'	 => $owner,
                                ),
                                false,
                                array('profileStore', array('user' => $user))
                        );

		// Администратор торгового центра
		} elseif($user->role == User::ROLE_MALL_ADMIN){

			$this->bodyClass = 'profile user';

			$social = UserSocial::model()->findAllByAttributes(array('user_id' => $user->id));
			return $this->render(
				'//member/profile/user/index',
				array(
					'user' => $user,
					'social' => $social,
					'owner'	 => $owner,
				),
				false,
				array('profileMall', array('user' => $user))
			);

                // Владельцы квартир
                } else {

			$this->bodyClass = 'profile user';

			$social = UserSocial::model()->findAllByAttributes(array('user_id' => $user->id));
			return $this->render(
				'//member/profile/user/index',
				array(
					'user' => $user,
					'social' => $social,
					'owner'	 => $owner,
				),
				false,
				array('profileUser', array(
					'user' => $user,
				))
			);
                }
        }

        /**
         * Поддержка старых форматов урл на страницу пользователя
         * Старый формат урл используется в нескольких десятках случаев в проекте, поэтому проще пока
         * использовать данный метод
         * @param integer $id
         */
        public function actionUser($id)
        {
                return $this->redirect($this->createUrl('index', array('id'=>$id)), true, 301);
        }


        /**
         * Возвращает массив последних работ пользователя
         * @param $user
         * @param int $limit
         * @return array
         */
        private function _getLatestProjects($user, $create_time, $limit = 4)
        {
                $st1 = Interior::STATUS_MODERATING;
                $st2 = Interior::STATUS_ACCEPTED;
                $st3 = Interior::STATUS_REJECTED;
                $st4 = Interior::STATUS_CHANGED;
                $st5 = Interior::STATUS_VERIFIED;
                $st6 = Architecture::STATUS_TEMP_IMPORT;

                $result = Yii::app()->db->createCommand("
                        (SELECT id, service_id, create_time, 'interior' as tname
                                FROM interior
                                WHERE author_id={$user->id} AND (status = {$st1} OR status = {$st2} OR status = {$st3} OR status = {$st4} OR status = {$st5}) AND create_time < {$create_time}
                                ORDER BY create_time DESC LIMIT {$limit})

                        UNION
                        (SELECT id, service_id, create_time, 'portfolio' as tname
                                FROM portfolio
                                WHERE author_id={$user->id} AND (status = {$st1} OR status = {$st2} OR status = {$st3} OR status = {$st4} OR status = {$st5}) AND create_time < {$create_time}
                                ORDER BY create_time DESC LIMIT {$limit})

                        UNION
                	   (SELECT id, service_id, create_time, 'architecture' as tname
                                FROM architecture
                                WHERE author_id={$user->id} AND (status = {$st1} OR status = {$st2} OR status = {$st3} OR status = {$st4} OR status = {$st5} OR status = {$st6}) AND create_time < {$create_time}
                                ORDER BY create_time DESC LIMIT {$limit})

                        UNION
                	   (SELECT id, service_id, create_time, 'interiorpublic' as tname
                                FROM interiorpublic
                                WHERE author_id={$user->id} AND (status = {$st1} OR status = {$st2} OR status = {$st3} OR status = {$st4} OR status = {$st5}) AND create_time < {$create_time}
                                ORDER BY create_time DESC LIMIT {$limit})
                        ORDER BY create_time DESC LIMIT {$limit};
                ")->queryAll();

                $projects = array();
                foreach($result as $item) {
			$tmp = null;
                        if ($item['service_id'] == Interior::SERVICE_ID)
			{
				if ($item['tname'] == 'interior')
                                	$tmp = Interior::model()->findByPk($item['id']);
				elseif ($item['tname'] == 'interiorpublic')
					$tmp = Interiorpublic::model()->findByPk($item['id']);
			}
			elseif ($item['service_id'] == Architecture::SERVICE_ID)
			{
				$tmp = Architecture::model()->findByPk($item['id']);
			}
                        else
			{
                                $tmp = Portfolio::model()->findByPk($item['id']);
			}

			if ($tmp) {
				$projects[] = $tmp;
			}
                }
                return $projects;
        }

        /**
         * Увеличивает количество просмотров профиля
         *
         * @param integer $user_id Идетнификатор пользователя
         */
        private function incrementProfileViews($user_id)
        {
                $user_id = (int)$user_id;

                // Получаем ip пользователя, просматривающего страницу
                $ip = Yii::app()->request->userHostAddress;
		$ip = ip2long($ip);

                $command = Yii::app()->db->createCommand();

                /*
                 * Проверяем есть ли для текущего просматривающего профиль
                 * запись в БД о том, что он смотрел в профиль в течение ПОСЛЕДНИХ СУТОК.
                 * Если нет, то считаем его голос.
                 */
                $command->select('*')
                        ->from('profile_views')
                        ->where('time > :offset_time AND user_id = :user_id AND ip = :ip', array(
                                ':offset_time' => (int)(time() - 86400), // Время между засчитанными просмотрами в секундах
                                ':user_id' => $user_id,
                                ':ip' => $ip,
                        )
                );
                $finded = $command->queryRow();

                // Если не найден голос.
                if (!$finded) {

                        // Увеличиваем количество просмотров профиля пользователя
                        $redis = Yii::app()->redis;
                        $redis->incr('profile_view_cnt::' . $user_id);

                        $command = Yii::app()->db->createCommand();
                        // Пишем голос в таблицу profile_views
                        $command->insert('profile_views', array(
                                'time' => time(),
                                'user_id' => $user_id,
                                'ip' => $ip,
                        ));
                }
        }

        /**
         * Получить количество просмотров профиля пользователя
         *
         * @param integer $user_id Идентификатор пользвоателя
         * return integer Количество просмотров
         */
        private function getProfileViews($user_id)
        {
                $user_id = (int)$user_id;

                // Увеличиваем количество просмотров профиля пользователя
                $redis = Yii::app()->redis;

                return (int)$redis->get('profile_view_cnt::' . $user_id);
        }




        /**
         * Показывает в зависимости от роли соответсвующую страницу
         * для редактирования персональных данных
         */
        public function actionSettings()
        {
                switch (Yii::app()->user->role) {
                        case User::ROLE_USER:
                        case User::ROLE_JUNIORMODERATOR:
                        case User::ROLE_MODERATOR:
			case User::ROLE_SENIORMODERATOR:
                        case User::ROLE_STORES_ADMIN:
                        case User::ROLE_ADMIN:
                                return $this->_settingsUser();
                        case User::ROLE_SALEMANAGER:
                                return $this->_settingsUser();
                        case User::ROLE_SPEC_FIS:
                                return $this->_settingsSpecialist();
                        case User::ROLE_SPEC_JUR:
                                return $this->_settingsSpecialist();
                        case User::ROLE_POWERADMIN:
                                return $this->_settingsSpecialist();
                }
                throw new CHttpException(404);
        }

        /**
         * Страница с настройками аккаунта текущего пользователя.
         */
        public function actionOptions()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile', 'user-edit');


                $user = Yii::app()->user->model;

                $dataRequest = Yii::app()->request->getParam('UserData');

                if ($dataRequest) {
                        $user->data->attributes = $dataRequest;

                        if ($user->data->save()) {

                                Yii::app()->user->setFlash('msg_success', 'Изменения сохранены');
                        }
                }

                if(in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR))){
                        $this->menuActiveKey = 'designer';
                        return $this->render('//member/profile/specialist/options', array(
                                'user' => $user,
                        ));
                }

                $this->render('//member/profile/user/options', array(
                        'user' => $user,
                ));
        }

        public function actionContacts()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

		$user = Cache::getInstance()->user;

                if ($user instanceof User) {
                        // Если смотрим свой профиль, запоминаем это
                        if ($user->id == Yii::app()->user->id)
                                $owner = true;
                        else
                                $owner = false;

			$msgError = '';
			$msgSuccess = '';
			if (isset($_POST['message']) && empty($_POST['message'])) {
				$msgError = 'Вы ничего не написали';
			}

                        $message = trim(Yii::app()->request->getParam('message'));
                        if (!$owner && !empty($message)) {
                                MsgBody::newMessage($user->id, $message);
				$msgSuccess = 'Ваше сообщение успешно отправлено, спасибо.';
                        }

                        $social = UserSocial::model()->findAllByAttributes(array('user_id' => $user->id));

			if ($user->role == User::ROLE_SPEC_JUR || $user->role == User::ROLE_SPEC_FIS) {

				//Если профиль просматривает не владелец
				//то наращиваем счетчик просмотров
				if(!$owner)
				{
					StatSpecialist::hit($user->id,StatSpecialist::TYPE_HIT_PROFILE);
					StatSpecialist::hit($user->id,StatSpecialist::TYPE_HIT_CONTACTS);

				}
                                $this->menuActiveKey = 'designer';
				return $this->render(
					'//member/profile/specialist/contacts',
					array(
						'user' => $user,
						'owner' => $owner,
						'social' => $social,
						'msgError' => $msgError,
						'msgSuccess' => $msgSuccess,
					),
					false,
					array('profileSpecialist', array('user' => $user))
				);
			}
                }


                throw new CHttpException(404);
        }

	/**
	 * Активность пользователя (комментарии)
	 * @author Alexey Shvedov
	 */
	public function actionActivity()
	{
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile', 'user');

		$user = Cache::getInstance()->user;
		
		if (! $user instanceof User )
			throw new CHttpException(404);

		$view = Yii::app()->request->getParam('view', 'all');
		$condition = 'user_id=:user_id';
		$params = array(':user_id' => $user->id);
		switch ($view) {
			case 'all':{} break;
			case 'review':{
				$condition .= ' AND model=:model';
				$params[':model'] = 'Review';
			} break;
			case 'comment':{
				$condition .= ' AND model=:model';
				$params[':model'] = 'Comment';
			} break;
			default: throw new CHttpException(404);
		}

		$dataProvider = new CActiveDataProvider('Activity', array(
			'criteria' => array(
				'condition' => $condition,
				'order' => 'create_time DESC',
				'params' => $params,
			),
			'pagination' => array(
				'pageSize' => 20,
			),
		));

		$sql = 'SELECT SUM(IF(rating IN ('.Review::RATING_PLUS.','.Review::RATING_RECOMMEND.'), 1,0)) as plus, SUM(IF(rating='.Review::RATING_MINUS.', 1,0)) as minus '
			.'FROM review WHERE type='.Review::TYPE_REVIEW.' AND status='.Review::STATUS_SHOW.' AND author_id='.$user->id;
		$statistic = Yii::app()->db->createCommand($sql)->queryRow();
		if (is_null($statistic['plus']))
			$statistic['plus'] = 0;
		if (is_null($statistic['minus']))
			$statistic['minus'] = 0;

		$layout = 'profileUser';
		if ( in_array($user->role, array( User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR )) ) {

			//Если профиль просматривает не владелец
			//то наращиваем счетчик просмотров
			if ($user->id !== Yii::app()->user->id)
			{
				StatSpecialist::hit($user->id,StatSpecialist::TYPE_HIT_PROFILE);
			}

			$this->menuActiveKey = 'designer';
			$layout = 'profileSpecialist';
		}
		$this->render(
			'//member/profile/activity',
			array(
				'user'         => $user,
				'dataProvider' => $dataProvider,
				'view'         => $view,
				'statistic'    => $statistic,
			),
			false,
			array($layout, array(
				'user' => $user,
			))
		);
	}
	
        /**
         * Метод выводит страницу списка профилей в социальных сетях
         *
         * @author Sergey Seregin
         */
        public function actionSocial()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile', 'user-edit');

                $user = Yii::app()->user->model;

                $social = UserSocial::model()->findAllByAttributes(array('user_id' => Yii::app()->user->id));

                if(in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR, User::ROLE_POWERADMIN))){
                        $this->menuActiveKey = 'designer';
                        return $this->render('//member/profile/specialist/social', array(
                                'user' => $user,
                                'social' => $social
                        ));
                }

                $this->render('//member/profile/user/social', array(
                        'user' => $user,
                        'social' => $social
                ));
        }

        /**
         * Ajax Метод по добавлению ссылки на профиль с других сайтов
         */
        public function actionAdd_other_social()
        {
                $success = false;
                $html = '';

                $count = UserSocial::model()->countByAttributes(array('user_id' => Yii::app()->user->id));

                if ($count >= 20) {
                        $success = false;
                } else {
                        $social = new UserSocial();
                        $social->user_id = Yii::app()->user->id;

                        if ($social->save()) {
                                $html = '
					<p>
						<label>Ссылка на профиль</label><br>
						<input type="text" class="textInput social-url" data-id="' . $social->id . '">
						<a class="remove delete_other_social" href="#" data-id="' . $social->id . '">Удалить</a>
					</p>
				';
                                $success = true;
                        }
                }


                die(CJSON::encode(array(
                        'success' => $success,
                        'html' => $html,
                )));
        }

        /**
         * Ajax Метод по добавлению ссылки на профиль с других сайтов
         */
        public function actionDelete_other_social($id = null)
        {
                $success = false;

                $model = UserSocial::model()->findByPk($id, 'user_id = :user_id', array('user_id' => Yii::app()->user->id));
                if ($model) {
                        $model->delete();
                        $success = true;
                }


                die(CJSON::encode(array(
                        'success' => $success,
                )));
        }

        /**
         * Метод обновляет ссылку на профиль.
         * Обновить данные может только владелец.
         * @param integer $id
         */
        public function actionSocial_update($id = null)
        {
                $success = false;
                $message = '';

                $model = UserSocial::model()->findByPk($id, 'user_id = :user_id', array('user_id' => Yii::app()->user->id));
                if ($model) {
                        $model->setScenario('update');

                        $model->url = Yii::app()->request->getParam('url');

                        if ($model->validate()) {
                                $model->save();
                                $success = true;
                                $message = 'Изменения сохранены';
                        } else {
                                $message = 'Введенная ссылка не является правильным URL';
                        }
                } else {
                        $message = 'Ошибка обновления записи';
                }

                die(CJSON::encode(array(
                        'success' => $success,
                        'message' => $message,
                )));
        }

        /**
         * Страница редактирования персональных данных Пользователя (роль User)
         */
        private function _settingsUser()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile user-edit');

                $user = Yii::app()->user->model;
                $user->setScenario('user-profile-edit');

                $userRequest = Yii::app()->request->getParam('User');
                $dataRequest = Yii::app()->request->getParam('UserData');
                if (!empty($userRequest)) {

			$user->attributes = $userRequest;
			$user->data->attributes = $dataRequest;
			
			$user->setImageType('user');
			$avatar = UploadedFile::loadImage($user, 'image_file');
			$userValid = $user->validate(null, false);
			$dataValid = $user->data->validate();

			if ( $userValid && $dataValid ) {
				if ($avatar)
					$user->image_id = $avatar->id;
				
                                $user->save(false);
                                $user->data->save(false);

                                Yii::app()->user->setFlash('msg_success', 'Изменения сохранены');
                        }
                }
		
                $this->render('//member/profile/user/settings', array(
                        'user' => $user
                ));
        }

        /**
         * Страница редактирования персональных данных Дизайнера,
         * как физическое лицо (роль Designer)
         */
        private function _settingsSpecialist()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile', 'user-edit');


                $this->menuActiveKey = 'designer';

                $user = Yii::app()->user->model;

                if ($user->role == User::ROLE_SPEC_JUR)
                        $user->setScenario('corporatedesigner-profile-edit');
                else
                        $user->setScenario('designer-profile-edit');


                $userRequest = Yii::app()->request->getParam('User');
                $dataRequest = Yii::app()->request->getParam('UserData');
                if (!empty($userRequest) || !empty($dataRequest)) {

                        $user->attributes = $userRequest;
                        $user->data->attributes = $dataRequest;

			$p = new CHtmlPurifier();
			$p->options = array(
				'HTML.AllowedElements' => array('a' => true, 'br' => true, 'p' => true),
				'HTML.AllowedAttributes' => array('a.href' => true, 'a.title' => true, 'a.target' => true),
				'URI.AllowedSchemes' => array('http' => true),
			);
			$user->data->about = $p->purify($user->data->about);

			$user->setImageType('user');
			$avatar = UploadedFile::loadImage($user, 'image_file');
			$userValid = $user->validate(null, false);
			$dataValid = $user->data->validate();

                        if ( $userValid && $dataValid ) {
				if ($avatar)
					$user->image_id = $avatar->id;

                                $user->save(false);
                                $user->data->save(false);

                                Yii::app()->user->setFlash('msg_success', 'Изменения сохранены');
                        }

			if ($user->data->hasErrors())
				$user->addErrors($user->data->getErrors());

                }

                $this->render('//member/profile/specialist/settings', array(
                        'user' => $user,
                ));
        }

        /**
         * Страница смены пароля. Для всех ролей одинаковая.
         */
        public function actionPassword()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile', 'user-edit');

                $user = Yii::app()->user->model;

                $user->setScenario('password-edit');

                $isSaved = false;
                $userRequest = Yii::app()->request->getParam('User');
                if (!empty($userRequest)) {
                        // Получаем текущий пароль пользователя
                        $curPassword = $user->password;

                        $user->attributes = $_POST['User'];

                        if ($curPassword != md5($user->old_password) && !empty($user->old_password))
                                $user->addError('old_password', 'Старый пароль введен ошибочно');


                        if ($user->validate(null, false)) {
				$unsafe_pass = $user->password;
                                $user->password = md5($user->password);

                                $user->save(false);
                                $isSaved = true;

                                Yii::app()->mail->create('paswordRestored')
                                        ->to($user->email)
                                        ->params(array(
                                                'username' 	=> $user->name,
                                                'user_login' 	=> $user->login,
                                                'user_pass' 	=> $unsafe_pass,
                                                'sign_A' 	=> Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
                                        ))
                                        ->send();
                        }
                }

                $user->password =  null;

                if ($isSaved)
                        $user->old_password = null;


                if(in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR))){
                        $this->menuActiveKey = 'designer';
                        return $this->render('//member/profile/specialist/password', array(
                                'user' => $user,
                                'isSaved' => $isSaved,
                        ));
                }

		$this->render('//member/profile/user/password', array(
                        'user' => $user,
                        'isSaved' => $isSaved,
                ));
        }

        /**
         * Просмотр и редактирование услуг специалиста.
         * @throws CHttpException
         */
        public function actionServices()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

                $this->menuActiveKey = 'designer';

		$user = Cache::getInstance()->user;
		if (! $user instanceof User )
			throw new CHttpException(404);

                /** Вывод списка услуг пользователя при просмотре чужого профиля */
                if ($user->id != Yii::app()->user->id) {
			//Наращиваем счетчик просмотров
			StatSpecialist::hit($user->id,StatSpecialist::TYPE_HIT_PROFILE);
                        return $this->render(
				'//member/profile/specialist/services',
				array(
                                	'user' => $user,
                        	),
				false,
				array('profileSpecialist', array('user' => $user))
			);
                }
		
		$hasErrors = false;
		$requestData = array();
		$errorCode = array();

                /** Редактирование списка услуг пользователя (при просмотре своего профиля) */
                if (Yii::app()->user->id === $user->id && isset($_POST['User'])) {

                        $oldUserServices = array();
			
                        /** Список выбранных пользователем услуг для сохранения */
                        $newUserServices = empty($_POST['User']['services']) ? null : $_POST['User']['services'];

                        /** Список сохраненных ранее услуг */
                        $tmpArray = Yii::app()->db->createCommand("SELECT * FROM user_service WHERE user_id = '{$user->id}'")->queryAll();

                        /** Замена ключей массива сохраненных ранее услуг на service_id */
                        foreach ($tmpArray as $value) {
                                $oldUserServices[$value['service_id']] = $value;
                        }

                        /** Формирование данных на сохранение в базу с переносом старых значений */
                        $sql_values = array();
			$sqlValues2 = array();
                        foreach ($newUserServices as $service_id => $value) {

                                if (!isset($value['id']) || !isset($value['segment'])) // для общих инпутов
                                        continue;

                                $experience = (int)isset($value['experience']) ? $value['experience'] : 0;
                                $service_id  =(int)$service_id;
				$segment = (int)isset($value['segment']) ? $value['segment'] : 0;
				$segment_supp = (int)isset($value['segment_supp']) ? $value['segment_supp'] : 0;
				
				
				$requestData[$service_id]['experience'] = $experience;
				$requestData[$service_id]['segment'] = $segment;
				$requestData[$service_id]['segment_supp'] = $segment_supp;
				// validators
				if ($experience == 0) {
					$hasErrors = true;
					$requestData[$service_id]['errorExp'] = true;
					$errorCode['errorExp'] = 'Не указан стаж';
				}
				if ($segment == 0 ) {
					$hasErrors = true;
					$requestData[$service_id]['errorSegment'] = true;
					$errorCode['errorSegment'] = 'Не указан приоритетный ценовой сегмент';
				}
				if ( ($segment != 0 && $segment_supp == $segment) || ($segment == 1 && $segment_supp == 3) || ($segment == 3 && $segment_supp == 1) ) {
					$hasErrors = true;
					$requestData[$service_id]['errorSegmentSupp'] = true;
				}
                                /** Формирование массива данных для сохранение */
                                $sql_values[] = "( '{$user->id}', '{$service_id}', '{$experience}', '{$segment}', '{$segment_supp}')";
				$sqlValues2[] = "( '{$user->id}', '{$service_id}', 0, 0)";
                        }
			
			if (!$hasErrors) {

				$transaction = Yii::app()->db->beginTransaction();
				try
				{
					/** Удаление всхех услуг пользователя */
					Yii::app()->db->createCommand()->delete('user_service', 'user_id = :uid', array(':uid' => $user->id));

					/** Вставка новых пользовательских услуг (в случае, если пользователь их выбрал) */
					if (!empty($sql_values)) {
						Yii::app()->db->createCommand('insert into user_service (`user_id`, `service_id`, `experience`, `segment`, `segment_supp`) values ' . implode(',', $sql_values))->execute();

						$sql = 'insert ignore into user_service_data (`user_id`, `service_id`, `rating`, `project_qt`) values ' . implode(',', $sqlValues2);
						Yii::app()->db->createCommand($sql)->execute();
					}

					/** Обновление рейтинга */
					Yii::app()->gearman->appendJob('userService', array('userId'=>$user->id));

					$transaction->commit();
				} catch (Exception $e) {

					$transaction->rollback();
					throw new CHttpException(500);
				}
			}


                }
		
		$checkedServices = array();
		$uploadErrors = array();
		if ($hasErrors) {
			$checkedServices = $requestData;
		} else {
			// Пытаемся сохранить загруженный файл, получая ошибки
			$uploadErrors = ( ! empty($_FILES)) ? self::savePricelist() : array();

			/** Составление массива предоставляемых пользователем услуг (для отметки checkbox'ами) */
			$tmpArr = Yii::app()->db->createCommand("SELECT * FROM user_service WHERE user_id = '{$user->id}'")->queryAll();

			foreach ($tmpArr as $data) {
				$checkedServices[$data['service_id']] = array('experience'=>$data['experience'], 
										'segment'=>$data['segment'],
										'segment_supp'=>$data['segment_supp'],
									);
			}
		}

                $locations = UserServicecity::model()->findAllByAttributes(array('user_id'=>$user->id));
		/** Список всех услуг */
		$services = Service::model()->findAll(array('order' => 'case when parent_id = 0 then id else parent_id end,parent_id', 'limit' => 200));


		return $this->render(
			'//member/profile/specialist/services_own',
			array(
				'user' 		  => $user,
				'services' 	  => $services,
				'checkedServices' => $checkedServices,
				'locations' 	  => $locations,
				'uploadErrors' 	  => $uploadErrors,
				'errorCode' 	  => $errorCode,
			),
			false,
			array('profileSpecialist', array('user' => $user))
		);
        }

	/**
	 * Сохраняет переданный из формы файл прайс листа,
	 * заносит запись в uploadedFile из заности ID последнего в
	 * свойство UserData->price_list
	 */
	private function savePricelist()
	{
		$model = Yii::app()->user->model->data;
		$model->setScenario('savePriceList');

		$model->price_list = CUploadedFile::getInstance($model, 'price_list');


		if ($model->validate()) {
			/**
			 * Формируем путь для сохранения прайс-листа
			 */
			$nameFile = time() . '_' . Amputate::rus2translit($model->price_list->getName());
			$pathFile = Config::UPLOAD_PATH_PRICE_LIST.'/'.(Yii::app()->user->id % 30000);
			if ( ! file_exists($pathFile))
				mkdir ($pathFile, 0700, true);

			/**
			 * Сохраняем файл на диске и записиваем значение
			 */
			if ($model->price_list->saveAs($pathFile . '/' . $nameFile)) {

				$uf = new UploadedFile();
				$uf->author_id = Yii::app()->user->id;
				$uf->path = $pathFile;
				$uf->name = Amputate::getFilenameWithoutExt($nameFile);
				$uf->ext = $model->price_list->getExtensionName();
				$uf->size = $model->price_list->getSize();
				$uf->type = UploadedFile::FILE_TYPE;
				if ($uf->save()) {
					$priceListId = $uf->id;
				} else {
					$priceListId = 0;
				}

				$model->setScenario('saveIdPrice');
				$model->price_list = $uf->id;
				if ( ! $model->save())
					return $model->getErrors();

				return $uf->getErrors();

			} else {
				return $model->getErrors();
			}
		} else {
			$uploadFileErrors = $model->getErrors();

			$model->setScenario('saveIdPrice');
			$model->price_list = 0;
			$model->save();

			return $uploadFileErrors;
		}
	}

	/**
	 * Метод для удаления своих собственных прайс листов.
	 * Прайс лист физически не удаляется, а только удаляется
	 * ID uploadedFile'а из userData->price_list
	 * @param null $id
	 * @throws CHttpException
	 */
	public function actionDeleteprice($id = null)
	{
		if ( ! Yii::app()->request->getIsAjaxRequest())
			throw new CHttpException(403);

		if (Yii::app()->user->model->data->price_list != $id)
			die(CJSON::encode(array(
				'success' => false
			)));

		$id = (int)$id;

		$model = Yii::app()->user->model->data;
		$model->setScenario('saveIdPrice');
		$model->price_list = 0;
		if ($model->save()) {
			die(CJSON::encode(array(
				'success' => true
			)));
		} else {
			die(CJSON::encode(array(
				'success' => false
			)));
		}

	}

        /**
         * Ajax загрузка регионов и городов на основе переданного родителя
         * @param string $for (country/region)
         * @param integer $id (country_id/region_id)
         */
        public function actionLoadchildcitys($for = 'country', $id = null)
        {

                if (!Yii::app()->request->isAjaxRequest || empty($for) || empty($id))
                        throw new CHttpException(404);


                /**
                 * Подгрузка требуемых объектов регионов или городов
                 */
                switch ($for) {
                        case 'country' :
                                $objects = Region::model()->findAllByAttributes(array('country_id' => (int)$id));
                                break;
                        case 'region' :
                                $objects = City::model()->findAllByAttributes(array('region_id' => (int)$id));
                                break;
                        default :
                                $objects = array();
                                break;
                }


                /**
                 * Формирование html-списка для ответа по ajax
                 */
                echo CHtml::openTag('ul');
		if ($for == 'region' && !empty($objects)) { // Пункт "все города региона"
			echo CHtml::tag(
				'li',
				array('data-location-id'=> intval($id), 'class'=>'select_region'),
				'Все города региона',
				true
			);
		}

		foreach ($objects as $object) {
			echo CHtml::tag(
				'li',
				array('data-location-id' => $object->id),
				$object->name,
				true
			);
		}

                echo CHtml::closeTag('ul');

                die();
        }

        /**
         * Добавление/удаление гордов пользователя, в которых он предоставляет услуги
         * @param $action string (insert/delete)
         * @param $type string (city, region, country)
         * @param $id integer
         */
        public function actionUpdatecity($action, $type, $id) {

                if (!Yii::app()->request->isAjaxRequest || empty($action) || empty($type) || empty($id))
                        throw new CHttpException(404);

		$id = intval($id);
                /**
                 * Определение на основе переданного типа объекта соответствующих модели и атрибута для дальнейшей работы
                 */
		if ($action == 'delete' && $type == 'region') { // Фикс для удаления
			$attr = 'region_id';
			$model = 'Region';
		} else {
		
			switch ($type) {
				case 'country' :
					$attr = 'country_id';
					$model = 'Country';
					throw new CHttpException(404); // нельзя выбрать страну
					break;
				case 'region' :
					$attr = 'region_id';
					$model = 'Region';
					throw new CHttpException(404); // нельзя выбрать регион
					break;
				case 'city' :
					$attr = 'city_id';
					$model = 'City';
					break;
				case 'all_cities':
					$attr = 'region_id';
					$model = 'Region';
					break;
				default :
					throw new CHttpException(404);
			}
		}

		/**
                 * Проверка наличия объекта города/региона/области к которому применяется операция вставки или удаления
                 */
                $attrExists = $model::model()->exists($id);

                /**
                 * Вставка связки город-пользователь
                 */
		if ($action == 'insert' && $attrExists) {

			UserServicecity::model()->deleteAllByAttributes(
				array('user_id' => Yii::app()->user->id, $attr => $id)
			);
			$serv_city = new UserServicecity();
			$serv_city->user_id = Yii::app()->user->id;
			$serv_city->$attr = (int)$id;
			// только при выборе города
			$modelObj = $model::model()->findByPk((int)$id);
			if (is_null($modelObj)) {
				exit('error');
			}

			if ($modelObj instanceof City) {

				UserServicecity::model()->deleteAllByAttributes(array(
					'user_id'   => $serv_city->user_id,
					'region_id' => $modelObj->region_id,
					'city_id'   => null
				));
				$serv_city->region_id = $modelObj->region_id;
				$serv_city->country_id = $modelObj->country_id;

			} elseif ($modelObj instanceof Region) {

				$serv_city->region_id = $id;
				$serv_city->country_id = $modelObj->country_id;
			}

			$serv_city->save(false);

			$locations = UserServicecity::model()->findAllByAttributes(
				array('user_id' => Yii::app()->user->id)
			);
			$this->updateServiceCity($locations);

			$list = $this->renderPartial(
				'//member/profile/specialist/_ownCityList',
				array('locations' => $locations),
				true
			);

			exit(CJSON::encode(array(
				'success' => true,
				'list'    => $list
			)));
		}


                /**
                 * Удаление связки город-пользователь
                 */
                if($action == 'delete') {
			$serv_city = UserServicecity::model()->findByAttributes(array('user_id'=>Yii::app()->user->id, $attr => $id));
			if ($serv_city) {
				// т.к. нет PRIMARY KEY
				Yii::app()->db->createCommand()->delete($serv_city->tableName(), 'user_id=:uid AND ' . $attr . '=:id', array(':uid'=>Yii::app()->user->id, ':id'=>(int)$id));

				$locations = UserServicecity::model()->findAllByAttributes(array('user_id'=>Yii::app()->user->id));
				$this->updateServiceCity($locations);

                        	die( CJSON::encode(array('success'=>true)) );
			}
                }

                /**
                 * Если дошло до этого момента, то значит что-то было не корректно
                 */
                die( CJSON::encode(array('error'=>true)) );
        }

	/**
	 * Обновление списка городов, в которых оказываются услуги (исключая текущий)
	 */
	private function updateServiceCity($locations)
	{
		$user = Yii::app()->user->model;
		$cityList = '';
		$cnt = 0;
		foreach ($locations as $location) {
			if (!is_null($user->city_id) && $location->city_id == $user->city_id)
				continue;
			if ($cnt > 0)
				$cityList .= ', ';
			$cityList .= $location->getLocationLabel();
			$cnt++;
		}
		Yii::app()->db->createCommand()->update('user_data', array('service_city_list'=>$cityList), 'user_id=:uid', array(':uid'=>Yii::app()->user->id));
	}

        /**
         * Просмотр портфолио пользователя
         * @param integer $service
         */
        public function actionPortfolio($service = null)
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

                $this->menuActiveKey = 'designer';

		/** @var $user User */
		$user = Cache::getInstance()->user;
		
		if ( !$user instanceof User || !in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR)) ) {
			throw new CHttpException(404);
		}

                /**
                 * Для владельца портфолио отображается весь список его услуг.
                 * Для стороннего пользователя - только услуги, в которых есть проекты.
                 */
                if($user->id == Yii::app()->user->id) {
			$serviceList = $user->getServiceList();
			$owner = true;
		} else {
			$serviceList = $user->getUsedServiceList();
			$owner = false;
		}

		//Если профиль открыл не владелец
		//профиля то наращиваем просмотр
		if(!$owner)
		{
			StatSpecialist::hit($user->id,StatSpecialist::TYPE_HIT_PROFILE);
		}


                /**
                 * Если услуга не указана, открывается портфолио для первой пользовательской услуги
                 * Для этого выполняем третье условие функции
                 */
                if(is_null($service) && isset($serviceList[0])) {
                        $service = $serviceList[0]['service_id'];
                }

                /**
                 * Если услуга не указана и у пользователя нет услуг, открывается пустой раздел портфолио
                 */
                if(is_null($service) && !isset($serviceList[0])) {
                        $projects = array();
                        $service_id = 0;
                }

                /**
                 * Если услуга указана и существует, открывается страница портфолио для этой услуги
                 */
                if(!is_null($service)) {
                        $service = Service::model()->findByPk((int)$service);

                        if(!$service)
                                throw new CHttpException(404);

                        $service_id = $service->id;
                        $class = Config::$projectTypes[$service->type];

			if (is_array($class))
			{
				$criteria = new CDbCriteria();
				$criteria->join = 'INNER JOIN portfolio_sort as s ON s.item_id=t.id';
				$criteria->condition = 's.service_id=:sid AND s.idea_type_id=:tid AND s.user_id=:uid';
				$criteria->select = 't.*, s.position';

				$projects = array();
				foreach ($class as $typeKey=>$c)
				{
					$criteria->params = array(':sid'=>$service_id, ':tid'=>$typeKey, ':uid'=>$user->id);
					$tmp = $c::model()->scopeOwnPublic($user->id)->findAll($criteria);
					if ($tmp) {
						foreach ($tmp as $item) {
							$projects[$item->position] = $item;
						}
					}
				}
				ksort($projects);
			}
			else
			{
				$criteria = new CDbCriteria();
				$criteria->join = 'INNER JOIN portfolio_sort as s ON s.item_id=t.id';
				$criteria->condition = 's.service_id=:sid AND s.idea_type_id=:tid AND s.user_id=:uid';
				$criteria->params = array(':sid'=>$service_id, ':tid'=>$service->type, ':uid'=>$user->id);
				$criteria->order = 's.position ASC, t.id DESC';
				$projects = $class::model()->scopeOwnPublic($user->id)->findAll($criteria);
			}

                }

                return $this->render(
			'//member/profile/specialist/portfolio',
			array(
                        	'user' => $user,
                        	'projects'=>$projects, // список проектов по выбранной услуге
                        	'currentServiceId'=>$service_id, // id услуги, по которой просматривается портфолио
                        	'currentService'=>Service::model()->findByPk($service_id),
				'owner' => $owner,
                	),
			false,
			array('profileSpecialist',array('user' => $user))
		);

        }

        /**
         * Черновики проектов пользователя
         */
        public function actionDraft()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

                $this->menuActiveKey = 'designer';

		$user = Cache::getInstance()->user;
		if (! $user instanceof User)
			throw new CHttpException(404);

                $modelsForSelect = array();
                foreach($user->serviceList as $service) {
                       $modelsForSelect[$service['service_type']] = Config::$projectTypes[$service['service_type']];
                }

                $projects=array();
                foreach($modelsForSelect as $modelname) {
			if (is_array($modelname))
			{
				foreach ($modelname as $name) {
					$tmp = $name::model()->findAllByAttributes(array('author_id'=>$user->id, 'status'=>Portfolio::STATUS_MAKING), array('order'=>'create_time DESC'));
					if ($tmp)
						$projects = array_merge($projects, $tmp);
				}
			}
			else
			{
                        	$projects = array_merge($projects, $modelname::model()->findAllByAttributes(array('author_id'=>$user->id, 'status'=>Portfolio::STATUS_MAKING), array('order'=>'create_time DESC')));
			}
                }

                /**
                 * Обновление кол-ва проектов в черновиках
                 */
                $user->data->draft_qt = count($projects);
                $user->data->save();

                return $this->render(
			'//member/profile/specialist/draft',
			array(
				'user' => $user,
				'projects' => $projects,
			),
			false,
			array('profileSpecialist', array('user' => $user))
		);
        }

        /**
         * Просмотр проекта
         * @param integer $service
         * @param integer $id
	 * @param integer $t Это инденификатор нужный для разделения двух разных типов интерьеров:
	 * 			Интерьеры и Общесвтенные интреьеры.
         * @throws CHttpException
         */
        public function actionProject($service = null, $id = null, $t = null)
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');


		$this->menuActiveKey = 'designer';

		$user = Cache::getInstance()->user;

                if(!$service || !$id || is_null($user))
                        throw new CHttpException(404);

                $service = Service::model()->findByPk((int)$service);
                if(!$service)
                        throw new CHttpException(404);

                $class = Config::$projectTypes[$service->type];

		if (is_array($class)) {
			if ($t == 1)
				$class = $class[Config::INTERIOR]; // Interior
			elseif ($t == 2)
				$class = $class[Config::INTERIOR_PUBLIC]; // Interiorpublic
			else
				throw new CHttpException(404);
		}

		$project = $class::model()->findByPk((int)$id);

                if (!$project || $project->status == $class::STATUS_DELETED) {
                        throw new CHttpException(404);
		}

		//Если профиль просматривает не владелец
		//то наращиваем счетчик просмотров
		if ($user->id !== Yii::app()->user->id)
		{
			StatSpecialist::hit($user->id,StatSpecialist::TYPE_HIT_PROFILE);
			StatProject::hit($project->id, get_class($project), $user->id, StatProject::TYPE_PROJECT_VIEW );
		}


                if($service->type == Config::INTERIOR) {

                        // Депонирование
                        Yii::import('application.modules.idea.controllers.CopyrightfileController');
                        $historyDepositionHtml = CopyrightfileController::getHistoryHtml($project->id);


                        // Массив, который содержит список всех цветов, использованный
                        // в идее. Массив array( 'hex' => 'имя', ... )
                        $all_colors = array();


                        // Массив стилей текущей идеи
                        $all_styles = array();

			if ($t == 1)
			{
				// Список доступных цветов
				$colors = IdeaHeap::getColors(Config::INTERIOR);
				$arr_colors_name = CHtml::listData($colors, 'id', 'option_value');
				$arr_colors_hex = CHtml::listData($colors, 'id', 'param');
				$arr_colors_eng_name = CHtml::listData($colors, 'id', 'eng_name');

				// Список доступных стилей
				$arr_styles = IdeaHeap::getStyles(Config::INTERIOR);
				$arr_styles = CHtml::listData($arr_styles, 'id', 'option_value');

				// Массив помещений
				$icRooms = unserialize($project->rooms_list);

				if ($icRooms) {
					// Дополнительные цвета наших помещений
					$additionalColors = IdeaAdditionalColor::model()->findAll(
						'item_id in (' . implode(',', array_keys($icRooms)) . ') AND color_id IS NOT NULL AND idea_type_id=:typeId', array(':typeId' => Config::INTERIOR)
					);

					if ($additionalColors) {
						foreach ($additionalColors as $color) {
							$all_colors[$arr_colors_hex[$color->color_id]]['name'] = $arr_colors_name[$color->color_id];
							$all_colors[$arr_colors_hex[$color->color_id]]['eng_name'] = $arr_colors_eng_name[$color->color_id];
						}
					}

					// Получаем главные цвета помещений
					$contents = InteriorContent::model()->findAllByAttributes(array('id' => array_keys($icRooms)));

					if ($contents) {
						foreach ($contents as $color) {
							if (!is_null($color->color_id)) {
								$all_colors[$arr_colors_hex[$color->color_id]]['name'] = $arr_colors_name[$color->color_id];
								$all_colors[$arr_colors_hex[$color->color_id]]['eng_name'] = $arr_colors_eng_name[$color->color_id];
							}
							if (!is_null($color->style_id)) {
								$all_styles[ $color->style_id ] = $arr_styles[$color->style_id];
							}
						}
					}
				}
			} elseif ($t == 2) {
				// Список доступных цветов
				$colors = IdeaHeap::getColors(Config::INTERIOR_PUBLIC);
				$arr_colors_name = CHtml::listData($colors, 'id', 'option_value');
				$arr_colors_hex = CHtml::listData($colors, 'id', 'param');
				$arr_colors_eng_name = CHtml::listData($colors, 'id', 'eng_name');

				// Список доступных стилей
				$arr_styles = IdeaHeap::getStyles(Config::INTERIOR_PUBLIC);
				$arr_styles = CHtml::listData($arr_styles, 'id', 'option_value');

				$all_styles[ $project->style_id ] = $arr_styles[ $project->style_id ];

				// Дополнительные цвета наших помещений
				$additionalColors = IdeaAdditionalColor::model()->findAll(
					'item_id = :itemId AND color_id IS NOT NULL AND idea_type_id=:typeId', array(':itemId' => $project->id, ':typeId' => Config::INTERIOR_PUBLIC)
				);

				if ($additionalColors) {
					foreach ($additionalColors as $color) {
						$all_colors[$arr_colors_hex[$color->color_id]]['name'] = $arr_colors_name[$color->color_id];
						$all_colors[$arr_colors_hex[$color->color_id]]['eng_name'] = $arr_colors_eng_name[$color->color_id];
					}
				}

				$all_colors[$arr_colors_hex[$project->color_id]]['name'] = $arr_colors_name[$project->color_id];
				$all_colors[$arr_colors_hex[$project->color_id]]['eng_name'] = $arr_colors_eng_name[$project->color_id];
			}

                }

                // Фотографии для плеера
		// TODO: убрать костыли после перевода на компонент картинок
		if ($project instanceof Architecture) {
			$arrImages = $project->getPhotoList();
		} else {
			/** @var $project IProject */
			$arrImages = $project->getPhotos();
		}



		// Фотки планировок
		if ($service->type == Config::INTERIOR && $t == 1) {
			$layouts = $project->getLayouts();
			$arrImages = array_merge($arrImages, $layouts);
		}

                // Получаем следующую работу
                $next_work = $class::model()->scopeOwnPublic($user->id)->find(new CDbCriteria(array(
			'condition' => 'create_time > ' . $project->create_time,
			'order'     => 'create_time ASC',
			'limit'     => 1,
			'offset'    => 0,
                )));
                // Получаем предыдущую работу
                $prev_work = $class::model()->scopeOwnPublic($user->id)->find(new CDbCriteria(array(
			'condition' => 'create_time < ' . $project->create_time,
			'order'     => 'create_time DESC',
			'limit'     => 1,
			'offset'    => 0,
                )));

		if (get_class($project) == 'Interiorpublic')
			$buildType = IdeaHeap::model()->findByPk($project->building_type_id);
		else
			$buildType = new IdeaHeap();

                $this->render(
			'//member/profile/specialist/project',
			array(
				'user'                  => $user,
				'service'               => $service,
				'project'               => $project,
				'historyDepositionHtml' => isset($historyDepositionHtml)
					? $historyDepositionHtml : '',
				'arrImages'             => isset($arrImages)
					? $arrImages : array(),
				'icRooms'               => isset($icRooms)
					? $icRooms : array(),
				'all_colors'            => isset($all_colors)
					? $all_colors : array(),
				'all_styles'            => isset($all_styles)
					? $all_styles : array(),
				'next_work'             => $next_work,
				'prev_work'             => $prev_work,
				'buildType'             => $buildType,
			),
			false,
			array('profileSpecialist', array('user' => $user))
		);
        }

        /**
         * AJAX
         * Удаление пользовательской аватары
         * @throws CHttpException
         */
        public function actionDeleteavatar()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $user = Yii::app()->user->model;
                $user->image_id = null;

                if($user->save(false)) {
                        die(CJSON::encode(array('result'=>true)));
                }
                die(CJSON::encode(array('result'=>false)));
        }

	/**
	 * Перремещение объекта на странице портфолио
	 * @throws CHttpException
	 */
	public function actionMoveItem()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$itemId = intval( $request->getParam('item_id') );
		$serviceId = intval( $request->getParam('service_id') );
		$typeId = intval( $request->getParam('type_id') );
		$position = intval( $request->getParam('position') );

		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		/** @var $item PortfolioSort */
		$item = PortfolioSort::model()->findByPk(array('item_id'=>$itemId, 'idea_type_id'=>$typeId, 'service_id'=>$serviceId));
		if ( $item===null )
			throw new CHttpException(404);
		if ( $item->user_id != Yii::app()->getUser()->getId())
			throw new CHttpException(403);

		$item->position = $position;
		$item->save(false);

		die( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
	}

	/**
	 * AJAX
	 * Скрытие флешки уведомлений
	 * о драге в портфолио
	 * @throws CHttpException
	 */
	public function actionAxHideFlash()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		Yii::app()->getUser()->setDbFlash('hide_portfolio_notice', true);

		die( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
	}


	/**
	 * Статистика кнопки связаться со мной в профиле специалистов
	 * В будущем надо зарефакторить
	 */
	public function actionStatHitAjax()
	{
		$idUser = Yii::app()->request->getPost('userId');
		$idUser = (int)$idUser;
		$user = User::model()->findByPk($idUser);

		if($user)
		{
			StatSpecialist::hit($idUser,StatSpecialist::TYPE_CLICK_CONTACT_ME);
		}
		die( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
	}


	/**
	 * Вывод статистики
	 * В личном кабинете
	 * специалиста
	 * @return string
	 */
	public function actionStatistic()
	{
		$this->bodyClass = 'profile stat';
		$this->menuActiveKey = 'designer';

		$user = Yii::app()->user->getModel();
		$this->setPageTitle('Статистика — '.$user->name.'— MyHome.ru');

		$timeFrom = time()-(30*(24*60*60));
		$timeTo = time();
		$city = false;
		$filter = false;

		$id = $user->id;


		//Переливание статистики из Reddis в mysql при открытии статистики магазина
		StatSpecialist::updateStatSpecialistMySql('STAT:SPECIALIST:' . $id . ':*');
		StatProject::updateStatProjectMySql('STAT:PROJECT:' . $id . ':*');
		StatUserService::updateStatUserServMySql('STAT:USER:' . $id . ':*');

		if (!empty($_GET['timeFrom']) || !empty($_GET['timeTo'])) {
			$timeFrom = $_GET['timeFrom'];
			$timeTo = $_GET['timeTo'];
		}

		if (isset($_GET['city']) && $_GET['city'] !== 'null') {
			$city = (int)$_GET['city'];
		}

		if (isset($_GET['filter']) && $_GET['filter'] !== 'null') {
			$filter = $_GET['filter'];
		}


		//Формируем массив городов по которым
		//Возможна сортировка
		$listCity = array();
		$listCity = StatUserService::model()->getListCity($user->id);

		$statSpecialistModel = StatSpecialist::model()->getStat($id, $timeFrom, $timeTo);
		$statProjectModel = StatProject::model()->getStat($id, $timeFrom, $timeTo);
		$statProjectModelDp = StatProject::model()->getStatTable($id, $timeFrom, $timeTo, true);
		$statUserService = StatUserService::model()->getStatTable($id, $timeFrom, $timeTo, $city);

		if (Yii::app()->request->getIsAjaxRequest()) {
			if($filter == 'time') {
				$html = $this->renderPartial('//member/profile/specialist/_statisticItem', array
				(
					'user'                => $user,
					'statSpecialistModel' => $statSpecialistModel,
					'statProjectModel'    => $statProjectModel,
					'statProjectModelDp'  => $statProjectModelDp,
					'timeFrom'            => $timeFrom,
					'timeTo'              => $timeTo,
					'statUserService'     => $statUserService,
					'city'                => $city,
					'listCity'	      => $listCity,
				), true);
			}
			else {
				$html = $this->renderPartial('//member/profile/specialist/_statUserServiceItem', array('statUserService' => $statUserService), true);
			}

			die(json_encode(array(
				'success'  => true,
				'html' 	   => $html
			), JSON_NUMERIC_CHECK));
		}



		return $this->render(
			'//member/profile/specialist/statistic',
			array(
				'user'                => $user,
				'statSpecialistModel' => $statSpecialistModel,
				'statProjectModel'    => $statProjectModel,
				'statProjectModelDp'  => $statProjectModelDp,
				'timeFrom'            => $timeFrom,
				'timeTo'              => $timeTo,
				'statUserService'     => $statUserService,
				'city'                => $city,
				'listCity'	      => $listCity,

			),
			false,
			array(
				'profileSpecialist', array(
				'user' => $user,
			)
			)
		);
	}
}