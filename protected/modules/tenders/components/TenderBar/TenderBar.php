<?php

/**
 * Может понадобиться при расширении фильтра, 
 * в данный момент не имеет смысла
 *
 * @author alexsh
 */
class TenderBar extends CWidget
{
	public $countryId = null;
	public $cityId = null;
	public $tenderType = null;
	public $sortType = null;
	public $pageSize = 20;
	public $mainService = null;
	public $childService = null;
	
	public function init()
	{
		parent::init();
		Yii::app()->clientScript->registerScriptFile('/js/tenderFilter.js');
		Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-1.8.18.custom.min.js');
		Yii::app()->clientScript->registerCssFile('/css/jquery-ui-1.8.18.custom.css');
	}
	
	public function run()
	{
		// Список категорий
		$mainServices = Service::getMainItems();
		if ( empty($this->mainService) && !empty($this->childService) ) {
			$child = Service::model()->findByPk($this->childService);
			if (!is_null($child)) {
				$this->mainService = $child->parent_id;
			}
		}

		$childServices = array();
		if (!empty($this->mainService)) {
			$childServices = Service::getChildrens($this->mainService);
		}
		
		$this->render('tenderBar', array(
		    'cityId' => $this->cityId,
		    'tenderType' => $this->tenderType,
		    'sortType' => $this->sortType,
		    'pageSize' => $this->pageSize,
		    
		    'mainServices' => $mainServices,
		    'childServices' => $childServices,
		    'mainService' => $this->mainService,
		    'childService' => $this->childService,
		));
	}
}