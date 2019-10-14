<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alexsh
 * Date: 05.03.13
 * Time: 14:59
 * To change this template use File | Settings | File Templates.
 */
class MainVendor extends CWidget
{
	const VENDOR_LIMIT = 10;

	public $roomId;
	public $categoryId;

	private $vendors;

	private $stop=false;

	public function init()
	{
		$criteria = new CDbCriteria();
		$criteria->condition = 't.status=:st AND t.type_id=:tid AND t.start_time<:time AND t.end_time>:time';
		$criteria->limit = self::VENDOR_LIMIT;
		$criteria->order = 'RAND()';
		$criteria->params = array(
			':st'=>MainUnit::STATUS_ENABLED,
			':tid'=>MainUnit::TYPE_VENDOR,
			':time'=>time(),
		);

		if (!empty($this->roomId)) {
			$criteria->join = 'INNER JOIN cat_main_unit_room as ur ON ur.unit_id=t.id AND ur.room_id=:rid';
			$criteria->params[':rid'] = $this->roomId;


		} elseif (!empty($this->categoryId)) {
			$criteria->join = 'INNER JOIN cat_main_unit_category as uc ON uc.unit_id=t.id AND uc.category_id=:cid';
			$criteria->params[':cid'] = $this->categoryId;
		} else {
			$this->stop = true;
			return;
		}

		$this->vendors = MainUnit::model()->findAll($criteria);
		if (empty($this->vendors))
			$this->stop = true;
	}

	public function run()
	{
		if ($this->stop)
			return;

		echo CHtml::openTag('div', array('class'=>'-col-12 -gutter-top-dbl -gutter-bottom-dbl '));
		echo CHtml::openTag('ul', array('class'=>'-menu -menu-inline -cat-manufacturers-list'));

		foreach ($this->vendors as $unitVendor) {
			$this->render('_item', array('unitVendor'=>$unitVendor));
		}

		echo CHtml::closeTag('ul');
		echo CHtml::closeTag('div');
	}

}
