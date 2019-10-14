<?php

/**
 * Description of IdeasList
 *
 * @brief Новый вывод идей с кастомизированым пагинатором
 * @author alexsh
 */
class ShowFavoriteLink extends CWidget
{
	// Флаг для прерывания работы виджета.
	private $failRun = false;

	public function init()
	{
		Yii::import('application.modules.member.models.FavoriteItem');
	}
	
	public function run()
	{
		$cacheKey = FavoriteItem::getCacheKey();

		// Кол-во элементов в избранном
		$count = Yii::app()->cache->get($cacheKey);

		if ($count === false) {

			if (Yii::app()->user->getIsGuest())
				$count = FavoriteItem::countFavorite('guest', User::getCookieId());
			else
				$count = FavoriteItem::countFavorite('auth', Yii::app()->user->id);

			Yii::app()->cache->set($cacheKey, (int)$count, Cache::DURATION_FAVORITE_COUNT);
		}


		if (Yii::app()->user->getIsGuest())
			$favoriteLink = '/member/favorite/guest/id/'.User::getCookieId();
		else
			$favoriteLink = '/users/'.Yii::app()->user->model->login.'/favorite';


		$linkClasses = array();
		$linkClasses[] = 'user-favorite';
		$linkClasses[] = '-button';
		$linkClasses[] = '-button-gray';
		$linkClasses[] = ($count == 0) ? '-icon-heart-empty' : '-icon-heart';

		echo CHtml::link('', $favoriteLink, array(
			'class'        => implode(' ', $linkClasses),
			'data-tooltip' => '-tooltip-bottom-center',
			'data-qnt'     => $count,
			'data-title'   => ($count == 0)
				? 'В избранном нет элементов'
				: 'В избранном ' . CFormatterEx::formatNumeral($count, array(
					'элемент', 'элемента', 'элементов'
				))
		));
	}

}