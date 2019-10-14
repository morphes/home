<?php
$urlParams = array();

if ($category instanceof Category) {
	$urlParams['eng_name'] = $category->eng_name;
} else {
	$category = new Category();
}
if ($city instanceof City) {
	$urlParams['city_name'] = $city->eng_name;
}

if (!is_array($options) || empty($options)) {
	$options = array();
}

echo CHtml::beginForm(
	Yii::app()->createUrl('/catalog2/category/list', $urlParams),
	'get',
	array('id' => 'filter_form')
);
?>
<?php echo CHtml::hiddenField('category_id', ($category) ? $category->id : 0); ?>

	<div class="-gutter-bottom-dbl sidebar-block">
		<span class="-strong -gutter-bottom -block">Помещение</span>
		<?php echo CHtml::dropDownList(
			'rooms',
			isset($selected['rooms']) ? $selected['rooms'] : '-1',
			array('-1' => 'Все помещения') + MainRoom::getAllRooms(),
			array('data-action' => 'submit')
		)?>
	</div>
	<div class="-gutter-bottom-dbl sidebar-block">
		<span class="-strong -gutter-bottom -block">Производитель</span>
		<?php echo CHtml::dropDownList(
			'vendor_country',
			isset($selected['vendor_country']) ? $selected['vendor_country'] : '',
			array('' => 'Все страны') + CHtml::listData(Vendor::getCountries($category->id), 'id', 'name'),
			array(
				'class' => '-gutter-bottom',
				'data-action' => 'submit'
			)
		); ?>

		<?php // Готовим список производителей

		$listData = CHtml::listData(Vendor::getVendorsByCountry(isset($selected['vendor_country']) ? $selected['vendor_country'] : '',$category->id), 'id', 'name');

		if ( ! $selected['vendors']) {
			$selected['vendors'] = array();
		}
		?>
		<div>
			<?php
			// Список производителей
			$htmlVendors = '';
			$index = 0;
			foreach ($listData as $v_id => $v_name) {

				// Часть элементов скрываем под "показать все"
				$clsHidden = ($index++ >= 5) ? ' -hidden ' : '';

				$htmlVendors .= CHtml::openTag(
					'label',
					array('class' => '-checkbox -block' . $clsHidden)
				);
				$htmlVendors .= CHtml::checkBox(
					'vendors[]',
					in_array($v_id, $selected['vendors']),
					array(
						'id'          => 'vendor_' . $v_id,
						'value'       => $v_id,
						'class'       => 'textInput',
						'data-action' => 'submit'
					)
				);
				$htmlVendors .= ' '; // Выводим пробел для отделения от чекбокса.
				$htmlVendors .= CHtml::tag('span', array(), $v_name);
				$htmlVendors .= CHtml::closeTag('label');
			}

			echo $htmlVendors;

			if ($index >= 5) {
				echo CHtml::tag('span', array('class' => '-gray -small -acronym show-all show_all'), 'Показать все');
			}
			?>

		</div>
		<span class="-light-gray-bg -small -inline -gutter-top submit-filter -hidden">Применить фильтр</span>
	</div>

	<?php // ФИЛЬТРЫ "ЦВЕТ" И "СТИЛЬ", ПРИМЕНЯЕМЫЕ КО ВСЕМ ОПЦИЯМ ДАННЫХ ТИПОВ ?>
	<?php if ($enableStyleColorFilter) : ?>
		<?php // ФИЛЬТР ПО ВСЕМ ОПЦИЯМ "СТИЛЬ" В КАТАЛОГЕ ?>
		<div class="-gutter-bottom-dbl sidebar-block">
			<span class="-strong -gutter-bottom -block">Стиль</span>
			<?php echo CHtml::dropDownList(
					'style',
					isset($selected['style']) ? $selected['style'] : '',
					array('' => 'Не важно') + CHtml::listData(Style::getAll(), 'id', 'name'),
					array('data-action' => 'submit')
				);
			?>

		</div>

		<?php // ФИЛЬТР ПО ВСЕМ ОПЦИЯМ "ЦВЕТ" В КАТАЛОГЕ ?>
		<div class="-gutter-bottom-dbl sidebar-block">
			<span class="-strong -gutter-bottom -block">Цвет</span>
			<div class="color-selector">
				<?php foreach(CatColor::getAll() as $val) : ?>
					<?php echo CHtml::openTag('label', array('class' => $val['param'], 'title' => $val['name'])); ?>
						<?php
						$checked = isset($selected['colors'])
							? in_array($val['id'], $selected['colors'])
							: false;

						echo CHtml::checkBox('colors[]',
							$checked,
							array(
								'value'       => $val['id'],
								'class'       => '-hidden',
								'data-action' => 'submit'
							)
						);
						$htmlOptions = array();
						if ($checked) {
							$htmlOptions['class'] = 'checked';
						}
						echo CHtml::tag('span', $htmlOptions, '');
						?>
					<?php echo CHtml::closeTag('label'); ?>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="-gutter-bottom-dbl sidebar-block price">
		<span class="-strong -gutter-bottom -block">Цена</span>
		<input type="text" value="<?php echo $selected['price_from'];?>" name="price_from" data-action="showButton">
		<span class="-gray">—</span>
		<input type="text" value="<?php echo $selected['price_to'];?>" name="price_to" data-action="showButton">
		<span class="-gray -small">руб.</span>
		<div class="range -gutter-top-dbl" data-maxprice="<?php echo round($category->getMaxPrice());?>" data-minprice=0></div>
		<span class="-light-gray-bg -small -inline -gutter-top-dbl submit-filter -hidden">Применить фильтр</span>
	</div>



	<?php
	/**
	 * Проход по опциям сокращенной карточки товара и вывод
	 */
	$htmlOut = '';

	foreach ($options as $option) {
		/*
		 * Пропуск опции без названий, типов или без значения
		 */
		if (empty($option['name']) || empty($option['type_id'])) {
			continue;
		}

		/*
		 * Контейнер опции
		 */
		$htmlOut .= CHtml::openTag('div', array('class' => '-gutter-bottom-dbl sidebar-block'));

		/*
		 * Заголовок опции
		 */
		$htmlOut .= CHtml::tag(
			'span',
			array('class' => '-semibold -gutter-bottom -block'),
			$option['name']
		);

		/**
		 * Вывод названия и значения опции в зависимости от типа опции
		 */
		switch ($option['type_id']) {

			case Option::TYPE_INPUT :
				$htmlOut .= CHtml::textField(
					$option['key'],
					isset($selected[$option['key']]) ? $selected[$option['key']] : '',
					array('class' => 'textInput')
				);
				break;



			case Option::TYPE_TEXTAREA :
				break;



			case Option::TYPE_SELECT :
				$values = Yii::app()->dbcatalog2->createCommand()
					->from('cat_value')
					->order('position')
					->where(
						'option_id=:oid and product_id is null',
						array(':oid' => $option['id'])
					)->queryAll();
				$dropDownValues = array();
				foreach ($values as $val) {
					$dropDownValues[$val['id']] = $val['value'];
				}
				$htmlOut .= CHtml::dropDownList(
					$option['key'],
					isset($selected[$option['key']]) ? $selected[$option['key']] : '',
					array('' => 'Не важно') + $dropDownValues,
					array('data-action' => 'submit')
				);
				break;



			case Option::TYPE_CHECKBOX: // Выбор «Да / Нет / Не важно»

				// Задаем значение радиобатонов и указывает default
				$values = array('' => 'Не важно', '1' => 'Да', '0' => 'Нет');
				if (!isset($selected[$option['key']])) {
					$selected[$option['key']] = '';
				}

				// Собираем radiobutton'ы
				foreach ($values as $key => $val) {

					$htmlOut .= CHtml::openTag('label', array('class' => '-block'));
					$htmlOut .= CHtml::radioButton(
						$option['key'],
						$selected[$option['key']] === (string)$key,
						array(
							'id'          => $option['key'] . '_' . $key,
							'value'       => $key,
							'class'       => 'textInput',
							'data-action' => 'submit'
						)
					);
					$htmlOut .= ' '; // Выводим пробел для отделения от чекбокса.
					$htmlOut .= CHtml::tag('span', array(), $val);
					$htmlOut .= CHtml::closeTag('label');
				}
				break;



			case Option::TYPE_SELECTMULTIPLE : // Список чекбоксов

				$values = Yii::app()->dbcatalog2->createCommand()
					->from('cat_value')
					->order('position')
					->where(
						'option_id = :oid AND product_id IS NULL',
						array(':oid' => $option['id'])
					)->queryAll();
				$checkboxes = array();
				foreach ($values as $val) {
					$checkboxes[$val['id']] = $val['value'];
				}
				if (!isset($selected[$option['key']])) {
					$selected[$option['key']] = false;
				}

				$index = 0;

				foreach ($checkboxes as $key => $val) {



					// Часть элементов скрываем под "показать все"
					$clsHidden = ($index++ >= 5) ? ' -hidden ' : '';

					$htmlOut .= CHtml::openTag(
						'label',
						array('class' => '-checkbox -block' . $clsHidden)
					);
					$htmlOut .= CHtml::checkBox(
						$option['key'] . '[]',
						isset($selected[$option['key']]) && !empty($selected[$option['key']]) && in_array($key, $selected[$option['key']])
							? true
							: false,
						array(
							'id'          => $option['key'] . '_' . $key,
							'value'       => $key,
							'class'       => 'textInput',
							'data-action' => 'submit'
						)
					);
					$htmlOut .= ' '; // Отделяем чекбокс от Label'а
					$htmlOut .= CHtml::tag('span', array(), $val);
					$htmlOut .= CHtml::closeTag('label');
				}
				if ($index > 5) {
					$htmlOut .= CHtml::tag(
						'span',
						array(
							'class' => '-gray -small -acronym show-all show_all',
							'data-alt' => 'Свернуть'
						),
						'Показать все'
					);
				}
				break;



			case Option::TYPE_COLOR :
				$values = Yii::app()->dbcatalog2->createCommand()
					->from('cat_color')
					->queryAll();

				$htmlOut .= CHtml::openTag('div', array('class' => 'color-selector'));

				foreach ($values as $val) {
					$htmlOut .= CHtml::openTag('label', array('class' => $val['param'], 'title' => $val['name']));

					$checked = isset($selected[$option['key']])
						? in_array($val['id'], $selected[$option['key']])
						: false;

					$htmlOut .= CHtml::checkBox(
						$option['key'] . '[]',
						$checked,
						array(
							'value'       => $val['id'],
							'class'       => '-hidden',
							'data-action' => 'submit'
						)
					);
					$htmlOptions = array();
					if ($checked) {
						$htmlOptions['class'] = 'checked';
					}

					$htmlOut .= CHtml::tag('span', $htmlOptions, '');
					$htmlOut .= CHtml::closeTag('label');
				}

				$htmlOut .= CHtml::closeTag('div');
				break;



			case Option::TYPE_STYLE :
				$htmlOut .= CHtml::dropDownList(
					$option['key'],
					isset($selected[$option['key']]) ? $selected[$option['key']] : '',
					array('' => 'Не важно') + CHtml::listData(Style::getAll(), 'id', 'name'),
					array('data-action' => 'submit')
				);
				break;



			case Option::TYPE_IMAGE :
				break;



			case Option::TYPE_SIZE :

				$catParams = $category->getParamsArray();

				if (isset($catParams['filterable_' . $option['type_id']]) && in_array($option['id'], $catParams['filterable_' . $option['type_id']])) {

					$htmlOut .= 'от ' . CHtml::textField(
							$option['key'] . '[from]',
							isset($selected[$option['key']]['from'])
								? $selected[$option['key']]['from']
								: '',
							array('class' => 'textInput', 'style' => 'width:50px;', 'data-action' => 'showButton')
						);
					$htmlOut .= ' до ' . CHtml::textField(
							$option['key'] . '[to]',
							isset($selected[$option['key']]['to'])
								? $selected[$option['key']]['to']
								: '',
							array('class' => 'textInput', 'style' => 'width:50px;', 'data-action' => 'showButton')
						);
				} else {
					$htmlOut .= CHtml::textField(
						$option['key'],
						isset($selected[$option['key']])
							? $selected[$option['key']]
							: '',
						array('class' => 'textInput')
					);
				}

				$params = $option['param'];
				if (empty($params{0})) {
					break;
				}
				$params = unserialize($params);
				if (isset($params['size_unit']) && isset(Option::$units[$params['size_unit']])) {
					$htmlOut .= ' ' . Option::$units[$params['size_unit']];
				}

				$htmlOut .= '<span class="-light-gray-bg -small -inline -gutter-top-hf submit-filter -hidden">Применить фильтр</span>';
				break;
		}

		$htmlOut .= CHtml::closeTag('div');
	}

	echo $htmlOut;
	?>

	<input type="hidden" id="layout" data-action="submit" value="<?php echo $viewType; ?>" name="view_type">

<?php echo CHtml::endForm(); ?>