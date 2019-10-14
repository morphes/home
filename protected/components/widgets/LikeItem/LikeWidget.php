<?php

/**
 * @brief  Новый вывод идей с кастомизированым пагинатором
 * @author alexsh
 */
class LikeWidget extends CWidget
{
	// Имя и ID модели, которую нужно лайкнуть
	// Если в параметрах не передаются, то заполняются из
	// $this->model
	public $modelName = null;
	public $modelId = null;
	public $likesCount = 0;
	public $userId = 0;

	// Флаг, обозначающий ошибку запуска.
	// При его выставлении виджет ничего не отрисует на странице.
	private $failRun = false;

	// список лайкнутых
	static public $selectedItems = null;


	public function init()
	{
		Yii::import('application.modules.member.models.*');
		// Если передана не модель CActiveRecord, то помещаем, что
		// виджет не проходит запуск.
		if (!array_key_exists($this->modelName, Config::$likeType) || is_null($this->modelId))
			$this->failRun = true;

		if (Yii::app()->user->isGuest) {
			if (Yii::app()->cookieStorage->getCookieId()) {
				$userId = Yii::app()->cookieStorage->getCookieId();
			} else {
				$userId = 1;
			}
		} else {
			$userId = Yii::app()->user->id;
		}

		self::$selectedItems = LikeItem::model()->isAdded($userId, $this->modelName, $this->modelId);
		$this->likesCount = LikeItem::model()->countLikes($this->modelName, $this->modelId);
	}


	public function run()
	{
		if ($this->failRun)
			return;

		$this->render('like', array(
			'modelName'  => $this->modelName,
			'modelId'    => $this->modelId,
			'likesCount' => $this->likesCount
		));
	}


	public function isAdded()
	{
		if (self::$selectedItems > 0)
			return true;
		else
			return false;
	}
}