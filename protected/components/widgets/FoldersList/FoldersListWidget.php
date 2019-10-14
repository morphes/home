<?php
class FoldersListWidget extends CWidget
{
	public $userId = false;

	//Массив объектов
	public $items;

	//Вариант с большими итемами
	// во view
	public $bigItems = false;

	public $view = '//widget/folders/items';

	public function init()
	{

	}

	public function run()
	{
		Yii::app()->controller->renderPartial($this->view, array(
			'items' => $this->items,
		));

	}

}
