<?php

class LikeController extends Controller
{
	public function actionIndex()
	{
		$this->render('index');
	}

	/**
	 * Добавляет лайк к элементу
	 *
	 * @param $itemid Идентификатор элемента модели $itemModel
	 * @param $itemModel Имя модели для идентификатора $itemid
	 */
	public function actionAjaxAddItem()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		Yii::import('application.modules.media.models.*');

		// Предполагаем, что ошибок нет.
		$success = true;
		$error = '';

		// Получаем входные данные
		$itemId = (int)Yii::app()->request->getPost('itemId');
		$itemModel = CHtml::encode(Yii::app()->request->getPost('itemModel'));

		if (!in_array($itemModel, array('MediaNew', 'MediaKnowledge'))) {
			throw new CHttpException(400);
		}

		// Проверяем существование элемента, который нужно лайкнуть
		$model = $itemModel::model()->findByPk($itemId);
		if ( ! $model) {
			$success = false;
			$error = 'Указанный элемент невозможно лайкнуть';
		}



		// Если элемент существует
		if ($success) {
			$item = new LikeItem();

			//получаем id пользователя.
			//Если пользователь - гость, то вместо id используем его CookieId
			if(Yii::app()->user->isGuest)
			{
				$item->author_id = 0;
				if(Yii::app()->cookieStorage->getCookieId())
				{
					$item->guest_id = Yii::app()->cookieStorage->getCookieId();
				}
				else
				{
					$item->guest_id = 1;
				}
			} else {
				$item->author_id = Yii::app()->user->id;
			}

			$item->model = $itemModel;
			$item->model_id = $itemId;
			$item->create_time=time();

			if ( ! $item->save()) {
				$success = false;
				$error = 'Ошибка выполнения операции';
			}
		}

		die(CJSON::encode(array(
			'success' => $success,
			'error' => $error,
		)));
	}

	/**
	 * Удаляет лайк с элемента
	 */
	public function actionAjaxRemoveItem()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		Yii::import('application.modules.media.models.*');

		// Предполагаем, что ошибок нет.
		$success = true;
		$error = '';


		// Получаем входные данные
		$itemId = (int)Yii::app()->request->getPost('itemId');
		$itemModel = CHtml::encode(Yii::app()->request->getPost('itemModel'));

		//получаем id пользователя.
		//Если пользователь - гость, то вместо id используем его CookieId
		if(Yii::app()->user->isGuest)
		{
			$userId = 0;
			if(Yii::app()->cookieStorage->getCookieId())
			{
				$guestId = Yii::app()->cookieStorage->getCookieId();
			}
			else
			{
				$guestId = 1;
			}
		} else {
			$userId = Yii::app()->user->id;
			$guestId = 0;
		}

		if (!LikeItem::model()->deleteAllByAttributes(array(
				'author_id' => $userId,
				'guest_id'  => $guestId,
				'model'     => $itemModel,
				'model_id'  => $itemId
			)
		)
		) {
			$success = false;
			$error = 'Ошибка при удалении элемента из избранного';
		}

		die(CJSON::encode(array(
			'success' => $success,
			'error' => $error,
		)));
	}
}