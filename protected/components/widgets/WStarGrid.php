<?php

/**
 * @brief This widget render a few stars.
 */
class WStarGrid extends CWidget
{

	/**
	 * @var integer Задает число звездочек, которое нужно раскрасить слева-направо.
	 */
	public $selectedStar = 0;

	/**
	 * @var integer Задает максимальное количетсво звезд для отображения
	 */
	public $maxStar = 5;

	// Классы для иконок, отвечающие за внешний вид зевздочек.
	public $itemClass = '-icon-star';
	public $itemClassEmpty = '-icon-star-empty';

	/**
	 *
	 * @var boolean Флаг отображения рейтинга числом.
	 */
	public $showNumRating = false;

	/**
	 * @var array описание рейтинга
	 */
	public $labels = array();

        /**
         * @var null доп. текст внутри span перед выводом звезд
         */
        public $innerText = null;


        public function init()
	{
		if (!is_integer($this->maxStar))
			throw new CException(__CLASS__ . ': Input params are incorrect');
	}

	public function run()
	{
		$htmlOut = '';

		if (!is_null($this->innerText)) {
			$htmlOut .= $this->innerText;
		}
		// Округляем рейтинг до целых
		$selStar = floor($this->selectedStar);

		for ($k = 1; $k <= $this->maxStar; $k++) {

			if ($k <= $selStar) {
				$htmlOut .= CHtml::tag('i', array('class' => $this->itemClass . ' -icon-only -red'), '', true);
				$htmlOut .= ' ';
			} else {
				$htmlOut .= CHtml::tag('i', array('class' => $this->itemClassEmpty . ' -icon-only -gray'), '', true);
				$htmlOut .= ' ';
			}
		}

		$starsQt = round($this->selectedStar, 1);

		if ($this->showNumRating) {
			$htmlOut .= ' ' . $starsQt;
		}

		if (!empty($this->labels)) {
			$htmlOut .= isset($this->labels[$starsQt])
				? CHtml::tag('span', array('class' => '-gutter-left-hf -small -gray'), ' ' . $this->labels[$starsQt])
				: '';
		}

		echo $htmlOut;
	}

}

?>