<?php
/**
 * Created by JetBrains PhpStorm.
 * User: serg
 * Date: 8/14/12
 * Time: 5:54 PM
 * To change this template use File | Settings | File Templates.
 */
class MediaFilterBar extends CWidget
{
	// Кол-во элементов в выборке
	public $totalItemCount = 0;
	// Тематики
	public $themes = array();
	// Фильтр
	public $filter = array();
	// Кол-во элементов на странице
	public $pageSize;
	// Тип сортировки
	public $sortType;

	// Название представления
	public $view;

	public function init()
	{
		if ( ! in_array($this->view, array('knowledge', 'new')))
			throw new CHttpException(500, 'Неизвестное представление');

	}

	public function run()
	{
		$this->render($this->view, array());
	}
}
