<?php
//Виджет для добавления товара в папку.

class AddToFolder extends CWidget
{
	//Список папок пользователя
	private $folders;

	//Модель для работы со списком папок
	private $model;

	//Id пользователя
	private $userId;

	//Id модели на котороую повеша кнопку
	public $modelId;

	//view для виджета
	public $view = 'addToFolder';

	// Была ли форма для виджета уже отрисована
	private static $isRender = false;

	public function init()
	{
		$this->model = CatFolders::model();
		$this->userId = Yii::app()->user->id;
		$this->folders = $this->model->getFoldersByUserId($this->userId);
	}


	public function run()
	{
		$this->render($this->view, array('modelId' => $this->modelId));

		if(!self::$isRender)
		{
			$this->render('form',array(
				'folders' => $this->folders,
				'modelId' => $this->modelId));
			self::$isRender = true;
		}

	}
}