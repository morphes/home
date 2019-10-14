<?php

/**
 * Виджет выводит название опции и ее значение для сокращенной карточки товара
 */
class CatFullcardOptionsGrid extends CWidget
{
	public $model;
	private $_options;

	public function init()
	{
		if(!($this->model instanceof Product))
			throw new CHttpException(500);

		/**
		 * Получение опций карточки товара с типами и значениями опций для текущего товара ($this->model)
		 */
		$this->_options = $this->model->fullcardOptions;
	}

	public function run()
	{
		/**
		 * Флаг группы опций, выводимых в данный момент в foreach
		 * Если езменяется, то в цикле выводится заголовок новой группы и продолжается вывод опций уже
		 * для новой группы
		 */
		$currentGroupId = 0;

		$column1 = array();
		$column2 = array();
		$column3 = array();

		/**
		 * Проход по опциям товара и вывод
		 */
		$selector = 1;
		foreach($this->_options as $option_id => $option)
		{

			/**
			 * Если у опции не указана группа - пропуск опции
			 */
			if(is_null($option['group_id']))
				continue;

			if(Option::$typeParams[$option['type_id']]['multiValue']) {
				$value = Value::serializeToArrray($option['value']);
				if(empty($value)) continue;
			}

			/**
			 * Пропуск опции без названий, типов или без значения
			 */
			if(empty($option['name']) || is_null($option['type_id']) || ($option['value'] !== '0' && empty($option['value'])))
				continue;

			/**
			 * Если группа опции отличается от той, что была на предыдущей итерации - добавляем в другую колонку
			 */
			if($this->model->category->groupsArray[$option['group_id']] != 'Общие' && $currentGroupId != $option['group_id'] && isset($this->model->category->groupsArray[$option['group_id']])) {
				$currentGroupId = $option['group_id'];

				switch($selector){
					case 1 :
						$column2[] = $option;
						$selector = 2;
						break;
					case 2 :
						$column3[] = $option;
						$selector = 3;
						break;
					case 3 :
						$column1[] = $option;
						$selector = 1;
						break;
					default :
						break;
				}
			}
			else {
				switch($selector){
					case 1 :
						$column1[] = $option;
						break;
					case 2 :
						$column2[] = $option;
						break;
					case 3 :
						$column3[] = $option;
						break;
					default :
						break;
				}
			}

		}

		$currentGroupId = 0;

		//Выводим колонки

		echo CHtml::openTag('div', array('class' => '-col-3'));
		?>
			<div class="-header-h3">Характеристики</div>
			<ul class="-menu-block">
				<li>
					Производитель —
					<?php echo CHtml::link($this->model->vendor->name, Vendor::getLink($this->model->vendor_id)); ?>
				</li>

				<?php if($this->model->countryObj) : ?>
					<li>
						Страна —
						<?php echo $this->model->countryObj->name; ?>
					</li>
				<?php endif; ?>

				<?php if($this->model->barcode) : ?>
					<li>
						Артикул —
						<?php echo $this->model->barcode;?>
					</li>
				<?php endif; ?>

				<?php if($this->model->collectionName) : ?>
					<li>
						Коллекция —
						<?php echo $this->model->collectionName; ?>
					</li>
				<?php endif; ?>

				<?php if($this->model->guaranty) : ?>
					<li>
						Гарантия —
						<?php echo $this->model->guaranty; ?>
					</li>
				<?php endif; ?>

				<?php if($this->model->eco) : ?>
					<li>
						Экологичность — Да
					</li>
				<?php endif; ?>


		<?php

		foreach($column1 as $cl)
		{
			if($this->model->category->groupsArray[$cl['group_id']] != 'Общие' && $currentGroupId != $cl['group_id'] && isset($this->model->category->groupsArray[$cl['group_id']])) {
				echo CHtml::closeTag('ul');
				echo CHtml::tag('div', array('class' => '-header-h3'), $this->model->category->groupsArray[$cl['group_id']]);
				$currentGroupId = $cl['group_id'];
				echo CHtml::openTag('ul', array('class' => '-menu-block'));
			}
			$this->getHtmlOption($cl);

		}
		echo CHtml::closeTag('ul');
		echo CHtml::closeTag('div');


		if($column2)
		{
			$currentGroupId = 0;
			$firstIteration = true;
			echo CHtml::openTag('div', array('class' => '-col-3'));
			foreach($column2 as $cl)
			{
				if($this->model->category->groupsArray[$cl['group_id']] != 'Общие' && $currentGroupId != $cl['group_id'] && isset($this->model->category->groupsArray[$cl['group_id']])) {
					if(!$firstIteration) {
						echo CHtml::closeTag('ul');
					}
					echo CHtml::tag('div', array('class' => '-header-h3'), $this->model->category->groupsArray[$cl['group_id']]);
					$currentGroupId = $cl['group_id'];
					echo CHtml::openTag('ul', array('class' => '-menu-block'));
					$firstIteration = false;
				}
				$this->getHtmlOption($cl);
			}
			echo CHtml::closeTag('ul');
			echo CHtml::closeTag('div');
		}


		if($column3)
		{
			echo CHtml::openTag('div', array('class' => '-col-3'));
			$firstIteration = true;
			$currentGroupId = 0;
			foreach($column3 as $cl)
			{
				if($this->model->category->groupsArray[$cl['group_id']] != 'Общие' && $currentGroupId != $cl['group_id'] && isset($this->model->category->groupsArray[$cl['group_id']])) {
					if(!$firstIteration) {
						echo CHtml::closeTag('ul');
					}
					echo CHtml::tag('div', array('class' => '-header-h3'), $this->model->category->groupsArray[$cl['group_id']]);
					$currentGroupId = $cl['group_id'];
					echo CHtml::openTag('ul', array('class' => '-menu-block'));
					$firstIteration = false;
				}
				$this->getHtmlOption($cl);
			}
			echo CHtml::closeTag('ul');
			echo CHtml::closeTag('div');
		}

	}



	private function getHtmlOption($option)
	{
		if(Option::$typeParams[$option['type_id']]['multiValue']) {
			$value = Value::serializeToArrray($option['value']);
			if(empty($value)) return;
		}

		switch($option['type_id']) {
			case Option::TYPE_INPUT :
				echo CHtml::tag('li', array(), $option['name'].' — '.$option['value']);
				break;

			case Option::TYPE_TEXTAREA :
				echo CHtml::tag('li', array(), $option['name'].' — '.$option['value']);
				break;

			case Option::TYPE_SELECT :
				$value = Value::model()->findByPk((int) $option['value']);
				if($value) {
					echo CHtml::tag('li', array(), $option['name'].' — '.$value->value);
				}
				else {

					echo CHtml::tag('li', array(), $option['name'].' — Не указано');
				}
				break;

			case Option::TYPE_CHECKBOX:
				echo CHtml::tag('span', array(), '<b>'.$option['name'].'</b>');
				if($option['value']) {
					echo CHtml::tag('li', array(), $option['name'].' —  Да');

				}
				else {
					echo CHtml::tag('li', array(), $option['name'].' —  Нет');
				}
				break;

			case Option::TYPE_SELECTMULTIPLE :
				$query = implode(',', $value);
				if(!$query) break;
				$values = Value::model()->findAll('t.id in ('.$query.')');
				$values_array = array();
				foreach($values as $val)
					$values_array[] = $val->value;
				if(!is_array($values_array)) break;

				echo CHtml::tag('li', array(), $option['name'].' — '.implode(', ', $values_array));

				break;

			case Option::TYPE_COLOR :

				echo CHtml::openTag('li');

				echo $option['name'].' — ';

				foreach($value as $v) {
					$color = CatColor::model()->findByPk((int) $v);
					echo CHtml::openTag('div', array('class' => '-color-tile '.$color->param));
					echo Chtml::openTag('p', array('class' => 'hide'));
					echo $color->name;
					echo Chtml::closeTag('p');
					echo CHtml::closeTag('div');
				}

				echo CHtml::closeTag('li');

				break;

			case Option::TYPE_STYLE :
				echo CHtml::tag('span', array(), '<b>'.$option['name'].'</b>');
				$style = Style::model()->findByPk((int) $option['value']);
				if (!$style) break;
				echo CHtml::tag('span', array('class'=>'param_value'),  $style->name);
				break;

			case Option::TYPE_IMAGE :
				// TODO : Если нужно выводить опции изображений на миникарте, то сделать это здесь
				break;
			case Option::TYPE_SIZE:
				echo CHtml::tag('li', array(), $option['name'].' — '.$option['value'] . ' ' . (isset(Option::$units[$option['param']['size_unit']]) ? Option::$units[$option['param']['size_unit']] : ''));
				break;
		}
	}
}
