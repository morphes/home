<?php
class SimilarEvents extends CWidget
{
	public $limit = 3;
	public $eventId = null;

	public function init()
	{
		if (empty($this->eventId))
			throw new CException('Invalid event id');
	}

	public function run()
	{
		$key = __CLASS__.':'.$this->eventId.':'.$this->limit;

		$data = Yii::app()->cache->get($key);

		if (empty($data)) {
			$sql = 'SELECT me.id, me.name as event_name, tmp1.city_id, tmp2.theme_id, met.name as type_name, '
				.'city.name as city_name, city.country_id, me.start_time, me.end_time '
				.'FROM media_event as me '
				.'INNER JOIN ( '
					.'SELECT event_id, city_id FROM media_event_place as mep WHERE city_id IN ( SELECT city_id FROM media_event_place WHERE event_id=:eid ) AND mep.event_id<>:eid '
					.'GROUP BY event_id '
				.') as tmp1 ON tmp1.event_id=me.id '
				.'INNER JOIN ( '
					.'SELECT model_id, theme_id FROM media_theme_select as mts WHERE model="MediaEvent" AND model_id<>:eid AND theme_id IN ( SELECT theme_id FROM media_theme_select WHERE model="MediaEvent" AND model_id=:eid ) '
					.'GROUP BY model_id '
				.') as tmp2 ON tmp2.model_id=me.id '
				.'INNER JOIN media_event_type as met ON met.id = me.event_type '
				.'INNER JOIN city ON city.id = tmp1.city_id '
				//.'INNER JOIN media_theme as mt ON mt.id=tmp2.theme_id '
				.'ORDER BY me.create_time DESC '
				.'LIMIT '.intval($this->limit);

			$events = Yii::app()->db->createCommand($sql)->bindParam(':eid', $this->eventId)->queryAll();
			$data = '';

			if (!empty($events)) {
				$data .= CHtml::openTag('div', array('class'=>'articles_block'));
				$data .= CHtml::tag('h3', array('class'=>'arch_name'), 'Похожие события');

				foreach ($events as $event) {
					$data .= $this->render('item', array('event'=>$event), true);
				}

				$data .= CHtml::closeTag('div');
			}
			Yii::app()->cache->set($key, $data, Cache::DURATION_REAL_TIME);

		}
		echo $data;
	}
}