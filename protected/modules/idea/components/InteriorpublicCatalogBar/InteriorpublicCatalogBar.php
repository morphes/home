<?php

/**
 * @author alexsh
 */
class InteriorpublicCatalogBar extends CWidget
{
	// Кол-во элементов архитектуры
	public $ideaCount = 0;

	public $selected = array();

	public function init()
	{
		parent::init();


	}
	
	public function run()
	{
		// Получаем список типов строения, и задаем значение по-умолчанию, если не задано
		//$objects = IdeaHeap::getListByOptionKey(Config::INTERIOR, 0, 'object');
		$objects = IdeaHeap::model()->findAllByAttributes(array('parent_id'=>0), 'idea_type_id IN (:t1,:t2)', array(':t1'=>Config::INTERIOR, ':t2'=>Config::INTERIOR_PUBLIC));
		if ( ! $this->selected['object_type']) {
			$this->selected['object_type'] = reset($objects)->id;
		}

		// Определяем константу для типа строений и корректность указанного параметра object_type
		foreach($objects as $obj) {
			if ($obj->id == $this->selected['object_type']) {
				// Not used now
				//$objectTypeConst = IdeaHeap::getBuildTypeByName($obj->option_value, Config::INTERIOR);
				$objectTypeID = $obj->id;
				break;
			}
		}

		if ( ! isset($objectTypeID))
			throw new CException(500, 'Invalid object type');


		// Получаем все параметры для фильтра
		$buildings 	= IdeaHeap::getListByOptionKey(Config::INTERIOR_PUBLIC, $this->selected['object_type'], 'building_type');
		$styles 	= IdeaHeap::getListByOptionKey(Config::INTERIOR_PUBLIC, $this->selected['object_type'], 'style');
		$colors 	= IdeaHeap::getListByOptionKey(Config::INTERIOR_PUBLIC, $this->selected['object_type'], 'color');



		$view = 'catalogBar';

		/* ------------
		 *  РЕНДЕРИНГ
		 * ------------
		 */
		$this->render($view, array(
			'ideaCount' 	=> $this->ideaCount,
			'objects'	=> $objects,
			'buildings'	=> $buildings,
			'styles'	=> $styles,
			'colors'	=> $colors,
			'selected' 	=> $this->selected,
		));
	}
	
}