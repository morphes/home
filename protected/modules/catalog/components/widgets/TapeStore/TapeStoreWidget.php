<?php
class TapeStoreWidget extends CWidget
{
	public $itemLimit = 3;

	public $categoryId;
	public $cityId;

	private $items;
	private $stop=false;
	public $additionalClass = '-gutter-top-dbl';

	public function init()
	{
		$city = Yii::app()->user->getSelectedCity();
		if (!$city)
			$city = Yii::app()->user->getDetectedCity();

		$criteria = new CDbCriteria();
		$criteria->condition = 't.status=:st AND t.start_time<:time AND t.end_time>:time AND t.city_id=:cityid';
		$criteria->limit = $this->itemLimit;
		$criteria->order = 'RAND()';
		$criteria->params = array(
			':st'     => Tapestore::STATUS_ENABLED,
			':time'   => time(),
			':cityid' => $city->id,
		);

		if (!empty($this->categoryId)) {
			$criteria->join = 'INNER JOIN cat_tapestore_category as tc ON tc.tapestore_id = t.id AND tc.category_id = :cid';
			$criteria->params[':cid'] = $this->categoryId;
		} else {
			$this->stop = true;

			return;
		}

		$this->items = Tapestore::model()->findAll($criteria);
		if (empty($this->items)) {
			$this->stop = true;
		}
	}

	public function run()
	{
		if ($this->stop) {
			return;
		}

		echo CHtml::openTag('div', array('class'=>'-gutter-bottom-dbl stores-logo-list'));

		foreach ($this->items as $data) {
			$this->render('_item', array('data'=>$data));
		}

		if($this->categoryId>1)
		{
			$paramArray = array('cid' => $this->categoryId);
		}
		else
		{
			$paramArray = array();
		}

		echo '<div class="-col-wrap all-stores">'
			. '<a class="-pointer-right -push-right" href="'.Yii::app()->createUrl('/catalog/stores', $paramArray).'">'
			. CFormatterEx::formatNumeral(Store::getQuantity($this->categoryId, $this->cityId), array('магазин', 'магазина', 'магазинов'))
			. '</a></div>';

		echo CHtml::closeTag('div');
	}

}
