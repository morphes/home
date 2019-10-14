<?php

class TenderForm extends CWidget
{
	/**
	 * @var Tender 
	 */
	public $tender = null;
	public $user = null; // Возможно не указывать

	public function init()
	{
		Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-1.8.18.custom.min.js', CClientScript::POS_BEGIN);
		Yii::app()->clientScript->registerCssFile('/css/jquery-ui-1.8.18.custom.css');
		if (! $this->tender instanceof Tender)
			throw new CException('Invalid tender');
	}
	
	public function run()
	{
		// Список городов
		if ( empty($this->tender->city_id) ) {
			if (!is_null($this->user) && !is_null($this->user->city_id)) {
				$this->tender->city_id = $this->user->city_id;
			}
		}
		
		$city = $this->tender->getCity();
		$cityName = is_null($city) ? '' : $city->name;

		$expireLabel =  '3 дня';
		if ( !empty($this->tender->expire) ) {
			$dtime = $this->tender->getDaysToExpire();
			$expireLabel = CFormatterEx::formatNumeral($dtime, array('день', 'дня', 'дней'));
		}
		
		// Доп материалы
		$files = array();
		if (!$this->tender->getIsNewRecord()) {
			$files = Yii::app()->db->createCommand()->select('uf.id, uf.name, uf.ext, uf.size, tender_file.desc')
				->from('uploaded_file as uf')
				->join('tender_file', 'tender_file.file_id=uf.id')
				->where('tender_file.tender_id=:tid', array(':tid'=>  $this->tender->id))
				->queryAll();
		}
		
		if (Yii::app()->user->fileApiSupport) {
			$renderView = 'form';
		} else {
			$renderView = 'simpleForm';
		}
		
		//$renderView = 'simpleForm';
		
		$this->render($renderView, array(
			'tender' => $this->tender,
			'cityName' => $cityName,
			'files' => $files,
			'expireLabel' => $expireLabel,
		));
	}
}
