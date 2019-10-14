<?php

class ForumController extends FrontController
{

	public function filters()
	{
		return array('accessControl');
	}

	public function accessRules()
	{
		return array(
			array('allow',
				'actions' => array('create', 'index', 'category', 'topic', 'search', 'DelTopicGuest', 'AjaxSimilarTopic'),
				'users' => array('*'),
			),
			array('allow',
				'actions' => array('mytopics', 'myanswers', 'create', 'delAnswer'),
				'users' => array('@'),
			),
			array('deny',
				'users' => array('*'),
			),
		);
	}

	public function beforeAction($action)
	{
		Yii::import('application.modules.content.models.Content');

		Yii::app()->getClientScript()->registerCssFile('/css/forum.css');
		Yii::app()->getClientScript()->registerScriptFile('/js/Cforum.js');

		$this->menuActiveKey = 'forum';
		if ($this->action->id != 'index')
			$this->menuIsActiveLink = true;

		return parent::beforeAction($action);
	}

	/**
	 * Главная страница раздела "Форум"
	 */
	public function actionIndex()
	{
		// Получаем 15 свежих топиков (тем)
		$freshTopics = ForumTopic::model()->findAllByAttributes(
			array(),
			array(
				'order'     => 'create_time DESC',
				'condition' => 'status = :st1',
				'params'    => array(':st1' => ForumTopic::STATUS_PUBLIC),
				'limit'     => '15'
			)
		);

		// Получаем 15 самых просматриваемых топика (темы)
		$popularTopics = ForumTopic::model()->findAllByAttributes(
			array(),
			array(
				'order'     => 'count_view DESC, count_answer DESC',
				'condition' => 'status = :st1',
				'params'    => array(':st1' => ForumTopic::STATUS_PUBLIC),
				'limit'     => '15'
			)
		);

		// Получаем список самых активных пользователей
		$mostActiveUsers = $this->getMostActiveUsers();

		// Получаем список самых активных Экспертов
		$mostActiveExperts = $this->getMostActiveExperts();

		$this->render(
			'//social/forum/index',
			array(
				'freshTopics'       => $freshTopics,
				'popularTopics'     => $popularTopics,
				'mostActiveUsers'   => $mostActiveUsers,
				'mostActiveExperts' => $mostActiveExperts,
			),
			false,
			array('forum', array(
				'sections' => $this->getSections(),
				'breadCrumbs' => array()
			))
		);
	}

	/**
	 * Получаем список самых активных пользователей форума.
	 *
	 * @param bool $use_cache Флаг определяющий использование данных из кеша
	 * @return array User[] Список самых активных пользователей
	 */
	private function getMostActiveUsers($use_cache = true)
	{
		$mostActiveUsers = Yii::app()->cache->get('mostActiveUsers');

		// Кол-во юзеров попадающих в результат
		$qntUsers = 15;

		if ( ! $use_cache)
			$mostActiveUsers = null;

		if ( ! $mostActiveUsers) {

			// Получаем список самых активных пользователей
			// Считается по сумме кол-ва ответов и кол-ва созданных тем.
			$sql = "
				SELECT
					user.id, user.login, (CASE WHEN tmp_fa.cnt IS NULL THEN 0 ELSE tmp_fa.cnt END)+(CASE WHEN tmp_ft.cnt IS NULL THEN 0 ELSE tmp_ft.cnt END) as qnt
				FROM
					user
				LEFT JOIN (SELECT author_id, COUNT(*) as cnt FROM forum_answer GROUP BY author_id ORDER BY cnt DESC LIMIT ".$qntUsers.") as tmp_fa
					ON tmp_fa.author_id = user.id
				LEFT JOIN (SELECT author_id, COUNT(*) as cnt FROM forum_topic GROUP BY author_id ORDER BY cnt DESC LIMIT ".$qntUsers.") as tmp_ft
					ON tmp_ft.author_id = user.id
				WHERE
					tmp_fa.cnt IS NOT NULL OR tmp_ft.cnt IS NOT NULL
				GROUP BY user.id
				ORDER BY qnt DESC
			";
			$user_ids = Yii::app()->db->createCommand($sql)->queryColumn();

			if (count($user_ids) > $qntUsers)
				$user_ids = array_slice($user_ids, 0, $qntUsers);
			$mostActiveUsers = User::model()->findAllByAttributes(array('id' => $user_ids ));

			Yii::app()->cache->set('mostActiveUsers', $mostActiveUsers, 3600);
		}


		return $mostActiveUsers;
	}

	/**
	 * Получаем список самых активных Экспертов
	 *
	 * @param bool $use_cache Флаг определяющий использование данных из кеша
	 * @return array User[] Список самых активных пользователей
	 */
	private function getMostActiveExperts($use_cache = true)
	{
		$mostActiveExperts = Yii::app()->cache->get('mostActiveExperts');

		// Кол-во юзеров попадающих в результат
		$qntUsers = 9;

		if ( ! $use_cache)
			$mostActiveExperts = null;

		if ( ! $mostActiveExperts) {

			// Получаем список самых активных пользователей
			// Считается по сумме кол-ва ответов и кол-ва созданных тем.
			$sql = "
				SELECT
					user.id, user.login, (CASE WHEN tmp_fa.cnt IS NULL THEN 0 ELSE tmp_fa.cnt END)+(CASE WHEN tmp_ft.cnt IS NULL THEN 0 ELSE tmp_ft.cnt END) as qnt
				FROM
					user
				LEFT JOIN (SELECT author_id, COUNT(*) as cnt FROM forum_answer GROUP BY author_id ORDER BY cnt DESC LIMIT ".$qntUsers.") as tmp_fa
					ON tmp_fa.author_id = user.id
				LEFT JOIN (SELECT author_id, COUNT(*) as cnt FROM forum_topic GROUP BY author_id ORDER BY cnt DESC LIMIT ".$qntUsers.") as tmp_ft
					ON tmp_ft.author_id = user.id
				WHERE
					(tmp_fa.cnt IS NOT NULL OR tmp_ft.cnt IS NOT NULL)
					AND
					(user.expert_type = :et1 OR user.expert_type = :et2)
				GROUP BY user.id
				ORDER BY qnt DESC
			";
			$user_ids = Yii::app()->db->createCommand($sql)->queryColumn(array(':et1' => User::EXPERT, ':et2' => User::EXPERT_TOP));

			if (count($user_ids) > $qntUsers)
				$user_ids = array_slice($user_ids, 0, $qntUsers);

			$mostActiveExperts = User::model()->findAllByAttributes(array('id' => $user_ids));

			Yii::app()->cache->set('mostActiveExperts', $mostActiveExperts, 3600);
		}


		return $mostActiveExperts;
	}

	/**
	 * Список тем категории с ключом $key
	 * @param $key
	 * @throws CHttpException
	 */
	public function actionCategory($key = '')
	{
		// Получаем ID раздела форума по его ключу
		$sect = ForumSection::model()->findByAttributes(array('key' => $key));
		if ( ! $sect)
			throw new CHttpException(404);


		// Получаем и устанавливаем кол-во элементов на странице
		$pageSize = $this->getPageSize();

		// Получаем параметры
		$filter = $this->getFilter();

		// Формируем критерии выборки с учетом фильтра
		$order = $this->getCriteriaOrder($filter);

		$criteria = new CDbCriteria(array(
			'order'     => $order,
			'condition' => 'status = :st AND section_id = :sId',
			'params'    => array(':st' => ForumTopic::STATUS_PUBLIC, ':sId' => $sect->id)
		));


		$count = ForumTopic::model()->count($criteria);
		$pages = new CPagination($count);

		$pages->pageSize = $pageSize;
		$pages->applyLimit($criteria);


		// Провайдер списка тем
		$topicProvider = new CActiveDataProvider('ForumTopic', array(
			'criteria' => $criteria,
			'pagination' => $pages
		));



		// Получаем последние три статьи, по теме связанной с разделом форума.
		Yii::import('application.modules.media.models.MediaKnowledge');

		$knowledges = MediaKnowledge::model()->published()->findAll(
			array(
				'limit'     => '3',
				'join'      => 'INNER JOIN ('
					.' SELECT model_id FROM media_theme_select'
					.' WHERE model = "MediaKnowledge"'
					.' AND theme_id = "' . $sect->theme_id . '"'
					.' ) as tmp ON tmp.model_id = t.id',
			)
		);


		if ($topicProvider->getTotalItemCount() > 0)
			$view = '//social/forum/category';
		else
			$view = '//social/forum/categoryEmpty';

		$this->render(
			$view,
			array(
				'topicProvider' => $topicProvider,
				'pageSize'      => $pageSize,
				'filter'        => $filter,
				'knowledges'    => $knowledges,
				'sectionName'	=> $sect->name,
			),
			false,
			array(
				'forum',
				array(
					'sections' => $this->getSections(),
					'activeSection' => $sect->id,
					'h1' => $sect->name,
				)
			)
		);
	}

	/**
	 * Страница тематики с ответами
	 * @param null $id
	 * @throws CHttpException
	 */
	public function actionTopic($id = null)
	{
		$topic = ForumTopic::model()->findByPk((int)$id);
		if ( ! $topic || $topic->status != ForumTopic::STATUS_PUBLIC)
			throw new CHttpException(404);

		// Увиличиваем кол-во просмотров
		$topic->updateByPk($topic->id, array('count_view' => $topic->count_view + 1));


		// Если в данных POST есть данные по "ответу", то пробуем сохранить "ответ"
		$modelAnswer = $this->addAnswer($topic->id);

		// Если в данных POST есть данные на обновлевние ответа, пробуем обновить запись
		$this->updateAnswer($topic->id);


		// Получаем список всех ответов для текущего топика.
		$answers = ForumAnswer::model()->findAllByAttributes(
			array('topic_id' => $topic->id),
			array(
				'order'     => 'create_time ASC',
				'condition' => 'status IN (:st1, :st2)',
				'params'    => array(
					':st1' => ForumAnswer::STATUS_PUBLIC,
					':st2' => ForumAnswer::STATUS_DELETED_SOFT,
				)
			)
		);



		$this->render(
			'//social/forum/topic',
			array(
				'topic'       => $topic,
				'answers'     => $answers,
				'modelAnswer' => $modelAnswer,
			),
			false,
			array(
				'forum',
				array(
					'sections' => $this->getSections(),
					'activeSection' => $topic->section_id,
					'categoryName' => ForumSection::model()->findByPk($topic->section_id)->name
				)
			)
		);
	}

	/**
	 * Создание новой темы
	 */
	public function actionCreate()
	{
		$model = new ForumTopic;


		if (isset($_POST['ForumTopic'])) {

			if (Yii::app()->user->isGuest)
				$model->setScenario('guest');

			$model->attributes = $_POST['ForumTopic'];

			$ip = Yii::app()->request->getUserHostAddress();

			if($ip)
			{
				$model -> author_ip = ip2long($ip);
			}

			if (Yii::app()->user->isGuest) {
				// Если гость
				$model->author_id = 0;
				$model->status = ForumAnswer::STATUS_MODERATING;
				if ($model->save()) {
					$model->saveFiles();

					$viewName = '//social/forum/createAfterSaveGuest';
					goto the_end;
				}
			} else {
				// Если авторизованный
				$model->author_id = Yii::app()->user->id;
				$model->status = ForumAnswer::STATUS_PUBLIC;

				if ($model->save()) {
					$model->saveFiles();

					$this->redirect($model->getElementLink());
				}
			}

		} else {
			$model->section_id = null;
		}


		$viewName = '//social/forum/create';

		the_end:
		$this->render(
			$viewName,
			array(
				'sections' => $this->getSections(),
				'model' => $model
			),
			false,
			array(
				'forum',
				array(
					'sections' => $this->getSections(),
					'h1' => 'Добавление темы'
				)
			)
		);
	}

	/**
	 * Удаляет ответ автора
	 * @param $id ID ответа, который нужно удалить
	 */
	public function actionDelAnswer($id)
	{
		$success = false;
		$errorMsg = '';

		$model = ForumAnswer::model()->findByPk((int)$id);
		if ( ! $model) {
			$errorMsg = 'Model does not exist';
			goto the_end;
		}
		if ($model->author->id != Yii::app()->user->id) {
			$errorMsg = 'Permision denied';
			goto the_end;
		}


		if ($model->status == ForumAnswer::STATUS_DELETED || $model->status == ForumAnswer::STATUS_DELETED_SOFT) {
			$errorMsg = 'Already deleted';
			goto the_end;
		}


		$model->status = ForumAnswer::STATUS_DELETED_SOFT;
		if ($model->save()) {
			$success = true;
		} else {
			$errorMsg = 'Deleting error';
			goto the_end;
		}



		the_end:
		die(json_encode(array(
			'success' => $success,
			'errorMsg' => $errorMsg
		)));
	}

	/**
	 * Удаляет топик гостя
	 * @param $id ID топика, который нужно удалить
	 */
	public function actionDelTopicGuest($id, $section_id)
	{
		$success = false;
		$errorMsg = '';

		$model = ForumTopic::model()->findByAttributes(array(
			'id'         => intval($id),
			'section_id' => intval($section_id),
			'status'     => ForumTopic::STATUS_MODERATING
		));
		if ( ! $model) {
			$errorMsg = 'Permision denied';
			goto the_end;
		}


		if ($model->status == ForumTopic::STATUS_DELETED) {
			$errorMsg = 'Already deleted';
			goto the_end;
		}


		$model->status = ForumTopic::STATUS_DELETED;
		if ($model->save()) {
			$success = true;
		} else {
			$errorMsg = 'Deleting error';
			goto the_end;
		}



		the_end:
		die(json_encode(array(
			'success' => $success,
			'errorMsg' => $errorMsg
		)));
	}


	/**
	 * Получаем параметры сортировки
	 */
	private function getFilter()
	{
		$filter = array();

		// Тип соритровки
		$filter['sorttype'] = Yii::app()->request->getParam('sorttype');
		if ($filter['sorttype'] != ForumTopic::SORT_TYPE_ANSWER)
			$filter['sorttype'] = ForumTopic::SORT_TYPE_TIME;


		// Направление сортировки
		$filter['sortdirect'] = Yii::app()->request->getParam('sortdirect');
		if ($filter['sortdirect'] != ForumTopic::SORT_DIRECT_UP)
			$filter['sortdirect'] = ForumTopic::SORT_DIRECT_DOWN;


		return $filter;
	}

	/**
	 * Получаем order по фильтру
	 */
	private function getCriteriaOrder($filter)
	{
		if ($filter['sorttype'] == ForumTopic::SORT_TYPE_TIME)
			$order = 'create_time';
		else
			$order = 'count_answer';

		if ($filter['sortdirect'] == ForumTopic::SORT_DIRECT_UP)
			$order = $order.' ASC';
		else
			$order = $order.' DESC';


		return $order;
	}

	/**
	 * Добавляет новый ответ к топику с идентификатором $topic_id
	 *
	 * @param $topic_id int ID топика
	 * @return ForumAnswer
	 */
	private function addAnswer($topic_id)
	{
		$model = new ForumAnswer();

		if (Yii::app()->request->getParam('action') != 'create')
			return $model;

		$ip = Yii::app()->request->getUserHostAddress();

		if($ip)
		{
			$model -> author_ip = ip2long($ip);
		}

		$model->topic_id = $topic_id;

		if (Yii::app()->user->isGuest) {
			// Если гость
			$model->author_id = 0;
			$model->status = ForumAnswer::STATUS_MODERATING;
		} else {
			// Если авторизованный
			$model->author_id = Yii::app()->user->id;
			$model->status = ForumAnswer::STATUS_PUBLIC;
		}



		if (isset($_POST['ForumAnswer'])) {
			$model->attributes = $_POST['ForumAnswer'];

			if ($model->save()) {
				$model->saveFiles();

				$this->redirect('#answer');
			}
		}

		return $model;
	}

	/**
	 * Обновляет ответ пользователя, принадлежащий теме $topic_id
	 * @param $topic_id
	 * @throws CHttpException
	 */
	private function updateAnswer($topic_id)
	{
		if (Yii::app()->request->getParam('action') != 'update')
			return;

		$model = ForumAnswer::model()->findByPk((int)$_POST['item_id']);
		if ( ! $model)
			throw new CHttpException(404);

		if ($model->author_id != Yii::app()->user->id)
			throw new CHttpException(403);

		if ($model->topic_id != $topic_id)
			throw new CHttpException(404);

		if (isset($_POST['ForumAnswer'])) {
			$model->attributes = $_POST['ForumAnswer'];

			$ip = Yii::app()->request->getUserHostAddress();

			if($ip)
			{
				$model -> author_ip = ip2long($ip);
			}

			if ($model->save()) {
				$model->saveFiles();

				// Удаляем указанные файлы
				if (isset($_POST['ForumAnswer']['forDelete']) && !empty($_POST['ForumAnswer']['forDelete']))
				{
					foreach($_POST['ForumAnswer']['forDelete'] as $fileID)
					{
						ForumFile::model()->deleteAllByAttributes(array(
							'item_id' => $model->id,
							'file_id' => (int)$fileID,
							'type' => ForumFile::TYPE_ANSWER
						));
					}
				}

				$this->redirect('#'.$model->id);
			}

		}

	}


	/**
	 * Список "моих" созданных топиков
	 */
	public function actionMytopics()
	{
		// Получаем и устанавливаем кол-во элементов на странице
		$pageSize = $this->getPageSize();


		// Получаем параметры
		$filter = $this->getFilter();

		// Формируем критерии выборки с учетом фильтра
		$order = $this->getCriteriaOrder($filter);


		// Формируем выдачу с учетом фильтра
		$criteria = new CDbCriteria(array(
			'order'     => $order,
			'condition' => 't.status = :st AND t.author_id = :aid',
			'params'    => array(':st' => ForumTopic::STATUS_PUBLIC, ':aid' => Yii::app()->user->id)
		));

		$count = ForumTopic::model()->count($criteria);
		$pages = new CPagination($count);

		$pages->pageSize = $pageSize;
		$pages->applyLimit($criteria);


		// Провайдер списка тем
		$topicProvider = new CActiveDataProvider('ForumTopic', array(
			'criteria' => $criteria,
			'pagination' => $pages
		));

		if ($topicProvider->getTotalItemCount() > 0)
			$view = '//social/forum/mytopics';
		else
			$view = '//social/forum/mytopicsEmpty';

		$this->render(
			$view,
			array(
				'topicProvider' => $topicProvider,
				'pageSize' => $pageSize,
				'filter' => $filter
			),
			false,
			array(
				'forum',
				array(
					'sections' => $this->getSections(),
					'h1' => 'Мои темы'
				)
			)
		);
	}

	/**
	 * Список топиков, в которых оставлял комментарии.
	 */
	public function actionMyanswers()
	{
		// Получаем и устанавливаем кол-во элементов на странице
		$pageSize = $this->getPageSize();


		// Получаем параметры
		$filter = $this->getFilter();

		// Формируем критерии выборки с учетом фильтра
		$order = $this->getCriteriaOrder($filter);


		// Формируем выдачу с учетом фильтра
		$criteria = new CDbCriteria(array(
			'order'     => $order, //'t.create_time DESC',
			'condition' => 't.status = :st',
			'join'      => 'INNER JOIN (SELECT topic_id FROM forum_answer WHERE author_id = :aid GROUP BY topic_id) as tmp ON tmp.topic_id = t.id',
			'params'    => array(':st' => ForumTopic::STATUS_PUBLIC, ':aid' => Yii::app()->user->id)
		));

		$count = ForumTopic::model()->count($criteria);
		$pages = new CPagination($count);

		$pages->pageSize = $pageSize;
		$pages->applyLimit($criteria);


		// Провайдер списка тем
		$topicProvider = new CActiveDataProvider('ForumTopic', array(
			'criteria' => $criteria,
			'pagination' => $pages
		));

		if ($topicProvider->getTotalItemCount() > 0)
			$view = '//social/forum/myanswers';
		else
			$view = '//social/forum/myanswersEmpty';

		$this->render(
			$view,
			array(
				'topicProvider' => $topicProvider,
				'pageSize' => $pageSize,
				'filter' => $filter
			),
			false,
			array(
				'forum',
				array(
					'sections' => $this->getSections(),
					'h1' => 'Мои ответы'
				)
			)
		);
	}

	/**
	 * Страница поиска
	 */
	public function actionSearch()
	{
		$text = Yii::app()->request->getParam('text');

		// Получаем и устанавливаем кол-во элементов на странице
		$pageSize = $this->getPageSize();


		$sphinxClient = Yii::app()->search;
		$sphinxQuery  = $sphinxClient->EscapeString(preg_replace('/[^\w -]+/u', '', $text));

		$dataProvider = new CSphinxDataProvider($sphinxClient, array(
			'index'          => 'forum_topic',
			'modelClass'     => 'ForumTopic',
			'query'          => $sphinxQuery.'*',
			'filters'        => array('status' => ForumTopic::STATUS_PUBLIC),
			'matchMode'      => SPH_MATCH_ALL,
			'pagination'     => array('pageSize' => $pageSize),
		));


		if ($dataProvider->getTotalItemCount() > 0)
			$view = '//social/forum/search';
		else
			$view = '//social/forum/searchEmpty';
		$this->render(
			$view,
			array(
				'pageSize' 	=> $pageSize,
				'dataProvider'  => $dataProvider,
				'text'	   	=> $text,
				'sphinxQuery'   => $sphinxQuery
			),
			false,
			array(
				'forum',
				array(
					'sections' => $this->getSections(),
					'h1' => 'Поиск'
				)
			)
		);
	}


	/**
	 * Получает и возвращает значение элементов на странице
	 * @return int
	 */
	private function getPageSize()
	{
		if (Yii::app()->request->getParam('pagesize'))
			$pageSize = Yii::app()->request->getParam('pagesize');
		else
			$pageSize = Yii::app()->session->get('forum_pagesize');

		$pageSize = empty(Config::$forumPageSizes[(int)$pageSize]) ? key(Config::$forumPageSizes) : (int)$pageSize;
		Yii::app()->session->add('forum_pagesize', $pageSize);

		return $pageSize;
	}

	/**
	 * Возвращает список всех разделов форму для навигации в левой колонке
	 * @return array
	 */
	private function getSections()
	{
		$sections = ForumSection::model()->findAllByAttributes(
			array('status' => ForumSection::STATUS_PUBLIC),
			array('order' => 'create_time DESC')
		);

		return $sections;
	}

	/**
	 * Возваращает json данных с похожими темами для поисковой строки
	 */
	public function actionAjaxSimilarTopic()
	{
		$sphinxClient = Yii::app()->search;
		$sphinxQuery  = $sphinxClient->EscapeString($_POST['value']);

		$html = '';
		$qntTopics = 0;

		if (mb_strlen($sphinxQuery, 'UTF-8') >= 3) {
			$dataProvider = new CSphinxDataProvider($sphinxClient, array(
				'index'          => 'forum_topic',
				'modelClass'     => 'ForumTopic',
				'filters'        => array('status' => ForumTopic::STATUS_PUBLIC),
				'query'          => $sphinxQuery.'*',
				'matchMode'      => SPH_MATCH_ALL,
				'pagination' => array('pageSize' => 10),
			));

			$qntTopics = $dataProvider->getTotalItemCount();

			if ($qntTopics > 0)
				$html = $this->renderPartial('//social/forum/_similarTopics', array('topics' => $dataProvider->getData()), true);
		}


		die(json_encode(array(
			'success' => true,
			'html' => $html,
			'qnt' => $qntTopics
		)));

	}
}