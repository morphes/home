<?php

/**
 * Интерфейс для моделей проектов пользователей (напр. Интерьеры, Портфолио и т.д.)
 */
interface IProject
{
        public function getUpdateLink();
        public function getDeleteLink();
        public function scopeOwnPublic($user_id = null, $sid = null);
        public function getIsOwner();
        public function updateProjectCountByService();
        public function countUserServiceRating();
        public function getPreview($config);
        public function afterSave();

	/**
	 * Url проекта в профиле пользователя
	 * @abstract
	 * @return mixed
	 */
	public function getElementLink();

	/**
	 * Название типа проекта
	 * @abstract
	 * @return mixed
	 */
	public function getProjectType();

	/**
	 * Получение системного типа объекта (например Config::INTERIOR)
	 * @return mixed
	 */
	public function getTypeId();
	/**
	 * Подключение сортировки проектов, требуется добавление вызова в afterSave
	 */
	public function initSorting();
	public function getPhotos();
}