<?php
class MediaEventBar extends CWidget
{
	public $dataProvider = null;
	public $viewType = 0;
	public $pageSize = null;
	public $sortType = null;
	public $sortDirect = null;
	public $cityId = 0;
	public $startTime = null;
	public $endTime = null;

	public function init()
	{
		if (is_null($this->dataProvider))
			throw new CException('Invalid data provider');
	}

	public function run()
	{
		$eventTypes = MediaEventType::model()->findAllByAttributes(array('status'=>MediaEventType::STATUS_ACTIVE), array('order'=>'name ASC'));
		$themes = MediaTheme::model()->findAllByAttributes(array('status'=>MediaTheme::STATUS_ACTIVE));
		if (empty($this->startTime))
			$this->startTime = mktime( 0,0,0,date( "m" ),date( "d"),date("y" ) );

		$this->render('eventBar', array(
			'eventTypes' => $eventTypes,
			'themes' => $themes,
			'dataProvider' => $this->dataProvider,
			'startTime' => $this->startTime,
			'endTime' => $this->endTime,
			'cityId' => $this->cityId,
			'viewType' => $this->viewType,
			'pageSize' => $this->pageSize,
			'sortType' => $this->sortType,
			'sortDirect' => $this->sortDirect,
		));
	}
}