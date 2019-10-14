<?php

/**
 * @brief Работа с сообщениями
 * @author Roman Kuzakov
 */
class MessageController extends FrontController
{
	
	public function beforeAction($action)
	{
		Yii::import('application.modules.idea.models.Interior');
		
		return parent::beforeAction($action);
	}

        public function filters()
        {
                return array('accessControl');
        }

	/**
         * @brief Разрешает доступ по ролям
         * @return array
         */
        public function accessRules()
        {

                return array(
                    array('allow',
                        'actions' => array('show', 'inbox', 'outbox', 'search', 'create', 'answer', 'delete','addMessageToSpam','deleteMessageFromSpam'),
                        'roles' => array(
				User::ROLE_ADMIN,
				User::ROLE_JUNIORMODERATOR,
				User::ROLE_SALEMANAGER,
				User::ROLE_MODERATOR,
				User::ROLE_SENIORMODERATOR,
				User::ROLE_POWERADMIN,
				User::ROLE_SPEC_FIS,
				User::ROLE_SPEC_JUR,
				User::ROLE_USER,
                                User::ROLE_JOURNALIST,
                                User::ROLE_STORES_MODERATOR,
                                User::ROLE_STORES_ADMIN,
			),
                    ),
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }

        /**
         * @brief Отображение входящих сообщений пользователя
         */
        public function actionInbox()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

		$pageSize = $this->getPageSize();
		
                $messageProvider = new CActiveDataProvider('MsgBody', array(
			'criteria' => array(
				'condition' => 'recipient_id = :user AND (recipient_status = :read OR recipient_status = :unread)',
				'order' => 'create_time DESC',
				'params' => array(':user' => Yii::app()->user->id, ':read' => MsgBody::STATUS_READ, ':unread' => MsgBody::STATUS_UNREAD),
			),
			'pagination' => array(
				'pageSize' => $pageSize,
				'pageVar' => 'page'
			)
		));

		if (in_array(Yii::app()->user->role, array(User::ROLE_SPEC_JUR, User::ROLE_SPEC_FIS))) {
			$this->menuActiveKey = 'designer';
			$this->menuIsActiveLink = true;
			return $this->render('//member/message/specialist/inbox', array(
				'messageProvider' => $messageProvider,
				'user' => Yii::app()->user->model,
				'pageSize' => $pageSize
			), false, array(
				'profileSpecialist', array(
					'user' => Yii::app()->user->model
				)
			));
		}

                $this->render('//member/message/user/inbox', array(
			'messageProvider' => $messageProvider,
			'user'            => Yii::app()->user->model,
			'pageSize'        => $pageSize
		), false, array(
			'profileUser', array(
				'user' => Yii::app()->user->model
			)
		));
        }

        /**
         * @brief Отображение исходящих сообщений пользователя
         */
        public function actionOutbox()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

		$pageSize = $this->getPageSize();

                $messageProvider = new CActiveDataProvider('MsgBody', array(
			'criteria' => array(
				'condition' => 'author_id = :user AND author_status = :status',
                                'order' => 'create_time DESC',
                                'params' => array(':user' => Yii::app()->user->id, ':status' => MsgBody::STATUS_READ),
			),
                        'pagination' => array(
				'pageSize' => $pageSize,
				'pageVar' => 'page'
			)
		));
		
		if (in_array(Yii::app()->user->role, array(User::ROLE_SPEC_JUR, User::ROLE_SPEC_FIS))) {
			$this->menuActiveKey = 'designer';
			$this->menuIsActiveLink = true;
			return  $this->render('//member/message/specialist/outbox', array(
				'messageProvider' => $messageProvider,
				'user' => Yii::app()->user->model,
				'pageSize' => $pageSize
			), false, array(
				'profileSpecialist', array(
					'user' => Yii::app()->user->model
				)
			));
		}

                $this->render('//member/message/user/outbox', array(
			'messageProvider' => $messageProvider,
			'user'            => Yii::app()->user->model,
			'pageSize'        => $pageSize
		), false, array(
			'profileUser', array(
				'user' => Yii::app()->user->model
			)
		));
        }

        /**
         * @brief Ответ из просмотра сообщений
         * @param int $recipient_id 
         */
        public function actionAnswer($recipient_id = null)
        {

                $recipient = User::model()->findByPk($recipient_id);
                
                if (!$recipient)
                        return $this->redirect(Yii::app()->user->returnUrl);

                if (isset($_POST['MsgBody'])) {
                        $body = new MsgBody('create');
                        $body->attributes = $_POST['MsgBody'];
                        $body->author_status = MsgBody::STATUS_READ;
                        $body->recipient_status = MsgBody::STATUS_UNREAD;
                        $body->author_id = Yii::app()->user->id;
                        $body->recipient_id = $recipient->id;
                        if ($body->save()) {

                                $body->saveFiles();

                                Yii::app()->user->setFlash('msg_success', 'Ваше сообщение отправлено');
                                $this->redirect($this->createUrl($this->id . '/show', array('id' => $body->id)));
                        }
                }

                Yii::app()->user->setFlash('msg_fail', 'Ваше сообщение не отправлено');
                $this->redirect(Yii::app()->user->returnUrl);
        }

        /**
         * @brief Отображение цепочки сообщений
         * @param int $id MsgBody id
         */
        public function actionShow($id = null)
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

                $msg = MsgBody::model()->findByPk((int) $id);
                Yii::app()->user->setReturnUrl($this->createUrl($this->id . '/' . $this->action->id, array('id' => $id)));
                

                if ($msg) {
                        
                        if($msg->author_id == Yii::app()->user->id ) {
				$user2 = $msg->recipient_id;
			} else if ($msg->recipient_id == Yii::app ()->user->id) {
				$user2 = $msg->author_id;
			} else {
				throw new CHttpException(404);
			}
                        

                        $msg->setReadStatus();

                        $messageProvider = new CActiveDataProvider('MsgBody', array(
				'criteria' => array(
					'condition' => '(author_id = :user AND author_status = :read AND recipient_id = :user2) OR (recipient_id = :user AND author_id = :user2 AND (recipient_status = :read OR recipient_status = :unread))',
                                        'order' => 'create_time DESC',
                                        'params' => array(':user' => Yii::app()->user->id, ':user2'=>$user2, ':read' => MsgBody::STATUS_READ, ':unread' => MsgBody::STATUS_UNREAD),
				),
                                'pagination' => array(
					'pageSize' => 30,
                                )
			));
			
                        $body = new MsgBody();
			
			if (in_array(Yii::app()->user->role, array(User::ROLE_SPEC_JUR, User::ROLE_SPEC_FIS))) {       
				$this->menuActiveKey = 'designer';
				$this->menuIsActiveLink = true;
				return $this->render('//member/message/specialist/show', array(
					'msg' => $msg,
					'user' => Yii::app()->user->model,
					'messageProvider' => $messageProvider,
					'body' => $body
				), false, array(
					'profileSpecialist', array(
						'user' => Yii::app()->user->model
					)
				));
			}
			
                        $this->render('//member/message/user/show', array(
				'msg'             => $msg,
				'user'            => Yii::app()->user->model,
				'messageProvider' => $messageProvider,
				'body'            => $body
			), false, array(
				'profileUser', array(
					'user' => Yii::app()->user->model
				)
			));
                } else {
                        throw new CHttpException(404);
                }
        }


	/**
	 * Поиск личных сообщений пользователя.
	 *
	 * @param string $qsearch
	 *
	 * @return string
	 */
	public function actionSearch($qsearch = '')
	{
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

		$pageSize = $this->getPageSize();
		
		$qsearch = preg_replace('/[!@#$%^&*()"\']/', '', $qsearch);
		
		$sphinxClient = Yii::app()->search;
		$uId = Yii::app()->user->id;

		$messageProvider = new CSphinxDataProvider($sphinxClient, array('index' => 'user_message',
			'modelClass'	=> 'MsgBody',
			'query'		=> '(
						((@author_id '.$uId.') (@author_status '.MsgBody::STATUS_READ.' | @author_status '.MsgBody::STATUS_UNREAD.'))
						|
						((@recipient_id '.$uId.') (@recipient_status '.MsgBody::STATUS_READ.'))
						)
						@message ('.$qsearch.'*)',

			'sortMode'	=> SPH_SORT_EXTENDED,
			'sortExpr'	=> 'create_time DESC',
			'matchMode'	=> SPH_MATCH_EXTENDED,
			'pagination'	=> array('pageSize' => $pageSize),
		));

		if (in_array(Yii::app()->user->role, array(User::ROLE_SPEC_JUR, User::ROLE_SPEC_FIS))) {
			$this->menuActiveKey = 'designer';
			$this->menuIsActiveLink = true;

			return $this->render('//member/message/specialist/search', array(
				'messageProvider' => $messageProvider,
				'user'            => Yii::app()->user->model,
				'pageSize'        => $pageSize
			), false, array(
				'profileSpecialist', array(
					'user' => Yii::app()->user->model
				)
			));
		}

                return $this->render('//member/message/user/search', array(
			'messageProvider' => $messageProvider,
			'user'            => Yii::app()->user->model,
			'pageSize'        => $pageSize
		));
	}

        /**
         * @brief Создание нового сообщения
         * @param int $uid
         */
        public function actionCreate($uid = null)
        {
		$this->layout = false;
		

                $body = new MsgBody('create');

                // Проверка переданного UID
                if ($uid && $uid != Yii::app()->user->id)
                        $rcp = User::model()->findByPk((int) $uid);
                else
                        $rcp = null;

                if (isset($_POST['MsgBody'])) {
                        $body->attributes = $_POST['MsgBody'];
                        $body->author_status = MsgBody::STATUS_READ;
                        $body->recipient_status = MsgBody::STATUS_UNREAD;
                        $body->author_id = Yii::app()->user->id;
			
			// Если передавали параметры ID пользователя
			// и ID верный, то подставляем получателя вручную.
			if (!is_null($rcp))
				$body->recipient_id = $rcp->id;
			
			
                        if ($body->validate() && $body->save(false)) {

                                $body->saveFiles();

				$urlReferrer = Yii::app()->request->getUrlReferrer();

				if($urlReferrer)
				{
					$urlParse = parse_url($urlReferrer);
				}

				$parseUrlHost = parse_url(Yii::app()->request->getHostInfo());

				if(isset($urlParse) && $parseUrlHost['host'] == $urlParse['host'])
				{
					$urlPath = $urlParse['path'];
					$stringArray = explode("/", $urlPath);
					if(isset($stringArray[1]) && isset($stringArray[2]) && $stringArray[1] == 'users')
					{
						StatSpecialist::hit($body->recipient_id, StatSpecialist::TYPE_SEND_MESSAGE_TO_SPECIALIST);

					}
				}

                                //Yii::app()->user->setFlash('msg_success', 'Ваше сообщение отправлено');
				$this->redirect(Yii::app()->user->returnUrl.'?send=good');
                        }
                }
                
		Yii::app()->user->setFlash('msg_list_error', 'Ваше сообщение не отправлено.<br>Выберите пользователя из списка и напишите сообщение не более 2000 символов.');
		
		$recipient = substr(@$_POST['recipient'], 0, 3);
		$this->redirect(Yii::app()->user->returnUrl.'?msg=show'.'&recipient='.$recipient );
        }

        /**
         * @brief AJAX функция удаления сообщения для текущего пользователя.
         * Определяет, кем является пользователь в цепочке и меняет его статус на "УДАЛЕНО"
         * @param int $id 
         */
        public function actionDelete($id = null)
        {
                if (Yii::app()->request->isAjaxRequest && !is_null($id)) {
                        $criteria = new CDbCriteria();
                        $criteria->condition = 'id = :id AND (recipient_id = :user OR author_id = :user)';
                        $criteria->params = array(':id' => (int) $id, ':user' => Yii::app()->user->id);

                        $msg = MsgBody::model()->find($criteria);

                        if ($msg && ($msg->author_id == Yii::app()->user->id)) {
                                $msg->author_status = MsgBody::STATUS_DELETE;
                                $msg->save();
                                echo 'ok';
                        } elseif ($msg && ($msg->recipient_id == Yii::app()->user->id)) {
                                $msg->recipient_status = MsgBody::STATUS_DELETE;
                                $msg->save();
                                echo 'ok';
                        } else {
                                echo 'error';
                        }
                }
        }


	/**
	 *@brief Ajax функция для добавления сообщения в спам лист.
	 *@param integer $id
	 *
	 */
	public function actionAddMessageToSpam($id)
	{
		if ( !Yii::app()->request->isAjaxRequest )
			throw new CHttpException(400);

		//Если сообщение с таким ID не существует
		if(!MsgBody::model()->findByPk(((int)$id)))
		{
			throw new CHttpException(400);
		}

		$spam=Spam::model()->findByAttributes(array('msg_id' => (int)$id));

		//Если сообщение с таким айди уже добавлено в спам
		if($spam)
		{
			throw new CHttpException(400);
		}

		$spam=new Spam();
		$spam->msg_id=(int)$id;
		$spam->status=$spam::STATUS_NEW;

		$recipientId=$spam->msgBody->recipient_id;
		$authorId=$spam->msgBody->author_id;

		$spam->save();

		Yii::app()->mail->create('spamNotifier')
			->to(Config::$adminEmails)
			->params(array(
				'recipientName' => User::model()->findByPk($recipientId)->login,
				'authorName' => User::model()->findByPk($authorId)->login,
				'request' => Yii::app()->homeUrl . '/member/admin/spam/update/id/'.$spam->id,
				'dateCreate' => date( 'H\hi l d F', $spam->create_time),
			))
			->send();

		die(json_encode(array('success'=>true), JSON_NUMERIC_CHECK));

	}


	/**
	 *@brief Ajax функция для удаления сообщения из спам листа
	 * @param integer $id
	 *
	 */
	public function actionDeleteMessageFromSpam($id)
	{
		if ( !Yii::app()->request->isAjaxRequest )
			throw new CHttpException(400);

		$spam=Spam::model()->findByAttributes(array('msg_id' => (int)$id));

		if($spam)
		{
			$spam->delete();
			die(json_encode(array('success'=>true), JSON_NUMERIC_CHECK));
		}
		else
		{
			throw new CHttpException(400);
		}

	}



	/**
	 * Функция получает из параметра и возвращает
	 * размер постранички для сообщений
	 * 
	 * @return int
	 */
	private function getPageSize()
	{
		$pageSize = (int)Yii::app()->request->getParam('pagesize');
		$pageSize = (is_null($pageSize) || !isset(Config::$messagePageSizes[$pageSize]) ) ? reset(Config::$messagePageSizes) : $pageSize;
		
		return $pageSize;
	}

}