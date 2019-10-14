<?php
/**
 * Интерфейс для моделей, выводящихся в активности пользователя
 */
interface IActivity
{
	/**
	 * Возвращает фрагмент активности для вывода
	 * @abstract
	 * @return string
	 */
	public function renderActivityItem($user);

	/**
	 * Получение ID автора
	 * @abstract
	 * @return mixed
	 */
	public function getAuthorId();
}