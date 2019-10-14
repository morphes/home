<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alexsh
 * Date: 05.03.13
 * Time: 14:59
 * To change this template use File | Settings | File Templates.
 */
class MainIdea extends CWidget
{
	const IDEA_LIMIT = 4;

	public $roomId;
	public $categoryId;

	private $ideas;

	private $stop=false;

	public function init()
	{
		$criteria = new CDbCriteria();
		$criteria->condition = 't.status=:st AND t.type_id=:tid';
		$criteria->order = 't.position ASC';
		$criteria->limit = self::IDEA_LIMIT;
		$criteria->order = 'RAND()';
		$criteria->params = array(
			':st'=>MainUnit::STATUS_ENABLED,
			':tid'=>MainUnit::TYPE_IDEA,
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

		$this->ideas = MainUnit::model()->findAll($criteria);
		if (empty($this->ideas))
			$this->stop = true;
	}

	public function run()
	{
		if ($this->stop)
			return;

		$this->render('view', array('ideas'=>$this->ideas));
	}

}
