<?php
/**
 * Класс-компонент для создания openGraph тегов на странице.
 * Эти теги используются соцсетями для сбора инфы о расшариваемой ссылке.
 *
 * Для включения тегов на странице нужно в любое представление написать примерно такой код.
 *
 * Yii::app()->openGraph->title = "Заголовок страницы";
 * Yii::app()->openGraph->description = "Любое описание, которое вам понравиться";
 * Yii::app()->openGraph->image = 'http://site.ru/img/test.jpg';
 *
 * User: serg
 * Date: 12/13/12
 * Time: 1:25 PM
 *
 */
class OpenGraph extends  CApplicationComponent
{
	private $_tags = array();


	public function init()
	{

	}

	/**
	 * Добавляет тег к коллекции
	 * @param $type Имя совйства для meta тега
	 * @param $value Значение тега
	 */
	public function addTag($type, $value)
	{
		$this->_tags[] = array('type' => $type, 'value' => $value);
	}

	/**
	 * Рендерит все добавленные добавленные meta теги
	 */
	public function renderTags()
	{
		if ( ! empty($this->_tags)) {


			foreach ($this->_tags as $tag) {
				Yii::app()->clientScript->registerMetaTag($tag['value'], null, null, array('property' => $tag['type']));

			}
		}

	}

	/**
	 * Устанавливаем значение для тега og:title
	 * @param $value string
	 */
	public function setTitle($value)
	{
		$this->_tags[] = array('type' => 'og:title', 'value' => $value);
	}

	/**
	 * Устанавливаем значение для тега og:description
	 * @param $value string
	 */
	public function setDescription($value)
	{
		$this->_tags[] = array('type' => 'og:description', 'value' => $value);
	}

	/**
	 * Устанавливаем значение для тега og:image
	 * @param $value string
	 */
	public function setImage($value)
	{
		$this->_tags[] = array('type' => 'og:image', 'value' => $value);
	}

}
