<?php
interface FileInterface
{
	/**
	 * Положить файл по указанному пути
	 * @return mixed
	 */
	public function putFile($src, $dest);

	/**
	 * Получает файл по указанному пути, сохраняет во временное
	 * хранилище и возвращает путь до него
	 * @param $path
	 * @return mixed
	 */
	public function getFile($path);

	/**
	 * Получение свойств файла
	 * @param $path
	 * @return mixed
	 */
	public function getProperties($path);

	/**
	 * Удаление файла
	 * @param $path
	 * @return mixed
	 */
	public function deleteFile($path);

	/**
	 * Перемещение файла
	 * @param $path
	 * @param $dest
	 * @return mixed
	 */
	public function moveFile($src, $dest);

	/**
	 * Копирование файла
	 * @param $src
	 * @param $dest
	 * @return mixed
	 */
	public function copyFile($src, $dest);

	/**
	 * Чтение директории
	 * @param $path
	 * @return mixed
	 */
	public function listDir($path);

	/**
	 * Установка настроек транспорта файлов
	 * @param $config array
	 * @return bool
	 */
	public function setOptions($config);
}