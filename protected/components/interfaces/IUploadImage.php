<?php

/**
 * Интерфейс для моделей загрузки изображений(возможно и файлов и т.д.)
 */
interface IUploadImage
{
	/**
	 * Получение пути для сохранения файла, в формате для uploadedFile
	 * @abstract
	 * @return string | false для новых записей
	 */
	public function getImagePath();
	/**
	 * Получение имени для сохранения файла, в формате для uploadedFile
	 * @abstract
	 * @return string | false для новых записей
	 */
	public function getImageName();

	// Получение ID владельца модели
	public function getAuthorId();

	/**
	 * Проверка доступа к объекту пользователем
	 * @abstract
	 * @return bool true-имеет доступ
	 */
	public function checkAccess();

	/**
	 * Установка типа загружаемого изображения для модели
	 * @abstract
	 * @return mixed
	 */
	public function setImageType($name);

	/**
	 * Сброс установленного типа изображения
	 * @abstract
	 * @return mixed
	 */
	public function flushImageType();

	/**
	 * Конфиг для получения превью в конкретной модели
	 * @abstract
	 * @return array
	 */
	public function imageConfig();

}