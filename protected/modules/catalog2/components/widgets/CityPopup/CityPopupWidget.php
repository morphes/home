<?php
/**
 * Виджет для управления отображением попапа города в каталоге товаров
 */
class CityPopupWidget extends CWidget
{
	public $city;
	public $renderHtml = false;
	public $cityUrlPos = 2; // Позиция города в адресе
	public $cookieName = Geoip::COOKIE_GEO_SELECTED; // Название куки с городом

	public $changeText = 'Будут показаны товары только в вашем городе';
	public $htmlOptions = array('class'=>'-col-4 -text-align-right -inset-top -compat-mode');

	private $_html='';

	public function init()
	{
		parent::init();
	}

	public function run()
	{
		$params = array(
			'city'       => $this->city,
			'geoCity'    => Yii::app()->getUser()->getDetectedCity(),
			'cityUrlPos' => $this->cityUrlPos,
			'cookieName' => $this->cookieName,
			'changeText' => $this->changeText,
			'htmlOptions' => $this->htmlOptions,
		);
		if ($this->renderHtml) {
			$this->render('view', $params);
		} else {
			$this->_html = '<noindex>'.$this->render('view', $params, true).'</noindex>';
		}
	}

	public function getHtml()
	{
		return $this->_html;
	}
}
