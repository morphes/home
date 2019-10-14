<?php

/**
 *
 * @author alexsh
 */
class ArchitectureCatalogBar extends CWidget
{
	// Кол-во элементов архитектуры
	public $ideaCount = 0;

	public $selected = array();

	public function init()
	{
		parent::init();
		Yii::app()->getClientScript()->registerScriptFile('/js/architectureFilter.js');

	}
	
	public function run()
	{
		// Получаем список типов строения, и задаем значение по-умолчанию, если не задано
		$objects = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, 0, 'object');
		if ( ! $this->selected['object_type']) {
			$this->selected['object_type'] = reset($objects)->id;
		}

		// Определяем константу для типа строений и корректность указанного параметра object_type
		foreach($objects as $obj) {
			if ($obj->id == $this->selected['object_type']) {
				$objectTypeConst = IdeaHeap::getBuildTypeByName($obj->option_value, Config::ARCHITECTURE);
				$objectTypeID = $obj->id;
				break;
			}
		}

		if ( ! isset($objectTypeID))
			throw new CHttpException(500, 'Invalid object type');


		// Получаем все параметры для фильтра
		$buildings 	= IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $this->selected['object_type'], 'building_type');
		$styles 	= IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $this->selected['object_type'], 'style');
		$materials 	= IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $this->selected['object_type'], 'material');
		$floors 	= IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $this->selected['object_type'], 'floor');
		$colors 	= IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $this->selected['object_type'], 'color');


		// В зависимости о группы типов строения, подключаем нужный блок с фильтром
		switch($objectTypeConst) {
			case Architecture::BUILD_TYPE_HOUSE:
				$view = 'catalogBarHouse';
				break;
			case Architecture::BUILD_TYPE_OUTBUILDING:
				$view = 'catalogBarOutbuilding';
				break;
			default:
				$view = 'catalogBar';
		}

		/* ------------
		 *  РЕНДЕРИНГ
		 * ------------
		 */
		$this->render($view, array(
			'ideaCount' 	=> $this->ideaCount,
			'objects'	=> $objects,
			'buildings'	=> $buildings,
			'styles'	=> $styles,
			'materials'	=> $materials,
			'floors'	=> $floors,
			'colors'	=> $colors,
			'selected' 	=> $this->selected,
		));
	}
	
}