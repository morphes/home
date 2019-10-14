<?php
/**
 * Вывод списка проектов (аналог IdeasWall)
 * User: desher
 * Date: 05.04.12
 * Time: 9:29
 */
class ProjectListWidget extends CWidget
{
	public $projects = null;
	public $galleryAdditionalClass = '';
	public $renderCounters = false;

	public function init()
	{

	}

	public function run()
	{
		if(!$this->projects)
			return false;

		$this->render('item', array(
			'projects'=>$this->projects,
			'galleryAdditionalClass' => $this->galleryAdditionalClass,
			'renderCounters'=>$this->renderCounters,
		));
	}
}
