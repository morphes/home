<?php

/**
 * @brief Управление пользователями и администраторами сайта
 * @author Roman Kuzakov <roman.kuzakov@gmail.com>
 */
class UserController extends AdminController
{

        public $layout = 'webroot.themes.myhome.views.layouts.backend';

        public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {
		return array(
			array(
				'allow',
				'actions' => array('AutocompletePhone'),
				'roles'   => array(
					User::ROLE_ADMIN,
					User::ROLE_JUNIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_SENIORMODERATOR,
				),
			),
			array('allow',
				'actions' => array('view', 'userlist', 'create_message_ajax', 'invite'),
				'roles'   => array(
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_SENIORMODERATOR,
				),
			),
			array('allow',
				'actions' => array('adminlist'),
				'roles'   => array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN
				),
			),
			array('allow',
				'actions' => array('view', 'create', 'update', 'delete', 'updateavatar', 'group_action', 'ajax_status_update', 'ajax_status_list', 'resendcode', 'uploadimage', 'removeimage'),
				'roles'   => array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_MODERATOR,
				),
			),
			array('allow',
				'actions' => array('update', 'uploadimage', 'removeimage'),
				'roles'   => array(User::ROLE_SALEMANAGER, User::ROLE_MODERATOR, User::ROLE_POWERADMIN),
			),
			array('allow',
				'actions' => array('activatelog'),
				'roles'   => array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
				),
			),
			array('allow',
				'actions' => array('ajaxMakeAdminStore'),
				'roles'   => array(
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_STORES_ADMIN,
				),
			),
			array('deny',
				'users' => array('*'),
			),
		);
        }

        public function beforeAction($action)
        {
                Yii::import('application.modules.idea.models.Interior');
                return true;
        }

        /**
         * @brief Отображает список пользователей с фильтром
         */
        public function actionUserlist($reg_from=null, $reg_to=null, $promocode_id=null)
        {
                // Отключаем правую панель
                $this->rightbar = null;

                if (Yii::app()->request->isAjaxRequest)
                        $this->layout = false;


		/**
		 * Условие исполнения функционала по сменен даты регистрации
		 */
		$reg_from_2 = Yii::app()->request->getParam('reg_from_2');
		$reg_to_2 = Yii::app()->request->getParam('reg_to_2');
		$reg_new = Yii::app()->request->getParam('reg_new');

		// Кол-во элементов для смены регистрации
		$qtForChange = 0;
		if (Yii::app()->request->getParam('action') == 'change-reg') {

			$condition = 'create_time >= :ct1 AND create_time < :ct2 AND (role = :r1 OR role = :r2) AND status = :st AND referrer_id IS NOT NULL';
			$params = array(
				':r1'  => User::ROLE_SPEC_FIS,
				':r2'  => User::ROLE_SPEC_JUR,
				':st'  => User::STATUS_VERIFYING,
				':ct1' => strtotime($reg_from_2),
				':ct2' => strtotime('+1 day', strtotime($reg_to_2))
			);

			$cmd = Yii::app()->db->createCommand();
			$cmd->select('COUNT(*) as cnt');
			$cmd->from(User::model()->tableName());
			$cmd->where($condition, $params);
			$qtForChange = $cmd->queryScalar();


			if (isset($_GET['change_now']) && ! empty($reg_new)) {
				// Обновляем дату регистрации у нужных пользвоателей
				$cmd = Yii::app()->db->createCommand();
				$cmd->update(
					User::model()->tableName(),
					array('create_time' => strtotime($reg_new)),
					$condition,
					$params
				);
			}
		}


                $model = new User('search');
                $model->unsetAttributes();  // clear any default values
                if (isset($_GET['User']))
                        $model->attributes = $_GET['User'];


                /*
                 * Ищем пользователей по логину
                 * Если находим, то формируем массив ID пользователей для выдачи.
                 */
		$sphinxClient = Yii::app()->search;
		$login = $sphinxClient->EscapeString($model->login);

		$userProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'user_login',
				'modelClass' => 'User',
				'query' => $login . '*',
				'matchMode' => 'SPH_MATCH_ANY',
				//'filters' => array('status' => User::STATUS_ACTIVE),
				'pagination' => array('pageSize' => 10),
			));
		$users = $userProvider->getData();

                $user_ids = array();
                foreach ($users as $usr) {
                        $user_ids[] = $usr->id;
                }


                // Создаем критерий поиска по-умолчанию.
                $criteria = new CDbCriteria;
                $criteria->select = 't.*';
                $criteria->join = 'LEFT JOIN user_data ON t.id = user_data.user_id';
                $criteria->condition = "(t.role = '" . implode("' OR t.role = '", array_keys(Config::$rolesUserReg)) . "'"
                        . ') AND t.status <> ' . User::STATUS_DELETED;


                // Поиск по ID
                if ($model->id)
                        $criteria->compare('t.id', explode(',', $model->id));


		// Фильтр по услуге
		if (($service_id = Yii::app()->request->getParam('service_id'))) {
			$criteria->join .= ' LEFT JOIN user_service ON user_service.user_id = t.id';
			if ($service_id == 'none') {
				$criteria->condition .= " AND user_service.service_id IS NULL";
			} else {
				$service_id = intval($service_id);
				$criteria->condition .= " AND user_service.service_id = ".$service_id;
			}

		}
		// Фильтр по городу пользователя
		if ($model->city_id) {
			$criteria->compare('t.city_id', $model->city_id);
		}

		// Фильтр по городу услуги
		if ($model->service_city) {
			$criteria->join .= ' LEFT JOIN user_servicecity ON user_servicecity.user_id = t.id';
			$criteria->condition .= ' AND user_servicecity.city_id = '.intval($model->service_city);
		}

                // Поиск по логину
                if ($model->login && !empty($user_ids)) {
                        $criteria->compare('t.id', $user_ids);
                }

                // Поиск по Email'у
                $criteria->compare('t.email', $model->email, true);

                // Поиск по роли
                if (($search_role = Yii::app()->request->getParam('search_role'))) {

                        if($search_role == 'allSpec') {
                                // поиск по всем специалистам
                                $criteria->condition .= ' AND (t.role = "' .User::ROLE_SPEC_FIS . '" OR t.role = "' .User::ROLE_SPEC_JUR . '")';
                        } else {
                                // поиск по конкретной роли
                                $criteria->compare('t.role', $search_role);
                        }
                }

                // Поиск по статусу
                if ($model->status)
                        $criteria->compare('status', $model->status);

                // Поиск по Referrer (пригласившему на сайт)
                if (($model->referrer = Yii::app()->request->getParam('referrer')) && $model->referrer) {
                        $criteria->compare('referrer_id', $model->referrer->id);
                }

                // Дата начала регитсрации
                if (($reg_from = Yii::app()->request->getParam('reg_from'))) {
                        $criteria->compare('create_time', '>=' . strtotime($reg_from));
		}

                // Дата окончания регистрации
                if (($reg_to = Yii::app()->request->getParam('reg_to'))) {
                        $criteria->compare('create_time', '<' . strtotime('+1 day', strtotime($reg_to)));
		}

                // Поиск по промокоду
                if ($model->promo_code)
                        $criteria->compare('promocode_id', $model->promo_code);

                // Поиск по referrer_id
                if ($model->referrer_id)
                        $criteria->compare('referrer_id', $model->referrer_id);

		if ($model->expert_type) {
			$criteria->compare('expert_type', $model->expert_type);
		}


                // Получаем список промокодов
                $promocodes = CHtml::listData(Promocode::model()->findAll(), 'id', 'name');

                //$criteria->with = 'data';
                // Определяем столбцы для соритровки
                if (!Yii::app()->request->getParam('sort'))
                        $criteria->order = 'id DESC';

                $sort = new CSort('User');
                $sort->attributes = array(
                    'id',
                    'login',
                    'status',
                    'email',
                    'promocode_id',
                    'project_quantity' => array(
                        'asc' => 'user_data.project_quantity ASC',
                        'desc' => 'user_data.project_quantity DESC',
                        'label' => 'Проекты',
                    ),
                    'create_time' => array(
                        'label' => 'Зарег&dash;ан'
                    ),
                    'name' => array(
                        'asc' => 't.firstname ASC, t.lastname ASC',
                        'desc' => 't.firstname DESC, t.lastname DESC',
                    )
                );
                $sort->applyOrder($criteria);

                $dataProvider = new CActiveDataProvider('User', array(
                            'criteria' => $criteria,
                            'sort' => $sort,
                            'pagination' => array(
                                'pageSize' => 20,
                            ),
                        ));

                /**
                 * Определение общего кол-ва проектов, добавленных выбранными пользователями
                 */
                $command = Yii::app()->db->createCommand();
                $command->select('SUM(project_quantity) AS pqt');
                $command->from('user t');
                $command->leftJoin('user_data ud', 'ud.user_id = t.id');

		// Если указан услуга то в подсчет кол-во работ, тоже вносим условие
		if ($service_id) {
                	$command->leftJoin('user_service', 'user_service.user_id = t.id');
		}
		if ($model->service_city) {
			$command->leftJoin('user_servicecity', 'user_servicecity.user_id = t.id');
		}
                $command->where($criteria->condition, $criteria->params);
                $command->limit(1);
                $projects_qt = $command->queryScalar();

                Yii::app()->user->setReturnUrl($this->createUrl($this->action->id));
                $this->render('userlist', array(
			'dataProvider' => $dataProvider,
			'promocodes'   => $promocodes,
			'model'        => $model,
			'search_role'  => $search_role,
			'reg_from'     => $reg_from,
			'reg_to'       => $reg_to,
			'projects_qt'  => $projects_qt,
			'service_id'   => $service_id,
			'reg_from_2'   => $reg_from_2,
			'reg_to_2'     => $reg_to_2,
			'reg_new'      => $reg_new,
			'qtForChange'  => $qtForChange
                ));
        }

        /**
         * @brief Отображает список пользователей с фильтром
         */
        public function actionAdminlist($login=null, $email=null, $group=null, $status=null, $reg_from=null, $reg_to=null)
        {
                // Выключаем правую панель
                $this->rightbar = null;

                if (Yii::app()->request->isAjaxRequest)
                        $this->layout = false;

		$conditionRole = '';
                foreach (Config::$rolesAdmin as $key => $value) {
                        if (!array_key_exists($key, Config::$rolesAdmin))
                                continue;

                        if (empty($conditionRole))
                                $conditionRole .= "t.role = '{$key}'";
                        else
                                $conditionRole .= " OR t.role = '{$key}'";
                }

                $current_group = 0;

		$sphinxClient = Yii::app()->search;
		$login = $sphinxClient->EscapeString($login);

		$userProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'user_login',
				'modelClass' => 'User',
				'query' => $login . '*',
				'matchMode' => 'SPH_MATCH_ANY',
				//'filters' => array('status' => User::STATUS_ACTIVE),
				'pagination' => array('pageSize' => 10),
			));
		$users = $userProvider->getData();

                $user_ids = array();
                foreach ($users as $usr) {
                        $user_ids[] = $usr->id;
                }
                $user_ids = implode(",", $user_ids);


		$condition = '';
                if ($login && !empty($user_ids)) {
                        $login = CHtml::encode($login);
                        $condition.= " AND id IN ({$user_ids})";
                }
                if ($email) {
                        $email = CHtml::encode($email);
                        $condition.= " AND email = '{$email}'";
                }
                if ($group) {
                        $group = CHtml::encode($group);
                        if (array_key_exists($group, Config::$rolesAdmin)) {
                                $conditionRole = "t.role = '{$group}'";
                                $current_group = $group;
                        }
                }
                if ($status) {
                        $status = (int) $status;
                        $condition.= " AND status = '{$status}'";
                }
                if ($reg_from) {
                        $reg_from = strtotime($reg_from);
                        $condition.= " AND create_time >= {$reg_from}";
                }
                if ($reg_to) {
                        $reg_to = strtotime($reg_to);
                        $condition.= " AND create_time <= {$reg_to}";
                }

                $dataProvider = new CActiveDataProvider('User', array(
                            'criteria' => array(
                                'select' => "t.*",
				'condition' => "({$conditionRole}) {$condition} AND status <> " . User::STATUS_DELETED . "",
                                'group' => 'id',
                                'order' => 'id DESC',
                            ),
                            'pagination' => array(
                                'pageSize' => 20,
                            ),
                        ));

                Yii::app()->user->setReturnUrl($this->createUrl($this->action->id));
                $this->render('adminlist', array('dataProvider' => $dataProvider, 'current_group' => $current_group));
        }

        /**
         * @brief Deletes a particular model.
         * If deletion is successful, the browser will be redirected to the 'admin' page.
         * @param integer $id the ID of the model to be deleted
         */
        public function actionDelete($id)
        {
                if (Yii::app()->request->isPostRequest) {
                        // we only allow deletion via POST request
                        $user = User::model()->findByPk((int) $id);


                        if ($user) {

                                // Проверка на возможность редактирования пользователя
                                if (array_key_exists($user->role, Config::$rolesUserReg))
				{
                                        if (!Yii::app()->user->checkAccess(array(
						User::ROLE_ADMIN,
						User::ROLE_JUNIORMODERATOR,
						User::ROLE_MODERATOR,
						User::ROLE_SENIORMODERATOR,
						User::ROLE_POWERADMIN
					)))
                                                Yii::app()->end();
                                }
				elseif (array_key_exists($user->role, Config::$rolesAdmin))
				{
                                        if (!Yii::app()->user->checkAccess(array(
						User::ROLE_ADMIN,
						User::ROLE_POWERADMIN
					)))
                                                Yii::app()->end();
                                }
				else
				{
                                        Yii::app()->end();
                                }

				$previous_status = $user->status;

                                $previousLogin = $user->login;
				$previousEmail = $user->email;

				$user->status = User::STATUS_DELETED;

				// Уведомление пользователя о сменен его статуса (по Email)
				if ($user->status != $previous_status)
					$user->notification($previous_status);

				$user->login = $previousLogin.'_'.time();

				$user->email = time().'_'.$previousEmail;

                                $user->save();

                        }

                        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
                        if (!isset($_GET['ajax']))
                                return $this->redirect(Yii::app()->user->returnUrl);
                }
                else
                        throw new CHttpException(400);
        }

        /**
         * @brief Возвращает выпадающий список для смены статуса пользователя 
         * @param integer $uid user id 
         */
        public function actionAjax_status_list($uid = null)
        {
                $this->layout = false;
                $status_list = '';
                if (Yii::app()->request->isAjaxRequest && $uid) {
                        $user = User::model()->findByPk((int) $uid);
                        if ($user) {
                                foreach (Config::$userStatus as $key => $status) {
                                        if ($user->status == $key) {
                                                $status_list.="<li id='update-status' class='current-status' user-id='{$user->id}' status-id='{$key}'>{$status}</li>";
                                        } else {
                                                $status_list.="<li id='update-status' user-id='{$user->id}' status-id='{$key}'>{$status}</li>";
                                        }
                                }
                        }
                }
                $this->renderText($status_list);
        }

        /**
         * @brief Обрабатывает запрос на смену статуса из контекстного меню
         * @param integer $uid
         * @param integer $status
         * @return JSON 
         */
        public function actionAjax_status_update($uid = null, $status=null)
        {
                $this->layout = false;

                if (Yii::app()->request->isAjaxRequest && $uid && $status) {
                        $user = User::model()->findByPk((int) $uid);
                        if ($user) {

                                // Проверка на возможность редактирования пользователя
                                if (array_key_exists($user->role, Config::$rolesUserReg))
				{
                                        if (!Yii::app()->user->checkAccess(array(
						User::ROLE_ADMIN,
						User::ROLE_JUNIORMODERATOR,
						User::ROLE_MODERATOR,
						User::ROLE_SENIORMODERATOR,
						User::ROLE_POWERADMIN
					)))
                                                Yii::app()->end();
                                }
				elseif (array_key_exists($user->role, Config::$rolesAdmin))
				{
                                        if (!Yii::app()->user->checkAccess(array(
						User::ROLE_ADMIN,
						User::ROLE_POWERADMIN
					)))
                                                Yii::app()->end();
                                }
				else
				{
                                        Yii::app()->end();
                                }

                                $previous_status = $user->status;

                                $user->status = (int) $status;
                                if ($user->status == User::STATUS_BANNED)
                                        $user->setBan(87600);

                                $user->save();

                                // Уведомление пользователя о сменен его статуса (по Email)
                                if ($user->status != $previous_status)
                                        $user->notification($previous_status);
                        }
                        return $this->renderText('ok');
                }
        }

        /**
         * @brief Массовые операции с выбранной группой пользователей
         * @param string $action
         * @param string $users
         * @return render 
         */
        public function actionGroup_action($action = null, $users = null)
        {
                $this->layout = false;

                if (Yii::app()->request->isAjaxRequest && $action && $users) {
                        switch ($action) {
                                case 'disable':
                                        $status = User::STATUS_NOT_ACTIVE;
                                        break;
                                case 'enable':
                                        $status = User::STATUS_ACTIVE;
                                        break;
                                case 'delete':
                                        $status = User::STATUS_DELETED;
                                        break;
                                default:
                                        return $this->render('ok');
                                        break;
                        }

                        $usrs = User::model()->findAll('id IN ( ' . $users . ' )');
                        foreach ($usrs as $user)
			{
                                if (array_key_exists($user->role, Config::$rolesUserReg))
				{
                                        if (Yii::app()->user->checkAccess(array(
						User::ROLE_ADMIN,
						User::ROLE_JUNIORMODERATOR,
						User::ROLE_MODERATOR,
						User::ROLE_SENIORMODERATOR,
						User::ROLE_POWERADMIN
					))) {

                                                $previous_status = $user->status;
                                                $user->status = $status;
                                                $user->save();

                                                // Уведомление пользователя о сменен его статуса (по Email)
                                                if ($user->status != $previous_status)
                                                        $user->notification($previous_status);
                                        }
                                }
				elseif (array_key_exists($user->role, Config::$rolesAdmin))
				{
                                        if (Yii::app()->user->checkAccess(array(
						User::ROLE_ADMIN,
						User::ROLE_POWERADMIN
					))) {
                                                $previous_status = $user->status;
                                                $user->status = $status;
                                                $user->save();

                                                // Уведомление пользователя о сменен его статуса (по Email)
                                                if ($user->status != $previous_status)
                                                        $user->notification($previous_status);
                                        }
                                }
                        }

                        return $this->renderText('ok');
                }
        }

        /**
         * @brief Создание нового пользователя
         */
        public function actionCreate()
        {
                $user = new User();

                if ( ! empty($_POST['User']) && isset($_POST['User']['role']) && array_key_exists($_POST['User']['role'], Config::$rolesUserReg + Config::$rolesAdmin))
		{
                        if (array_key_exists($_POST['User']['role'], Config::$rolesUserReg))
			{

                                if ( ! Yii::app()->user->checkAccess(array(
					User::ROLE_ADMIN,
					User::ROLE_JUNIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN
				)))
                                        throw new CHttpException(403);


                        }
			elseif (array_key_exists($_POST['User']['role'], Config::$rolesAdmin))
			{
                                if ( ! Yii::app()->user->checkAccess(array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN
				)))
                                        throw new CHttpException(403);
                        }
			else
			{
                                throw new CHttpException(403);
                        }

                        $user->setScenario('reg-'.$_POST['User']['role']);

                        $user->setAttributes($_POST['User']);


                        if ( ! empty($_POST['UserData']))
                                $user->data->attributes = $_POST['UserData'];

                        $user_validate = ($user->validate()) ? true : false;
                        $userdata_validate = ($user->data->validate()) ? true : false;

                        if ($user_validate && $userdata_validate) {
                                $user->password = md5($user->password);
				
				// Fix for change role
				if (
					array_key_exists(Yii::app()->user->role, Config::$rolesAdmin)
					&& $user->role == User::ROLE_SPEC_JUR
				) {
					$user->lastname = '';
				}

				// Перед транзакцией, выставляем флаг, чтобы методы afterSaver не выполнялись
				$user->afterSaveCommit = true;

                                $transaction = $user->dbConnection->beginTransaction();
                                if ($user->save()) {

                                        // Сохраняем данные для пользователя.
                                        $user->data->user_id = $user->id;
                                        $user->data->save();

                                        try {
                                                $transaction->commit();

						// После комита запускаем afterSave с флагом ручного запуска
						$user->afterSave(true);

                                        } catch (Exception $e) {
                                                $transaction->rollBack();
                                                throw new CHttpException(403);
                                        }

                                        if (isset($_POST['msg_body']) && isset($_POST['msg_type'])) {

                                                switch ($_POST['msg_type']) {

                                                        case '1':
                                                                MsgBody::newMessage($user->id, $_POST['msg_body']);
                                                                break;

                                                        case '2':
                                                                Yii::app()->mail->create()
                                                                        ->from(array('email'=>'noreply@myhome.ru', 'author'=>'Администрация MyHome.ru'))
                                                                        ->to($user->email)
                                                                        ->subject('Письмо от администрации')
                                                                        ->message(CHtml::encode($_POST['msg_body']))
                                                                        ->send();
                                                                break;
                                                }
                                        }

                                        $user->notification();

                                        if (isset($_POST['system_message']))
                                                SystemMessage::setMessage($user, $_POST['system_message']);


                                        Yii::app()->user->setFlash('user-create-success', 'Пользователь успешно добавлен');
                                        return $this->redirect(Yii::app()->user->returnUrl);
                                }
                        }
                }

                $user->password = '';
                $this->render('_userform', array('user' => $user));
        }

	/**
	 * @brief User update by salemanager
	 * @param $user
	 */
	private function salesUpdate($user)
	{
		// access control
		if (!in_array($user->role, array(User::ROLE_USER, User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR)))
			throw new CHttpException(403);

		$idea_count = Interior::model()->count('author_id = :uid AND status = :stat', array(':uid' => $user->id, ':stat' => Interior::STATUS_ACCEPTED));
		/**
		 * Список всех услуг
		 */
		$services = Service::model()->findAll(array('order' => 'case when parent_id = 0 then id else parent_id end,parent_id', 'limit' => 200));
		$hasErrors = false;

		if (isset( $_POST['UserData']['about'] )) {
			$user->data->about = $_POST['UserData']['about'];
		}

		$checkedServices = $this->updateUserService($user, $hasErrors);
		if ( Yii::app()->request->isPostRequest && !$hasErrors && $user->data->validate()) {
			$user->data->save(false);
			$this->redirect( Yii::app()->user->returnUrl );
		}

		$this->render('_salesUserForm', array(
			'user' => $user,
			'idea_count' => $idea_count,
			'services' => $services,
			'checkedServices' => $checkedServices,
		));
	}

        /**
         * @brief Редактирование пользователя
         */
        public function actionUpdate($id = null)
        {
		/** @var $user User */
		$user = User::model()->findByPk((int) $id);

                if ($user) {
			if ( Yii::app()->user->checkAccess(array( User::ROLE_SALEMANAGER )) ) {
				return $this->salesUpdate($user);
			}

                        if (array_key_exists($user->role, Config::$rolesUserReg)) {
                                if (!Yii::app()->user->checkAccess(array(
					User::ROLE_ADMIN,
					User::ROLE_JUNIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN
				)))
                                        throw new CHttpException(403);
                        } elseif (array_key_exists($user->role, Config::$rolesAdmin)) {
                                if (!Yii::app()->user->checkAccess(array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN
				)))
                                        throw new CHttpException(403);
                        } else {
                                throw new CHttpException(404);
                        }
                }
		/**
		 * Список всех услуг
		 */
		$services = Service::model()->findAll(array('order' => 'case when parent_id = 0 then id else parent_id end,parent_id', 'limit' => 200));
		$checkedServices = array();

                if ( ! empty($_POST['User']) && isset($_POST['User']['role']) && array_key_exists($_POST['User']['role'], Config::$rolesUserReg + Config::$rolesAdmin))
		{

                        $user->setScenario('reg-'.$_POST['User']['role']);

                        if (empty($_POST['User']['password'])) {
                                $password = $user->password;
                        } else {
                                $password = md5($_POST['User']['password']);
                        }

                        $previous_status = $user->status;

                        $user->attributes = $_POST['User'];
                        $user->data->attributes = $_POST['UserData'];

			$user->expert_type = (isset( $_POST['User']['expert_type']) && !empty(User::$expertNames[ $_POST['User']['expert_type'] ]) ) ? intval( $_POST['User']['expert_type'] ) : User::EXPERT_NONE;
			$user->data->expert_desc = isset( $_POST['UserData']['expert_desc']) ? $_POST['UserData']['expert_desc'] : '';

                        $user->password = $password;

                        /*
                         * Следующие два свойства используются при регистрации.
                         * Они являются обязательными, и чтобы не было ошибок
                         * мы указываем для них значения.
                         */
			$hasErrors = false;
			$checkedServices = $this->updateUserService($user, $hasErrors);

			if ($user->validate() && $user->data->validate() && !$hasErrors) {
				// Fix for change role
				if (
					array_key_exists(Yii::app()->user->role, Config::$rolesAdmin)
					&& $user->role == User::ROLE_SPEC_JUR
				) {
					$user->lastname = '';
				}

				// Перед транзакцией, выставляем флаг, чтобы методы afterSaver не выполнялись
				$user->afterSaveCommit = true;

                                $transaction = $user->dbConnection->beginTransaction();
                                if ($user->save() && $user->data->save()) {

                                        try {
                                                if ($user->status == User::STATUS_BANNED && isset($_POST['ban-time']))
                                                        $user->setBan( (int)$_POST['ban-time'] );

                                                $transaction->commit();

						// После комита запускаем afterSave с флагом ручного запуска
						$user->afterSave(true);

                                        } catch (Exception $e) {
                                                $transaction->rollBack();
                                                throw new CHttpException(403);
                                        }

                                        // Уведомление пользователя о сменен его статуса (по Email)
                                        if ($user->status != $previous_status)
                                                $user->notification($previous_status);


                                        if (isset($_POST['msg_body']) && isset($_POST['msg_type'])) {
                                                switch ($_POST['msg_type']) {
                                                        case '1':
                                                                MsgBody::newMessage($user->id, $_POST['msg_body']);
                                                                break;

                                                        case '2':
                                                                Yii::app()->mail->create()
                                                                        ->from(array('email'=>'admin@myhome.ru', 'author'=>'Администрация MyHome.ru'))
                                                                        ->to($user->email)
                                                                        ->subject('Письмо от администрации')
                                                                        ->message(CHtml::encode($_POST['msg_body']))
                                                                        ->send();
                                                                break;
                                                }
                                        }

                                        if (isset($_POST['system_message']))
                                                SystemMessage::setMessage($user, $_POST['system_message']);


                                        Yii::app()->user->setFlash('user-create-success', 'Информация обновлена');


					if (Yii::app()->request->getParam('save_stay')) {
						// Если нажали на кнопку "Применить", то редирект не делаем
					} else {
                                        	$this->redirect(Yii::app()->user->returnUrl);
					}
                                }
                        }
                }
                $user->password = null;

                $idea_count = Interior::model()->count('author_id = :uid AND status = :stat', array(':uid' => $user->id, ':stat' => Interior::STATUS_ACCEPTED));

		// Список выбранных услуг для специалиста
		if ( in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR)) && empty($checkedServices) ) {
			/**
			 * Составление массива предоставляемых пользователем услуг (для отметки checkbox'ами)
			 */
			$tmpArr = Yii::app()->db->createCommand("SELECT * FROM user_service WHERE user_id = '{$user->id}'")->queryAll();

			foreach ($tmpArr as $data) {
				$checkedServices[$data['service_id']] = array(
					'experience'   => $data['experience'],
					'segment'      => $data['segment'],
					'segment_supp' => $data['segment_supp'],
					'expert'       => $data['expert'],
				);
			}
		}

                return $this->render('_userform', array(
                            'user' => $user,
                            'idea_count' => $idea_count,
				'services' => $services,
				'checkedServices' => $checkedServices,
                        ));
        }

	private function updateUserService($user, &$hasErrors)
	{
		// Для не специалистов ( в том числе на смену роли)
		if ( !in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR)) ) {
			/**
			 * Удаление всхех услуг пользователя
			 */
			Yii::app()->db->createCommand()->delete('user_service', 'user_id = :uid', array(':uid' => $user->id));
			return array();
		}

		$requestData = array();
		$errorCode = array();

		$oldUserServices = array();
		/**
		 * Список выбранных пользователем услуг для сохранения
		 */
		$newUserServices = empty($_POST['User']['services']) ? array() : $_POST['User']['services'];
		/**
		 * Список сохраненных ранее услуг
		 */
		$tmpArray = Yii::app()->db->createCommand("SELECT * FROM user_service WHERE user_id = '{$user->id}'")->queryAll();

		/**
		 * Замена ключей массива сохраненных ранее услуг на service_id
		 */
		foreach ($tmpArray as $value) {
			$oldUserServices[$value['service_id']] = $value;
		}

		/**
		 * Формирование данных на сохранение в базу с переносом старых значений
		 */
		$sql_values = array();
		foreach ($newUserServices as $service_id => $value) {

			if (!isset($value['id']) || !isset($value['segment'])) // для общих инпутов
				continue;

			$experience = (int)isset($value['experience']) ? $value['experience'] : 0;
			$service_id  =(int)$service_id;
			$segment = (int)isset($value['segment']) ? $value['segment'] : 0;
			$segment_supp = (int)isset($value['segment_supp']) ? $value['segment_supp'] : 0;
			$expert = (int)isset($value['expert']) ? $value['expert'] : 0;


			$requestData[$service_id]['experience'] = $experience;
			$requestData[$service_id]['segment'] = $segment;
			$requestData[$service_id]['segment_supp'] = $segment_supp;
			$requestData[$service_id]['expert'] = $expert;

                        if (!in_array(Yii::app()->user->role, array(
				User::ROLE_ADMIN,
				User::ROLE_POWERADMIN,
				User::ROLE_MODERATOR,
				User::ROLE_SALEMANAGER,
				User::ROLE_SENIORMODERATOR
			))) {
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
                        }
			/**
			 * Формирование массива данных для сохранение
			 */
			$sql_values[] = "( '{$user->id}', '{$service_id}', '{$experience}', '{$segment}', '{$segment_supp}', '{$expert}')";
			$sqlValues2[] = "( '{$user->id}', '{$service_id}', 0, 0)";
		}



		if (!$hasErrors && Yii::app()->request->isPostRequest) {

			$transaction = Yii::app()->db->beginTransaction();
			try
			{
				/**
				 * Удаление всхех услуг пользователя
				 */
				Yii::app()->db->createCommand()->delete('user_service', 'user_id = :uid', array(':uid' => $user->id));

				/**
				 * Вставка новых пользовательских услуг (в случае, если пользователь их выбрал)
				 */

				if (!empty($sql_values)) {
					Yii::app()->db->createCommand('insert into user_service (`user_id`, `service_id`, `experience`, `segment`, `segment_supp`, `expert`) values ' . implode(',', $sql_values))->execute();

					$sql = 'insert ignore into user_service_data (`user_id`, `service_id`, `rating`, `project_qt`) values ' . implode(',', $sqlValues2);
					Yii::app()->db->createCommand($sql)->execute();
				}

				/** Обновление рейтинга */
				// Yii::app()->gearman->appendJob('userService', array('userId'=>$user->id));

				$transaction->commit();
			} catch (Exception $e) {

				$transaction->rollback();
				throw new CHttpException(500);
			}
		}

		$checkedServices = array();
		if ($hasErrors) {
			$checkedServices = $requestData;
		} else {
			/**
			 * Составление массива предоставляемых пользователем услуг (для отметки checkbox'ами)
			 */
			$tmpArr = Yii::app()->db->createCommand("SELECT * FROM user_service WHERE user_id = '{$user->id}'")->queryAll();

			foreach ($tmpArr as $data) {
				$checkedServices[$data['service_id']] = array(
					'experience'   => $data['experience'],
					'segment'      => $data['segment'],
					'segment_supp' => $data['segment_supp'],
					'expert'       => $data['expert'],
				);
			}
		}

		return $checkedServices;

	}

        public function actionResendcode($id = null)
        {
                $user = User::model()->findByPk($id);

                if (!$user)
                        throw new CHttpException(404);

                $user->activateKey($user->role);

                Yii::app()->user->setFlash('user-create-success', 'Код активации отправлен');
                return $this->redirect(Yii::app()->user->returnUrl);
        }

        /**
         * @brief Просмотр пользователя
         */
        public function actionView($id = null)
        {
                $user = User::model()->findByPk((int) $id);
                if ($user) {
                        Yii::app()->user->setReturnUrl(Yii::app()->request->getRequestUri());

                        if (array_key_exists($user->role, Config::$rolesUserReg))
			{
                                if (!Yii::app()->user->checkAccess(array(
					User::ROLE_ADMIN,
					User::ROLE_JUNIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER
				)))
                                        throw new CHttpException(403);
                        }
			elseif (array_key_exists($user->role, Config::$rolesAdmin))
			{
                                if (!Yii::app()->user->checkAccess(array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN
				)))
                                        throw new CHttpException(403);
                        }
			else
			{
                                throw new CHttpException(404);
                        }

			$stores = array();
			if ($user->role == User::ROLE_STORES_ADMIN) {
				Yii::import('catalog.models.Store');
				$stores = Store::model()->findAllByAttributes( array('admin_id'=>$user->id) );
			}

                        return $this->render('view', array(
                                	'user' => $user,
					'stores' => $stores,
                                	'messages' => SystemMessage::getMessages($user),
                                ));
                }
        }

        /**
         * С помощью ajax запроса создает новый комментарий к модели
         * @param integer $model_id ID пользователя, которому добавляется комментарий
         * @param string $message Комментарий
         */
        public function actionCreate_message_ajax()
        {

                $model = User::model()->findByPk($_POST['model_id']);

                if (!$model)
                        die(CJSON::encode(array('key' => 'error', 'val' => 'Ошибка 404')));

                if (!isset($_POST['message']) || empty($_POST['message']))
                        die(CJSON::encode(array('key' => 'error', 'val' => 'Необходимо указать комментарий')));

                SystemMessage::setMessage($model, $_POST['message']);

                die(CJSON::encode(array('key' => 'ok')));
        }

        public function actionInvite()
        {
                $user = new User();

                if ( ! empty($_POST['User'])) {

                        $user->setScenario('reg-'.$_POST['User']['role']);

                        if ($_POST['User']['role'] == User::ROLE_SPEC_JUR)
                                $user->data->setScenario('reg-'.User::ROLE_SPEC_JUR);

                        $user->attributes = $_POST['User'];

                        if (!empty($_POST['UserData']))
                                $user->data->attributes = $_POST['UserData'];


                        // Ajax валидация
                        if (isset($_POST['ajax'])) {
                                $this->performAjaxValidation($user, $user->data);
                        }


                        $user_validate = ($user->validate()) ? true : false;
                        $userdata_validate = ($user->data->validate()) ? true : false;


                        if ($user_validate && $userdata_validate) {
                                $unsafe_pass = $user->password;
                                $user->password = md5($user->password);

                                $user->activateKey = User::generateActivateKey();
                                $user->status = User::STATUS_VERIFYING;
                                $user->referrer_id = Yii::app()->user->model->id;
				
				// Fix for change role
				if (
					array_key_exists(Yii::app()->user->role, Config::$rolesAdmin)
					&& $user->role == User::ROLE_SPEC_JUR
				) {
					$user->lastname = '';
				}

				// Перед транзакцией, выставляем флаг, чтобы методы afterSaver не выполнялись
				$user->afterSaveCommit = true;

                                $transaction = $user->dbConnection->beginTransaction();
                                if ($user->save()) {

                                        // Сохраняем данные для пользователя.
                                        $user->data->user_id = $user->id;
                                        $user->data->save();

                                        // установка города предоставления услуг для специалистов
                                        if(in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR))) {
						$city = City::model()->findByPk($user->city_id);
						if (!is_null($city)) {
							Yii::app()->db->createCommand()->insert('user_servicecity', array(
										'user_id'=>$user->id, 
										'city_id'=>$user->city_id,
										'region_id'=>$city->region_id,
										'country_id'=>$city->country_id,
								));
						}
					}


                                        try {
                                                $transaction->commit();

						// После комита запускаем afterSave с флагом ручного запуска
						$user->afterSave(true);

                                        } catch (Exception $e) {
                                                $transaction->rollBack();
                                                throw new CHttpException(403);
                                        }


                                        Yii::app()->redis->set($user->id . '_pass', serialize($unsafe_pass));

                                        $user->notification();


                                        /* ------------------------------------
                                         *  Отправка письма пользователю
                                         * ------------------------------------
                                         */

					if(in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR))) {
						$template = 'inviteSpecialist';
						$activationKey = Yii::app()->homeUrl . '/site/activation/key/' . $user->activateKey;
					}
					else {
						$template = 'invite';
						$activationKey = CHtml::link(
							Yii::app()->homeUrl . '/site/activation/key/' . $user->activateKey,
							Yii::app()->homeUrl . '/site/activation/key/' . $user->activateKey
						);
					}

                                        Yii::app()->mail->create($template)
                                                ->to($user->email)
                                                ->notifier(true)
						->useView(0)
                                                ->params(array(
                                                        'user_name' 	=> $user->name,
                                                        'manager_name' 	=> Yii::app()->user->model->name,
                                                        'activate_link'	=> $activationKey,
                                                        'sign_C' => Yii::app()->mail->create('sign_C')->params(array(
                                                                                'manager_name'  => Yii::app()->user->model->name,
                                                                                'manager_email' => Yii::app()->user->model->email,
                                                                                'manager_skype' => Yii::app()->user->model->data->skype,
                                                                                'manager_phone' => Yii::app()->user->model->phone,
                                                                        ))->useView(true)->prepare()->getMessage(),
                                                ))
                                                ->send();


                                        Yii::app()->user->setFlash('user-create-success', 'Пользователь успешно добавлен и ему отправлено приглашение.');
                                        return $this->redirect('/admin/user/invite');
                                }
                        }
                }


                return $this->render('invite', array(
                            'user' => $user,
                        ));
        }

        public function actionActivatelog()
        {
                Yii::import('application.modules.log.models.*');

                $model = new LogUserActivation('search');
                
                $model->unsetAttributes();  // clear any default values
                if (isset($_GET['LogUserActivation']))
                        $model->attributes = $_GET['LogUserActivation'];

                $criteria = new CDbCriteria;

                if ($model->referrer_id) {
			$criteria->compare('referrer_id', null);
			if ($model->referrer_id == '-1')
				$criteria->addCondition('ISNULL(referrer_id)');
			else
				$criteria->compare('referrer_id', $model->referrer_id);
		}

                
                if (($activate_from = Yii::app()->request->getParam('activate_from')))
                        $criteria->compare('activate_time', '>=' . strtotime($activate_from));

                if (($activate_to = Yii::app()->request->getParam('activate_to')))
                        $criteria->compare('activate_time', '<=' . strtotime($activate_to));

		$criteria->order = 'id DESC';

                $dataProvider = new CActiveDataProvider('LogUserActivation', array(
                            'criteria' => $criteria,
                            'pagination' => array(
                                'pageSize' => 20,
                            ),
                        ));

                $this->render('activatelog', array(
                    'dataProvider' => $dataProvider,
                    'model' => $model,
                    'activate_from' => $activate_from,
                    'activate_to' => $activate_to,
                ));
        }

        protected function performAjaxValidation($model, $user_data)
        {
                if (isset($_POST['ajax'])) {

                        echo CActiveForm::validate(array($model, $user_data));
                        Yii::app()->end();
                }
        }

	/**
	 * Загрузка аватарок пользователей
	 * @throws CHttpException
	 */
	public function actionUploadimage()
	{
		if (!Yii::app()->request->isPostRequest || empty($_FILES['User']) || empty($_POST['userId']) )
			throw new CHttpException(404);

		$userId = intval($_POST['userId']);
		$user = User::model()->findByPk($userId);

		if (is_null($user))
			throw new CHttpException(404);
		// Менять только для указанных ролей
		if ( !in_array($user->role, array(User::ROLE_USER, User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR, User::ROLE_JOURNALIST)) ) {
			throw new CHttpException(403);
		}

		$user->setImageType('user');
		$avatar = UploadedFile::loadImage($user, 'image_file', '', true);
		if ($avatar) {
			$user->image_id = $avatar->id;
			$user->save(false);
			$response = array(
				'success'=>true,
				'fileUrl'=>'/'.$user->getPreview(Config::$preview['crop_150']),
			);
			die(CJSON::encode($response));
		}
		die ( json_encode(array('error'=>true)) );
	}

	/**
	 * Удаление аватарок пользователей
	 * @throws CHttpException
	 */
	public function actionRemoveimage()
	{
		if (!Yii::app()->request->isAjaxRequest || empty($_POST['userId']) )
			throw new CHttpException(404);

		$userId = intval($_POST['userId']);
		$user = User::model()->findByPk($userId);

		if (is_null($user))
			throw new CHttpException(404);
		// Менять только для указанных ролей
		if ( !in_array($user->role, array(User::ROLE_USER, User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR, User::ROLE_JOURNALIST)) ) {
			throw new CHttpException(403);
		}

		$user->image_id = null;
		try {
			$user->save(false);
			die ( json_encode( array('success'=>true, 'fileUrl'=>'/'.$user->getPreview(User::$preview['crop_150']) ) ) );
		} catch (Exception $e) {
			die ( json_encode(array('error'=>true)) );
		}
	}

	/**
	 * @return JSON array with user info
	 * @author Alexey Shvedov
	 * @param string $term
	 */
	public function actionAutocompletePhone($term)
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		if (!empty($term)) {

			$term = addslashes($term);

			$users = Yii::app()->db->createCommand("SELECT * FROM user WHERE status <> '".User::STATUS_DELETED."' AND phone_search LIKE '%{$term}%'")->queryAll();

			$arr = array();

			foreach ($users as $user) {
				$label = '('.$user['login'].') '.$user['firstname'];
				$label .= empty($user['secondname']) ? '' : ' ' . $user['secondname'];
				$label .= empty($user['lastname']) ? '' : ' ' . $user['lastname'];
				$arr[] = array(
					'label' => $label.' — '.$user['phone'], // label for dropdown list
					'value' => $label.' — '.$user['phone'], // value for input field
					'id' => $user['id'], // return value from autocomplete
				);
			}
			echo CJSON::encode($arr);
		}
	}

	/**
	 * Ajax метод, который делает из пользователя администратора магазина,
	 * т.е. меняет ему роль на User::ROLE_STORES_ADMIN
	 */
	public function actionAjaxMakeAdminStore($user_id)
	{
		$success = true;
		$msgError = '';

		if ( ! Yii::app()->request->isAjaxRequest)
			throw new CHttpException(403, 'Страница доступна только по Ajax запросу.');


		// Ищем указанного пользвоателя
		$model = User::model()->findByPk((int)$user_id);
		if ( ! $model)
			throw new CHttpException(400, 'Указанный пользователь не существует!');

		// Назначаем ему стасус Администратора магазина
		$model->role = User::ROLE_STORES_ADMIN;

		if ( ! $model->save()) {
			$success = false;
			$msgError = $model->getErrors();
		}


		die(json_encode(array(
			'success' => $success,
			'msgError' => $msgError
		)));
	}
}