<?php

/**
 * @author alexsh
 */
class HelpBar extends CWidget
{
	public $baseId = null;
	public $articleId = null; // current article
	public $query = ''; // current query

	public function init()
	{
		parent::init();
		Yii::app()->clientScript->registerCssFile('/css/help.css');
		Yii::app()->clientScript->registerScriptFile('/js/CHelp.js');

		if (is_null($this->baseId))
			throw new CException('Invalid base path id');

	}
	
	public function run()
	{
		$sections = HelpSection::model()->findAllByAttributes(array('base_path_id'=>$this->baseId, 'status'=>HelpSection::STATUS_OPEN), array('order'=>'position ASC'));
		// TODO: проставить всем статьям id базового раздела
		
		$this->render('helpBar', array(
			'baseId' => $this->baseId,
			'sections' => $sections,
			'articleId' => $this->articleId,
			'query' => $this->query,
		));
	}
}