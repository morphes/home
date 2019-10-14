<?php

/**
 * @brief This widget render a few stars.
 */
class WStar extends CWidget
{

	/**
	 * @var interger Задает число звездочек, которое нужно раскрасить слева-направо.
	 */
	public $selectedStar = 0;

	/**
	 * @var integer Задает максимальное количетсво звезд для отображения
	 */
	public $maxStar = 5;
	
	/**
	 *
	 * @var boolean Флаг отображения рейтинга числом.
	 */
	public $showNumRating = false;
	/**
	 *
	 * @var string Дополнительные классы для обрамляющего элемента span
	 */
	public $addSpanClass = '';

        /**
         * @var array описание рейтинга
         */
        public $labels = array();

        /**
         * @var null кол-во голосов за рейтинг (выводит в виде "5 оценок")
         */
        public $votesQt = null;

        /**
         * @var null доп. текст внутри span перед выводом звезд
         */
        public $innerText = null;

	//Использовать новую реализацию рейтинга
	public $useNewRealisation = false;

	//Выводить большие иконки
	public $largeIcon = false;

        public function init()
	{
		if (!is_integer($this->maxStar))
			throw new CException(__CLASS__ . ': Input params are incorrect');
	}

	public function run()
	{
		if ($this->useNewRealisation) {

			$selStar = floor($this->selectedStar);

			$iconStar = '-icon-star-xs';
			$iconStarEmpty = '-icon-star-empty-xs';

			if ($this->largeIcon) {
				$iconStar = '-icon-star-large -icon-only';
				$iconStarEmpty = '-icon-star-empty-large -icon-only';
			}

			for ($k = 1; $k <= $this->maxStar; $k++) {
				if ($k <= $selStar)
					echo CHtml::tag('i', array('class' => '' . $iconStar . ' -red'), '', true);
				else
					echo CHtml::tag('i', array('class' => '' . $iconStarEmpty . ' -gray'), '', true);
			}

			$starsQt = round($this->selectedStar, 1);

			if (!empty($this->labels)) {
				echo isset($this->labels[$starsQt])
					? CHtml::tag('span', array('class' => '-gray -small -gutter-left-hf'), ' ' . $this->labels[$starsQt])
					: '';
			}

		} else {

			$htmlOut = '';

			$htmlOut .= CHtml::openTag('span', array('class' => 'rating ' . $this->addSpanClass));

			if (!is_null($this->innerText)) {
				$htmlOut .= $this->innerText;
			}
			// Округляем рейтинг до целых
			$selStar = floor($this->selectedStar);

			for ($k = 1; $k <= $this->maxStar; $k++) {

				if ($k <= $selStar) {
					$htmlOut .= CHtml::tag('i', array('class' => 'active'), '*', true);
				} else {
					$htmlOut .= CHtml::tag('i', array(), '', true);
				}
			}

			$starsQt = round($this->selectedStar, 1);

			if ($this->showNumRating) {
				$htmlOut .= ' ' . $starsQt;
			}

			if (!is_null($this->votesQt)) {
				$htmlOut .= CHtml::tag('span', array(), CFormatterEx::formatNumeral($this->votesQt, array('оценка', 'оценки', 'оценок')));
			}

			if (!empty($this->labels)) {
				$htmlOut .= isset($this->labels[$starsQt])
					? CHtml::tag('span', array(), ' ' . $this->labels[$starsQt])
					: '';
			}

			$htmlOut .= CHtml::closeTag('span');

			echo $htmlOut;
		}
	}

}

?>