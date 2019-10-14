<?php

/**
 * @brief Новый вывод идей с кастомизированым пагинатором
 * @author alexsh
 */
class AddToFavorite extends CWidget
{
	// Список дополнительных классов для обрамляющего div
	public $cssClass = '';

	// Имя и ID модели, которую нужно добавить в избранное
	// Если в параметрах не передаются, то заполняются из
	// $this->model
	public $modelName = null;
	public $modelId = null;

	// Представление для сердечка
	public $viewHeart = 'favorite';

	// Флаг для удаления элемента со страницы.
	public $deleteItem = false;

	// Список всех созданных групп избранного
	static public $groups = null;

	// Список всего избранного для пользователя
	static public $selectedItems = null;

	// Флаг, обозначающий ошибку запуска.
	// При его выставлении виджет ничего не отрисует на странице.
	private $failRun = false;

	// Ключ текущий модели, который мы будем проверять среди уже выбранных.
	private $key = null;
	// Был ли виджет уже отрисован
	private static $isRender = false;

	/**
	 * Массив дополнительной информации, необходимой для добавления определенного
	 * типа объектов (например, изображения идей)
	 * @var null|array
	 */
	public $data = null;

	public function init()
	{
		Yii::import('application.modules.member.models.*');
		// Если передана не модель CActiveRecord, то помещаем, что
		// виджет не проходит запуск.
		if (
			!array_key_exists($this->modelName, Config::$favoriteType)
			|| is_null($this->modelId)
		) {
			$this->failRun = true;
		}

		if (Yii::app()->user->getIsGuest()) {
			$this->key = User::getCookieId() . ':' . $this->modelName . ':' . $this->modelId;
		} else {
			$this->key = Yii::app()->user->id . ':' . $this->modelName . ':' . $this->modelId;
		}

		if (is_null(self::$groups)) {
			// Получаем список групп избранного
			self::$groups = FavoriteGroup::model()->getGroupsByUserId(Yii::app()->user->id);
		}

		if (is_null(self::$selectedItems)) {

			self::$selectedItems = array();

			if (Yii::app()->user->getIsGuest()) {
				$arr = FavoriteItem::model()->getItemsByUserId(User::getCookieId());
			} else {
				$arr = FavoriteItem::model()->getItemsByUserId(Yii::app()->user->id);
			}

			foreach ($arr as $item) {
				self::$selectedItems[$item['k']] = true;
			}
		}
	}
	
	public function run()
	{
		if ($this->failRun) {
			return;
		}

		$defaultGroupId = 0;

		// Проверяем в какую избранную группу был последний раз добавлен элемент
		$lastFavGroup = Yii::app()->session->get('last_favorite_group');
		if ($lastFavGroup) {
			$defaultGroupId = $lastFavGroup;
		}

		echo '<noindex>';
		$this->render($this->viewHeart, array(
			'selectedItems'  => self::$selectedItems,
			'modelName'      => $this->modelName,
			'modelId'        => $this->modelId,
			'defaultGroupId' => $defaultGroupId,
			'data'		 => $this->data,
		));
		echo '</noindex>';

		if (!self::$isRender) {
			$this->render('list', array(
				'groups'         => self::$groups,
				'defaultGroupId' => $defaultGroupId,
			));
			self::$isRender = true;
		}
	}


	public function isAdded()
	{
		if (array_key_exists($this->key, self::$selectedItems))
			return true;
		else
			return false;
	}
}