<?php

/**
 *
 * @author alexsh
 */
class IdeasCatalogBar extends CWidget
{
	public $objectType = null;
	public $ideaType = null;
	public $visibleRooms = 5; // число развернутых помещений
	public $ideaCount = 5;
	public $sortType;
	public $pageSize;
	public $selected = array();
	
	public function init()
	{
		parent::init();
		if ( is_null($this->sortType) || is_null($this->pageSize)) {
			throw new CException('Invalid parameters');
		}
		Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-1.8.18.custom.min.js');
		Yii::app()->clientScript->registerCssFile('/css/jquery-ui-1.8.18.custom.css');
	}
	
	public function run()
	{
		// !hardcode
		//if (is_null($this->ideaType))
		$this->ideaType = Config::INTERIOR;
		
		if (is_null($this->objectType)) {
			$this->objectType = IdeaHeap::model()->findByAttributes(array('option_key' => 'object', 'idea_type_id' => $this->ideaType));
		}
		
		//$objects = IdeaHeap::getObjects($this->ideaType);
		$objects = IdeaHeap::model()->findAllByAttributes(array('parent_id'=>0), 'idea_type_id IN (:t1,:t2)', array(':t1'=>Config::INTERIOR, ':t2'=>Config::INTERIOR_PUBLIC));
		if (is_null($this->objectType)) {
			$this->objectType = @reset($objects);
		}
		
		$rooms = IdeaHeap::getRooms($this->ideaType, $this->objectType->id);
		$styles = IdeaHeap::getStyles($this->ideaType, $this->objectType->id);
		$colors = IdeaHeap::getColors($this->ideaType, $this->objectType->id);
		
		$this->render('catalogBar', array(
			'rooms'        => $rooms,
			'styles'       => $styles,
			'colors'       => $colors,
			'objects'      => $objects,
			'objectType'   => $this->objectType,
			'visibleRooms' => $this->visibleRooms,
			'ideaType'     => $this->ideaType,
			'ideaCount'    => $this->ideaCount,
			'sortType'     => $this->sortType,
			'pageSize'     => $this->pageSize,
			'selected'     => $this->selected,
		));
	}
	
}