<?php

/**
 * Виджет выводит баннер для текущей страницы
 * @see http://doc.myhome.ru/p/site-banners
 */
class BannerWidget extends CWidget
{
	/**
	 * @var CController объект контроллера, обрабатывающего запрос
	 * нужен для автоопределения текущего раздела сайта
	 */
	public $controller;

	/**
	 * @var integer раздел сайта, в котором показывается баннер
	 * если указан, то переданный $controller игнорируется
	 */
	public $section;

	/**
	 * @var integer Тип баннера (см. BannerItem::TYPE_*)
	 */
	public $type;

	/**
	 * @var City город посетителя
	 */
	private $city;

	public function init()
	{
		Yii::import('admin.models.BannerItem');
		Yii::import('admin.models.BannerItemSection');

		if ( !$this->type || !in_array($this->type, array_keys(BannerItem::$typeLabels)) )
			throw new Exception('Не укзаан тип баннера');

		if ( !$this->section && !$this->controller )
			throw new Exception('Не указан раздел сайта для ротации баннера');

		// определение раздела сайта, если он явно не указан
		if ( !$this->section || !in_array($this->section, Config::$sections) )
			$this->section = BannerItemSection::getSection($this->controller);

		if ( $this->section === false )
			return false;

		// определение города посетителя (если не определили, то выставляем Москву)
		$this->city = Yii::app()->user->getSelectedCity();
		if ( !$this->city )
			$this->city = Yii::app()->user->getDetectedCity();
	}

	public function run()
	{

		if ( $this->section === false )
			return false;

		// выборка баннеров, подходящих под текущие условия
		// (регион посетителя, раздел сайта, тип баннера, время посещения)
		$condition = '( section_id=:sid OR section_id=:allsid ) AND type_id=:tid'
				. ' AND start_time<:now AND end_time>:now'
				. ' AND status=:st'
				. ' AND ( city_id=:cid OR region_id=:rid OR country_id=:coid )';

		$params = array(
			':sid'=>$this->section,           // section_id
			':allsid'=>Config::SECTION_ALL, // all sections
			':tid'=>$this->type,              // type_id
			':now'=>time(),                   // now time
			':cid'=>$this->city->id,          // city_id
			':rid'=>$this->city->region_id,   // region_id
			':coid'=>$this->city->country_id, // country_id
			':st'=>BannerItem::STATUS_ACTIVE, // country_id
		);

		$command = Yii::app()->db;
		$banners = $command->createCommand()->select('item_id, tariff_id')
			->from('banner_rotation')->where($condition, $params)->queryAll();

		if ( !$banners ) {
			if ($this->type === BannerItem::TYPE_HORIZONTAL)
				$this->render('banner_google');
				//$this->render('banner_admitad');
			return false;
		}


		$bannerSegments = array();

		$lastPoint = 0;
		foreach ( $banners as $banner ) {
			$endPoint = $lastPoint + $banner['tariff_id'];
			$bannerSegments[ $banner['item_id'] ] = array('start'=>$lastPoint, 'end'=>$endPoint);
			$lastPoint = $endPoint;
		}

		$rndPoint = rand(0, $endPoint);
		$rndBanner = null;

		// если случайное значение равно верхней крайней границе - выдаем последний баннер в сегменте
		// т.к. известно, что его крайняя граница равна крайней границе сегмента
		if ( $rndPoint == $endPoint ) {
			$rndBanner = key(end($bannerSegments));

		// иначе - обход по всем сегментам и поиск вхождения случайной точки в какой-либо сегмент
		} else {
			foreach ( $bannerSegments as $key=>$segment ) {
				if ( $segment['start'] <= $rndPoint && $segment['end'] > $rndPoint ) {
					$rndBanner = $key;
					break;
				}
			}
		}

		// получение объекта баннера
		$bannerItem = BannerItem::model()->findByPk($rndBanner);

		$htmlcode = false;


		if ( $bannerItem && !empty($bannerItem->htmlcode) )
			$htmlcode = $bannerItem->htmlcode;

		if ( !$bannerItem || ( !$bannerItem->swf_file_id && !$bannerItem->file_id && empty($bannerItem->htmlcode) ) )
			return false;

		Yii::app()->redis->incr(BannerItem::REDIS_STAT_VIEWS_VAR . $bannerItem->id);

		// рендеринг представления баннера
		// представление определяется по типу баннера
		// if ( $this->type === BannerItem::TYPE_HORIZONTAL)
		// 	$this->render('banner_h', array('model'=>$bannerItem, 'htmlcode'=>$htmlcode));
		// if ( $this->type === BannerItem::TYPE_VERTICAL)
		// 	$this->render('banner_v', array('model'=>$bannerItem, 'htmlcode'=>$htmlcode));

		// return false;
	}

}

?>