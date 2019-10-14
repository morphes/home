<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alexsh
 * Date: 04.09.12
 * Time: 12:05
 * To change this template use File | Settings | File Templates.
 */
class ReviewController extends FrontController
{
	public function filters()
	{
		return array('accessControl');
	}



	protected function beforeAction($action)
	{
		$this->bodyClass = 'profile reviews';
		return parent::beforeAction($action);
	}

	/**
	 * @brief Разрешает доступ
	 * @return array
	 */
	public function accessRules()
	{

		return array(
			array(
				'allow',
				'actions' => array('create', 'delete', 'createanswerajax', 'editanswerajax'),
				'roles'   => array(
					User::ROLE_ADMIN,
					User::ROLE_JUNIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_SPEC_FIS,
					User::ROLE_SPEC_JUR,
					User::ROLE_USER
				),
			),
			array(
				'allow',
				'actions' => array('list'),
				'users'   => array('*'),
			),
			array(
				'deny',
				'users' => array('*'),
			),
		);
	}


	/**
	 * Отзывы в профиле пользователя
	 */
	public function actionList()
	{
		$errors = false;

		$post = Yii::app()->request->getPost('Review');

		if (isset($post['message']) && !empty($_POST)) {
			$request = Yii::app()->getRequest();

			$userId = $request->getParam('userId');

			// юзер, на которого оставляется отзыв
			$user = User::model()->findByPk($userId);

			$currentUser = Yii::app()->user->model;

			$reviewModel = $this->_create($currentUser, $user, $post);
		} else {
			$reviewModel = Review::model();
		}

		$user = Cache::getInstance()->user;

		$view = Yii::app()->getRequest()->getParam('view', 'all');
		if (!$user instanceof User || !in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR)))
			throw new CHttpException(404);

		//Если профиль просматривает не владелец
		//то наращиваем счетчик просмотров
		if ($user->id !== Yii::app()->user->id) {
			StatSpecialist::hit($user->id, StatSpecialist::TYPE_HIT_PROFILE);
		}

		$condition = '';
		$params = array();

		$reviews = Review::model()->findAllByAttributes(
			array(
				'spec_id' => $user->id,
				'status'  => Review::STATUS_SHOW,
			),
			array(
				'order'     => 'case when type = ' . Review::TYPE_ANSWER . ' then parent_id else id end DESC, create_time ASC',
				'condition' => $condition,
				'params'    => $params,
			)
		);

		/* При открытии страницы отзывов, сбрасыаем счетчик непрочитанных
		отзывов, оставленных текущему пользователю */
		Yii::app()->redis->delete(User::getRedisKeyUnreadReview($user->id));

		$this->render('list',
			array(
				'user'        => $user,
				'reviews'     => $reviews,
				'view'        => $view,
				'errors'      => $errors,
				'reviewModel' => $reviewModel
			),
			false,
			array('profileSpecialist', array('user' => $user))
		);
	}


	/**
	 * ВНИМАНИЕ НАДО ВЫПИЛИТЬ
	 * Добавление отзыва
	 */
	public function actionCreate()
	{
		$request = Yii::app()->getRequest();

		$userId = $request->getParam('userId');
		$action = $request->getParam('action');

		// юзер, на которого оставляется отзыв
		$user = User::model()->findByPk($userId);

		if (!$user instanceof User || !$request->getIsAjaxRequest())
			throw new CHttpException(404);

		$currentUser = Yii::app()->user->model;
		if (!$currentUser instanceof User)
			throw new CHttpException(404);
		if (!in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR)))
			die (CJSON::encode(array('error' => true, 'message' => 'Invalid user role')));

		switch ($action) {
			case 'createReview':
			{
				if ($currentUser->id == $user->id)
					throw new CHttpException(404);
				if (!$currentUser->hasSocial()) // нет привязанных соц. сетей
				die (CJSON::encode(array('error' => true, 'message' => 'No social networks')));
				if (Review::hasReview($user->id, $currentUser->id))
					die (CJSON::encode(array('error' => true, 'message' => 'Отзыв уже оставлен')));
				/** @var $review Review */
				$review = new Review();
				$review->status = Review::STATUS_SHOW;
				$review->spec_id = $user->id;
				$review->type = Review::TYPE_REVIEW;
				$review->author_id = $currentUser->id;
				$review->message = (string)$request->getParam('message', '');

				if ($request->getParam('recommend', false)) {
					$review->rating = Review::RATING_RECOMMEND;
				} elseif ($request->getParam('mark', false)) {
					$review->rating = Review::RATING_PLUS;
				} else {
					$review->rating = Review::RATING_MINUS;
				}

				if ($review->validate()) {
					$review->save(false);

					/*
					 * Послед сохранения отзыва, нарищиваем
					 * счетчик непрочитанных отзывов для
					 * того пользователя, которому оставили отзыв
					 */
					Yii::app()->redis->incr(User::getRedisKeyUnreadReview($review->spec_id));


					$html = $this->renderPartial('_reviewItem', array('review' => $review, 'author' => $currentUser), true);
					die (CJSON::encode(array('success' => true, 'html' => $html)));
				}
				$errors = $review->getErrors();
				$message = '';
				foreach ($errors as $key => $val) {
					$message .= $review->getError($key) . '<br />';
				}

				die (CJSON::encode(array('error' => true, 'message' => $message)));
			}
				break;
			case 'updateReview':
			{
				if ($currentUser->id == $user->id)
					throw new CHttpException(404);
				$reviewId = $request->getParam('reviewId');
				$review = Review::model()->findByAttributes(array('id' => $reviewId, 'status' => Review::STATUS_SHOW, 'parent_id' => 0, 'author_id' => $currentUser->id, 'spec_id' => $user->id));
				if (is_null($review))
					throw new CHttpException(404);

				if ($request->getParam('recommend', false)) {
					$review->rating = Review::RATING_RECOMMEND;
				} elseif ($request->getParam('mark', false)) {
					$review->rating = Review::RATING_PLUS;
				} else {
					$review->rating = Review::RATING_MINUS;
				}

				$review->message = (string)$request->getParam('message', '');
				if ($review->validate()) {
					$review->save(false);
					die (CJSON::encode(array('success' => true, 'message' => 'So good')));
				}
				$errors = $review->getErrors();
				$message = '';
				foreach ($errors as $key => $val) {
					$message .= $review->getError($key) . '<br />';
				}

				die (CJSON::encode(array('error' => true, 'message' => $message)));
			}
				break;
			case 'createAnswer':
			{
				$reviewId = $request->getParam('reviewId');
				$parentReview = Review::model()->findByAttributes(array('id' => $reviewId, 'status' => Review::STATUS_SHOW, 'parent_id' => 0, 'spec_id' => $currentUser->id));
				if (is_null($parentReview))
					throw new CHttpException(404);
				$review = new Review();
				$review->status = Review::STATUS_SHOW;
				$review->type = Review::TYPE_ANSWER;
				$review->rating = $parentReview->rating;
				$review->spec_id = $currentUser->id;
				$review->author_id = $currentUser->id;
				$review->parent_id = $parentReview->id;
				$review->message = $request->getParam('message', '');

				if ($review->validate()) {
					$review->save(false);
					$html = $this->renderPartial('_reviewCommentItem', array('review' => $review), true);
					die (CJSON::encode(array('success' => true, 'html' => $html)));
				}
				$errors = $review->getErrors();
				$message = '';
				foreach ($errors as $key => $val) {
					$message .= $review->getError($key) . '<br />';
				}

				die (CJSON::encode(array('error' => true, 'message' => $message)));
			}
				break;
			case 'updateAnswer':
			{
				$reviewId = $request->getParam('reviewId');
				$review = Review::model()->findByAttributes(array('id' => $reviewId, 'status' => Review::STATUS_SHOW, 'type' => Review::TYPE_ANSWER, 'author_id' => $currentUser->id, 'spec_id' => $currentUser->id));
				if (is_null($review))
					throw new CHttpException(404);
				$review->message = $request->getParam('message', '');

				if ($review->validate()) {
					$review->save(false);
					die (CJSON::encode(array('success' => true, 'message' => 'So good')));
				}
				$errors = $review->getErrors();
				$message = '';
				foreach ($errors as $key => $val) {
					$message .= $review->getError($key) . '<br />';
				}

				die (CJSON::encode(array('error' => true, 'message' => $message)));
			}
				break;
			default:
				throw new CHttpException(404);
		}

		throw new CHttpException(404);
	}


	/**
	 * Удаление отзыва
	 * @throws CHttpException
	 */
	public function actionDelete()
	{
		$reviewId = Yii::app()->getRequest()->getParam('reviewId');
		$review = Review::model()->findByPk($reviewId);

		if (is_null($review) || $review->status != Review::STATUS_SHOW)
			throw new CHttpException(404);

		if (!$review->checkAccess())
			throw new CHttpException(403);


		if ($review->parent_id == 0) {
			$relReview = Review::model()->findByAttributes(array('parent_id' => $review->id, 'status' => Review::STATUS_SHOW, 'type' => Review::TYPE_ANSWER));
			if (!is_null($relReview)) {
				$relReview->status = Review::STATUS_DELETED;
				$relReview->save(false);
			}
		}

		$review->status = Review::STATUS_DELETED;
		$review->save(false);

		die (CJSON::encode(array('success' => true)));
	}


	/**
	 * Метод для создания ответа
	 * на комменаттарий
	 * @throws CHttpException
	 */
	public function actionCreateAnswerAjax()
	{

		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$currentUser = Yii::app()->user->model;

		$post = Yii::app()->request->getPost('item');

		$reviewId = (int)$post['parrentId'];

		$parentReview = Review::model()->findByAttributes(array('id' => $reviewId, 'status' => Review::STATUS_SHOW, 'parent_id' => 0, 'spec_id' => $currentUser->id));
		if (is_null($parentReview))
			throw new CHttpException(404);

		if (count($post['message']) > 0) {
			$review = new Review();
			$review->status = Review::STATUS_SHOW;
			$review->type = Review::TYPE_ANSWER;
			$review->rating = $parentReview->rating;
			$review->spec_id = $currentUser->id;
			$review->author_id = $currentUser->id;
			$review->parent_id = $parentReview->id;
			$review->message = $post['message'];

			$review->save(false);

			die (CJSON::encode(array('success' => true)));
		} else {
			die (CJSON::encode(array('success' => false)));
		}
	}


	/**
	 * Редактирование ответа на отзыв
	 * @throws CHttpException
	 */
	public function actionEditAnswerAjax()
	{

		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$currentUser = Yii::app()->user->model;

		$post = Yii::app()->request->getPost('item');

		$id = (int)$post['id'];

		$review = Review::model()->findByAttributes(array('id' => $id, 'status' => Review::STATUS_SHOW, 'type' => Review::TYPE_ANSWER, 'author_id' => $currentUser->id, 'spec_id' => $currentUser->id));

		if (is_null($review))
			throw new CHttpException(404);

		if (count($post['message']) > 0) {
			$review->message = $post['message'];
			$review->save(false);
			die (CJSON::encode(array('success' => true)));
		} else {
			die (CJSON::encode(array('success' => false)));
		}
	}


	/**
	 * Создание
	 * отзыва,
	 * @param $currentUser
	 * @param $user
	 * @param $request
	 *
	 * @throws CHttpException
	 */
	private function _create($currentUser, $user, $post)
	{

		if ($currentUser->id == $user->id)
			throw new CHttpException(404);

		if (Review::hasReview($user->id, $currentUser->id)) {
			return;
		}

		/** @var $review Review */
		$review = new Review();
		$review->setAttributes($post);
		$review->status = Review::STATUS_SHOW;
		$review->spec_id = $user->id;
		$review->type = Review::TYPE_REVIEW;
		$review->author_id = $currentUser->id;
		$review->message = (string)$post['message'];

		if ($review->validate()) {
			$review->save(false);

			$review->setImageType('review');

			$files = UploadedFile::loadImages($review, 'attach');

			foreach ($files as $file) {
				$rUModel = new ReviewUploadedfile();
				$rUModel->file_id = $file->id;
				$rUModel->item_id = $review->id;
				$rUModel->save(false);
			}

			/*
			 * Послед сохранения отзыва, нарищиваем
			 * счетчик непрочитанных отзывов для
			 * того пользователя, которому оставили отзыв
			 */
			Yii::app()->redis->incr(User::getRedisKeyUnreadReview($review->spec_id));

		}
		$errors = $review->getErrors();

		$message = '';
		foreach ($errors as $key => $val) {
			$message .= $review->getError($key) . '<br />';
		}

		return $review;
	}
}
