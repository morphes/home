<?php

/**
 * @brief Виджет выводит список идей на главной сайта
 */
class UIdea extends CWidget
{

	// current number, read from redis
	public $pageNumber = 0;
	
        private $unit = null;
        private $settings = null;
        
	const IMG_COUNT = 1;
	const CACHE_VERSIONS_COUNT = 10;
	private $cacheVersionKey = '';
	private $disabled = false;

        public function init()
        {
                $this->unit = Unit::getUnitSettings('idea');

                if (is_null($this->unit) || empty($this->unit->data))
                        throw new CException('Invalid unit');
		
		Yii::import('application.modules.idea.models.*');
		if ($this->unit->status == Unit::STATUS_DISABLED) {
			$this->disabled = true;
			return;
		}
		$cacheVersion = $this->pageNumber % self::CACHE_VERSIONS_COUNT;
		$this->cacheVersionKey = get_class($this) . '_' . $cacheVersion;

        }

        public function run()
        {
		if ($this->disabled)
			return;

		$data = Yii::app()->cache->get($this->cacheVersionKey);

		if( !$data || $data['dependValue'] != $this->unit->update_time ) {

			$this->settings = unserialize($this->unit->data);
			switch ($this->settings['unitSettings']['output']) {
				case Unit::OUTPUT_TYPE_RANDOM:
					$data = $this->randomSelect($this->settings['unitData'], self::IMG_COUNT, array(Unit::STATUS_ENABLED));
					break;
				default : 
					$data = $this->randomSelect($this->settings['unitData'], self::IMG_COUNT, array(Unit::STATUS_ENABLED));
					break;
			}


			$data['data'] = Yii::app()->controller->renderPartial('//widget/unit/idea', array(
				'unit' => $this->unit,
				'settings' => $this->settings['unitSettings'],
				'data' => $data,
			), true);
			$data['dependValue'] = $this->unit->update_time;

			Yii::app()->cache->set($this->cacheVersionKey, $data, Cache::DURATION_MAIN_PAGE);
		}

		// В кешированном блоке подставляем количество идей
		$data['data'] = str_replace('##idea_quantity##', Idea::getIdeasPhotoQuantity(), $data['data']);

		echo $data['data'];
        }

        private function randomSelect($array = array(), $number_keys = 1, $statuses = array(Unit::STATUS_ENABLED))
        {
                if (empty($array))
                        return array();

                $sorted_array = array();
		
                foreach ($array as $key => $value) {
                        if (in_array($value['status'], $statuses))
                                $sorted_array[$key] = $value;
                }

                if (count($sorted_array) < $number_keys)
                        $number_keys = count($sorted_array);

                $keys = array_rand($sorted_array, $number_keys);

                if (is_array($keys)) {
                        $result = array_intersect_key($sorted_array, array_flip($keys));
                        shuffle($result);
                        return $result;
		} else
                        return array($keys=>$sorted_array[$keys]);
        }

}
