<?php

/**
 * @brief Виджет выводит список дизайнеров на главной сайта
 */
class UDesigner extends CWidget
{
	// current number, read from redis
	public $pageNumber = 0;
	
        private $unit = null;
        private $settings = null;
        
        const LARGE_QT = 1;
        const SMALL_QT = 6;
	const CACHE_VERSIONS_COUNT = 15;
	private $cacheVersionKey = '';
	private $disabled = false;
	private $usedItems = array();

        public function init()
        {
		Yii::import('application.modules.member.models.Service');
                $this->unit = Unit::getUnitSettings('designer');

                if (!$this->unit && !empty($this->unit->data))
                        throw new CHttpException(500, 'Юнит не инициализирован');

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
			switch ($this->settings['unitSettings']['largeOutput']) {
				case Unit::OUTPUT_TYPE_RANDOM:
					$largeData = $this->randomSelect($this->settings['unitData'], self::LARGE_QT, array(Unit::STATUS_LARGE, Unit::STATUS_SMALL_LARGE));
					break;
				default : 
					$largeData = $this->randomSelect($this->settings['unitData'], self::LARGE_QT, array(Unit::STATUS_LARGE, Unit::STATUS_SMALL_LARGE));
					break;
			}

			switch ($this->settings['unitSettings']['smallOutput']) {
				case Unit::OUTPUT_TYPE_RANDOM:
					$smallData = $this->randomSelect($this->settings['unitData'], self::SMALL_QT, array(Unit::STATUS_SMALL, Unit::STATUS_SMALL_LARGE));
					break;
				default :
					$smallData = $this->randomSelect($this->settings['unitData'], self::SMALL_QT, array(Unit::STATUS_SMALL, Unit::STATUS_SMALL_LARGE));
					break;
			}

			$popularService = Service::model()->findAllByAttributes(
				array('popular' => Service::POPULAR_YES),
				'parent_id > 0'
			);

			$data['data'] = Yii::app()->controller->renderPartial('//widget/unit/designer', array(
				'unit' => $this->unit,
				'settings' => $this->settings['unitSettings'],
				'largeData' => $largeData,
				'smallData' => $smallData,
				'popularService' => $popularService,
			), true);
			$data['dependValue'] = $this->unit->update_time;
			
			Yii::app()->cache->set($this->cacheVersionKey, $data, Cache::DURATION_MAIN_PAGE);
		}

		// В кэшированном блоке подставляем кол-во специалистов
		$data['data'] = str_replace('##specialist_quantity##', User::getSpecialistsQuantity(), $data['data']);

		echo $data['data'];
        }

        private function randomSelect($array = array(), $number_keys = 1, $statuses = array(Unit::STATUS_SMALL_LARGE))
        {
                if (empty($array))
                        return array();

                $sorted_array = array();
		
                foreach ($array as $key => $value) {
                        if (empty($this->usedItems[$key]) && in_array($value['status'], $statuses)) {
                                $sorted_array+=array($key => $value);
			}
                }
		
		if (empty ($sorted_array))
			return $sorted_array;

                if (count($sorted_array) < $number_keys)
                        $number_keys = count($sorted_array);

                $keys = array_rand($sorted_array, $number_keys);
                if (is_array($keys)) {
			foreach ($keys as $key) {
				$this->usedItems[$key] = 1;
			}
                        return array_intersect_key($sorted_array, array_flip($keys));
		} else {
			$this->usedItems[$keys] = 1;
                        return array($keys=>$sorted_array[$keys]);
		}
        }

}
