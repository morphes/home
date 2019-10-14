<?php

class FavoriteController extends FrontController
{
	// ID выбранной группы
	private $selectedGroupId = null;

	public function init()
	{
		Yii::import('application.modules.idea.models.*');
	}

	public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {
                return array(
			array('allow',
				'actions' => array('guest', 'additem', 'removeitem', 'shared'),
				'users' => array('*'),
			),
			array('allow',
				'users' => array('@'),
			),
			array('deny',
				'users' => array('*'),
			),
                );
        }

	protected function beforeAction($action)
	{
		$this->bodyClass = 'profile favorite-page -affix';
		return parent::beforeAction($action);
	}


	public function getSelectedGroupId()
	{
		return $this->selectedGroupId;
	}

	/**
	 * Главная страница раздела "Избранное"
	 *
	 * @return string
	 */
	public function actionIndex($id = null)
	{
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile', 'favorite-page');

		$this->menuIsActiveLink = true;
		$this->menuActiveKey = '';

		/** @var $user User */
		$user = Yii::app()->user->model;

		if ( ! is_null($id))
			$this->selectedGroupId = (int)$id;
		else
			$this->selectedGroupId = 0;

		// Получаем список групп
		$command = Yii::app()->db->createCommand();

		$command->select("id, name");
		$command->from(FavoriteGroup::model()->tableName());
		$command->where('author_id = :id', array(':id' => $user->id));
		$command->order = 'create_time ASC';

		$groups = $command->queryAll();

		// --начало "Для шаринга избранного"

		$shared = Shared::findShared(Shared::TYPE_FAVORITE, $this->selectedGroupId, $user->id);
		if ( !$shared )
			$shared = Shared::createShared(Shared::TYPE_FAVORITE, $this->selectedGroupId, $user->id);

		$publicUrl = $shared->getUrl();

		if ( $this->selectedGroupId ) {
			$groupName = Yii::app()->db->createCommand()
				->select('name')
				->from(FavoriteGroup::model()->tableName())
				->where('id=:id', array(':id'=>$this->selectedGroupId))->queryScalar();
		} else {
			$groupName = 'Общий список';
		}
		// -- конец "Для шаринга избранного"

		// Получаем кол-во избранных элементов в каждой группе
		$command = Yii::app()->db->createCommand();

		$command->select('favoritegroup_id as group_id, COUNT(*) as cnt');
		$command->from(FavoriteItem::model()->tableName());
		$command->where('author_id = :id', array(':id' => $user->id));
		$command->group('favoritegroup_id');

		$arr = $command->queryAll();


		// Формируем ассоциативный массив  array('id списка' => 'кол-во элементов', ...)
		$itemsCnt = array();
		if ($arr) {
			foreach($arr as $item) {
				$itemsCnt[ $item['group_id'] ] = $item['cnt'];
			}
		}


		if ( in_array(Yii::app()->user->role, array( User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR, User::ROLE_POWERADMIN )) ) {
			return $this->render(
				'//member/profile/specialist/favorite',
				array(
					'user' => $user,
					'groups' => $groups,
					'itemsCnt' => $itemsCnt,
					'publicUrl' => $publicUrl,
					'groupName' => $groupName,
				),
				false,
				array('profileSpecialist', array('user' => $user))
			);
		} else {
			return $this->render(
				'//member/profile/user/favorite',
				array(
					'user' => $user,
					'groups' => $groups,
					'itemsCnt' => $itemsCnt,
					'publicUrl' => $publicUrl,
					'groupName' => $groupName,
				),
				false,
				array('profileUser', array(
					'user' => $user
				))
			);
		}

	}

	public function actionGuest($id)
	{
		if ( ! Yii::app()->user->getIsGuest())
			$this->redirect(array('/users', 'login'=>Yii::app()->user->model->login, 'action'=>'favorite'));


		if ( ! is_null($id))
			$this->selectedGroupId = (int)$id;
		else
			$this->selectedGroupId = 0;

		// Получаем кол-во избранных элементов в каждой группе
		$command = Yii::app()->db->createCommand();

		$command->select('favoritegroup_id as group_id, COUNT(*) as cnt');
		$command->from(FavoriteItem::model()->tableName());
		$command->where('cookie_id = :id', array(':id' => User::getCookieId()));
		$command->group('favoritegroup_id');

		$arr = $command->queryAll();


		// Формируем ассоциативный массив  array('id списка' => 'кол-во элементов', ...)
		$itemsCnt = array();
		if ($arr) {
			foreach($arr as $item) {
				$itemsCnt[ $item['group_id'] ] = $item['cnt'];
			}
		}


		return $this->render('//member/profile/guest/favorite', array(
			'itemsCnt' => $itemsCnt,
		));
	}


	/**
	 * Создает для текущего пользователя группу с именем $name
	 *
	 * @param string $name имя группы для избранного
	 * @throws CHttpException 403 ошибка, если пользователь открыл ссылку
	 *	не AJAX вызовом.
	 */
	public function actionCreate($name = '')
	{
		if ( ! Yii::app()->request->isAjaxRequest)
			throw new CHttpException(403);

		$name = CHtml::encode($name);

		$model = new FavoriteGroup();
		$model->name = $name;
		$model->author_id = Yii::app()->user->id;

		if ($model->save()) {
			die(CJSON::encode(array(
				'success' => true,
				'id'	  => $model->id,
			)));
		} else {
			$html = ($model->getError('name'))
				? 'Имя не может быть пустым'
				: '';
			die(CJSON::encode(array(
				'success' => false,
				'html' => $html
			)));
		}
	}

	/**
	 * Удаление группы избранного с идентификатором $id
	 *
	 * @param $id
	 * @throws CHttpException 403 ошибка, если пользователь открыл ссылку
	 *	не AJAX вызовом
	 */
	public function actionDelete($id)
	{
		if ( ! Yii::app()->request->isAjaxRequest)
			throw new CHttpException(403);

		$id = (int)$id;
		$group = FavoriteGroup::model()->findByPk($id);

		$user_id = Yii::app()->user->id;

		// Если существует группа, и она принадлежит текущему пользователю
		if ($group && $group->author_id == $user_id)
		{
			// Удалаем все ссылки группы
			FavoriteItem::model()->deleteAllByAttributes(array(
				'author_id'        => $user_id,
				'favoritegroup_id' => $group->id
			));

			Yii::app()->cache->delete(FavoriteItem::getCacheKey());

			$group->delete();

			die(CJSON::encode(array(
				'success' => true
			)));
		}

		die(CJSON::encode(array(
			'success' => false
		)));
	}

	/**
	 * Обновляет название существующей группы
	 *
	 * @param integer $id Идентификатор группы избранного
	 * @param string $name Новое имя
	 * @throws CHttpException 403 Если вызов не AJAX'ом
	 */
	public function actionUpdate($id = null, $name = '')
	{
		if ( ! Yii::app()->request->isAjaxRequest)
			throw new CHttpException(403);

		$id = (int)$id;
		$name = CHtml::encode($name);
		$group = FavoriteGroup::model()->findByPk($id);

		if ($id && $name && $group && $group->author_id == Yii::app()->user->id) {
			FavoriteGroup::model()->updateByPk($id, array('name' => $name));

			die(CJSON::encode(array(
				'success' => true
			)));
		}

		die(CJSON::encode(array(
			'success' => false
		)));
	}

	/**
	 * Добавляет в группу избранных указанный элемент.
	 *
	 * @param $groupid Идентификатор группы
	 * @param $itemid Идентификатор элемента модели $itemmodel
	 * @param $itemmodel Имя модели для идентификатора $itemid
	 */
	public function actionAdditem($groupid, $itemid, $itemmodel)
	{
		Yii::import('application.modules.idea.models.*');
		Yii::import('application.modules.media.models.*');
                Yii::import('application.modules.catalog.models.*');

		$data = Yii::app()->request->getParam('data');

		// Предполагаем, что ошибок нет.
		$success = true;
		$error = '';
		// Флаг необходимость обновить страницу после добавления
		// элемента в избранное
		$needReloadPage = false;

		// Получаем входные данные
		$groupid = (int)$groupid;
		$itemid = (int)$itemid;
		$itemmodel = CHtml::encode($itemmodel);

		// Проверяем существование группы избранного
		if (Yii::app()->user->getIsGuest()) {
			if ($groupid > 0 && ! FavoriteGroup::model()->findByAttributes(array('cookie_id' => User::getCookieId(), 'id' => $groupid))) {
				$success = false;
				$error = 'Неверный список избранного';
			}
		} else {
			if ($groupid > 0 && ! FavoriteGroup::model()->findByAttributes(array('author_id' => Yii::app()->user->id, 'id' => $groupid))) {
				$success = false;
				$error = 'Неверный список избранного';
			}
		}

		if ($success) {
			// Проверяем существование элемента, который нужно
			// добавить в избранное
			$model = $itemmodel::model()->findByPk($itemid);
			if ( ! $model) {
				$success = false;
				$error = 'Указанный элемент невозможно добавить в избранное';
			}
		}

		// Если группа существует и модель, которую нужно добавить в избранное
		if ($success) {
			$item = new FavoriteItem();
			if (Yii::app()->user->getIsGuest())
				$item->cookie_id = User::getCookieId();
			else
				$item->author_id = Yii::app()->user->id;

			$item->model = $itemmodel;
			$item->model_id = $itemid;
			$item->favoritegroup_id = $groupid;
			$item->setData($data);

			if ( ! $item->save()) {
				$success = false;
				$error = 'Ошибка добавления в избранное';
			}
		}

		// Если нет ошибок, то пересчитываем кол-во элементов в избранном
		// и записываем его в кеш
		if ($success) {
			if (Yii::app()->user->getIsGuest())
				$count = FavoriteItem::countFavorite('guest', User::getCookieId());
			else
				$count = FavoriteItem::countFavorite('auth', Yii::app()->user->id);

			Yii::app()->cache->set(FavoriteItem::getCacheKey(), $count, Cache::DURATION_FAVORITE_COUNT);


			if (Yii::app()->session->get('last_favorite_group') != $groupid) {
				// Запоминаем в сессию последнюю выбранную категорию
				Yii::app()->session->add('last_favorite_group', $groupid);

				$needReloadPage = true;
			}

			//Если профиль просматривает не владелец
			//И автор идеи специалист
			//то наращиваем счетчик просмотров
			if($model instanceof Interior || $model instanceof Interiorpublic || $model instanceof Architecture || $model instanceof Portfolio)
			{
				$authorId = $model->author_id;
				if(yii::app()->user->id !== $authorId)
				{
					$author = User::model()->findByPk($model->author_id);

					if (in_array($author->role, array( User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR )))
					{
						StatProject::hit($model->id, get_class($model), $author->id, StatProject::TYPE_PROJECT_TO_FAVORITES);
						Yii::app()->gearman->appendJob('userService', array('userId'=>$authorId));
					}
				}
			}
		}

		die(CJSON::encode(array(
			'success' => $success,
			'error' => $error,
			'needReloadPage' => $needReloadPage
		)));
	}

	public function actionRemoveitem($itemid, $itemmodel)
	{
		// Предполагаем, что ошибок нет.
		$success = true;
		$error = '';

		// Получаем входные данные
		$itemid = (int)$itemid;
		$itemmodel = CHtml::encode($itemmodel);


		// Проверяем существование избранного
		if (Yii::app()->user->getIsGuest()) {
			if ( ! FavoriteItem::model()->deleteAllByAttributes(array('cookie_id' => User::getCookieId(), 'model' => $itemmodel, 'model_id' => $itemid))) {
				$success = false;
				$error = 'Ошибка при удалении элемента из избранного';
			}
		} else {
			if ( ! FavoriteItem::model()->deleteAllByAttributes(array('author_id' => Yii::app()->user->id, 'model' => $itemmodel, 'model_id' => $itemid))) {
				$success = false;
				$error = 'Ошибка при удалении элемента из избранного';
			}
		}

		// Если нет ошибок, то пересчитываем кол-во элементов в избранном
		// и записываем его в кеш
		if ($success) {
			if (Yii::app()->user->getIsGuest())
				$count = FavoriteItem::countFavorite('guest', User::getCookieId());
			else
				$count = FavoriteItem::countFavorite('auth', Yii::app()->user->id);

			Yii::app()->cache->set(FavoriteItem::getCacheKey(), $count, Cache::DURATION_FAVORITE_COUNT);
		}

		die(CJSON::encode(array(
			'success' => $success,
			'error' => $error
		)));
	}

	/**
	 * Рендерит список элементов добавленных в избранное.
	 * Для каждого типа избранного изпользуется свое представление с именем
	 * _favorite<$type>
	 *
	 * @param string $type Тип избранного. Один из вариантов Config::$favoriteType
	 * @return mixed
	 * @throws CHttpException 500 Если указали недопустимый тип элементов в избранном
	 */
	public function renderFavoriteList($type = '')
	{
		Yii::import('application.modules.idea.models.*');
		Yii::import('application.modules.media.models.*');
		Yii::import('application.modules.catalog.models.*');

		// Если не установлен ID группы избранного
		if (is_null($this->selectedGroupId)) {
			return;
		}

		// Проверяем на доспустимый тип Избранного
		if ( ! array_key_exists($type, Config::$favoriteType)) {
			throw new CHttpException(500, 'Ошибка вывода списка избранного');
		}


		$criteria = new CDbCriteria();

		// гостевое избранное
		if (Yii::app()->user->getIsGuest())
		{
			$criteria->condition = 'cookie_id = :id AND model = :model AND favoritegroup_id = 0';
			$criteria->params = array(':id' => User::getCookieId(),':model' => $type,);

		// пользовательское избранное
		} else {
			$criteria->condition = 'author_id = :id AND model = :model AND favoritegroup_id = :group_id';
			$criteria->params = array(':id' => Yii::app()->user->id,':model' => $type,':group_id' => $this->selectedGroupId);
		}

		/** @var $items FavoriteItem[] */
		$items = FavoriteItem::model()->findAll($criteria);

		$models = array();
		foreach ($items as $item) {
			$models[] = $item->getFavoriteObject();
		}

		$this->renderPartial('//member/profile/specialist/_favorite'.$type, array(
			'models' => $models,
			'items'  => $items,
		));
	}

	public function actionShared($hash)
	{
		$this->layout = '//layouts/new_main';
		$this->bodyClass = 'favorites ';

		Yii::import('application.modules.idea.models.*');
		Yii::import('application.modules.media.models.*');
		Yii::import('application.modules.catalog.models.*');

		$shared = Shared::getSharedId($hash);

		if ( !$shared )
			throw new CHttpException(404);

		if ( $shared->object_id == 0) {

			$groupName = 'Общий список';
		} else {

			$group = FavoriteGroup::model()->findByPk($shared->object_id);
			if ( !$group )
				throw new CHttpException(404);

			$groupName = $group->name;
		}

		$user = User::model()->findByPk($shared->user_id);
		if ( !$user )
			throw new CHttpException(404);

		$items = FavoriteItem::model()->findAllByAttributes(
			array(
				'author_id'=>$user->id,
				'favoritegroup_id'=>$shared->object_id,
			)
		);

		$this->render('shared', array(
			'groupName'=>$groupName,
			'user'=>$user,
			'items'=>$items,
		));
	}
}