<?php

/**
 *
 * @author alexsh
 */
class InteriorRejectedMessage extends CWidget
{

	/**
	 * @var string JQuery селектор для поля, в котором выводится статус
	 */
	public $selectorForStatus;

	/**
	 * @var string JQuery селектор для поля, в который будет вставлен шаблон сообщения.
	 */
	public $selectorMessageField;

	/**
	 * @var string Имя автора, подставляется в шаблон сообщений
	 */
	public $authorName;

	/**
	 * @var string Название проекта, подставляется в шаблон сообщений
	 */
	public $projectName;


	public function init()
	{
		parent::init();


	}
	
	public function run()
	{
		/* ------------
		 *  РЕНДЕРИНГ
		 * ------------
		 */
		$this->render('view', array(

		));
	}
	
}