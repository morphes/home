<script type="text/javascript">
	function changeBuildingType()
	{
		$('#filter_form').append('<input type="hidden" name="change_build_type" value="true">');
		$('#filter_form').submit();
	}
</script>

<div class="ideas_showed">
	<?php echo CFormatterEx::formatNumeral($ideaCount, array('Показан', 'Показано', 'Показано'), true);?>
	<span><?php echo $ideaCount;?></span>
	<?php echo CFormatterEx::formatNumeral($ideaCount, array('вариант', 'варианта', 'вариантов'), true);?>
</div>

<?php echo CHtml::form('/idea/catalog/index', 'get', array('id' => 'filter_form')); ?>

<div class="shadow_block padding-18 ideas_filter">

	<?php
	echo CHtml::hiddenField('filter', '0');
	echo CHtml::hiddenField('ideatype', Config::ARCHITECTURE);
	echo CHtml::hiddenField('sortby', $selected['sortType'], array('id'=>'sort_elements'));
	echo CHtml::hiddenField('pagesize', $selected['pageSize'], array('id'=>'elements_on_page'));
	echo CHtml::hiddenField('page', Yii::app()->request->getParam('page'));
	?>

	<div class="filter_hint">
		<i></i>
		Показать <a class="" href="#" onclick="$('#filter_form').submit(); return false;"><span>22</span> варианта</a>
	</div>

	<div class="drop_down filter_item">
		<p>Тип объекта</p>
		<?php
		$objectsLi = '';
		$currentObject = '&nbsp;';
		if ($objects) {
			foreach($objects as $obj)
			{
				if ($obj->id == $selected['object_type']) {
					$currentObject = $obj->option_value;
					$cls = 'active';
				} else {
					$cls = '';
				}

				$objectsLi .= CHtml::tag('li', array('data-value' => $obj->id, 'class' => $cls), $obj->option_value, true);
			}
		}
		?>
		<span class="exp_current"><?php echo $currentObject;?><i></i></span>
		<ul class="set_input" data-callback="changeBuildingType">
			<?php echo $objectsLi; ?>
		</ul>
		<?php echo CHtml::hiddenField('object_type', $selected['object_type'], array('id' => 'object_type')); ?>
	</div>

	<div class="room_style filter_item">
		<p>Стиль<span></span></p>
		<ul class="visible_types">
			<?php
			foreach (Config::$architectureStyleGroups as $key => $value) {
				echo CHtml::openTag('li', array('class' => 'parent'));

				echo CHtml::checkBox('', false, array('class'=>'check_all', 'value'=>''));
				echo CHtml::link($value);
				echo CHtml::openTag('ul', array('class'=>'level2 hide'));
				foreach ($styles as $styleKey => $style) {
					if ($style->param != $key)
						continue;
					echo CHtml::tag('li', array(),
						CHtml::checkBox('', false, array('value'=>$style->id))
							. CHtml::link($style->option_value, '#')
					);
					unset($styles[$styleKey]);
				}
				echo CHtml::closeTag('ul');
				echo CHtml::closeTag('li');
			}

			foreach ($styles as $style) {
				echo CHtml::tag('li', array(),
					CHtml::checkBox('', false, array('value'=>$style->option_value))
						. CHtml::link($style->option_value, '#')
				);
			}
			?>
		</ul>
		<a href="#" class="show_all">развернуть список</a>
		<?php echo CHtml::hiddenField('style', $selected['style'], array('id'=>'styles_input')); ?>
	</div>
	<div class="drop_down filter_item">
		<p>Материал несущих конструкций</p>
		<?php
		$materialLi = '';
		$currentMaterial = '&nbsp;';
		if ($materials) {
			foreach($materials as $item)
			{
				if ($selected['material'] == $item->id) {
					$currentMaterial = $item->option_value;
					$cls = 'active';
				} else {
					$cls = '';
				}

				$materialLi .= CHtml::tag('li', array('data-value' => $item->id, 'class' => $cls), $item->option_value, true);
			}
		}
		?>
		<span class="exp_current"><?php echo $currentMaterial;?><i></i></span>
		<ul class="set_input" data-callback="getCount">
			<li data-value="" <?php if ($currentMaterial == '&nbsp;') echo 'class="active"';?>>&nbsp;</li>
			<?php echo $materialLi; ?>
		</ul>
		<?php echo CHtml::hiddenField('material', $selected['material'], array('id' => 'object_material'));?>
	</div>
	<div class="drop_down filter_item">
		<p>Количество этажей</p>
		<?php
		$floorLi = '';
		$currentFloor = '&nbsp;';
		if ($floors) {
			foreach($floors as $item)
			{
				if ($selected['floor'] == $item->id) {
					$currentFloor = $item->option_value;
					$cls = 'active';
				} else {
					$cls = '';
				}

				$floorLi .= CHtml::tag('li', array('data-value' => $item->id, 'class' => $cls), $item->option_value, true);
			}
		}
		?>
		<span class="exp_current"><?php echo $currentFloor;?><i></i></span>
		<ul class="set_input" data-callback="getCount">
			<li data-value="" <?php if ($currentFloor == '&nbsp;') echo 'class="active"'; ?>>&nbsp;</li>
			<?php echo $floorLi; ?>
		</ul>
		<?php echo CHtml::hiddenField('floor', $selected['floor'], array('id' => 'object_flors'));?>

	</div>

	<div class="additional_room filter_item">
		<p>Дополнительные строения</p>
		<ul>
			<li data-value="mansard"><input value="mansard" type="checkbox"><a href="#">Мансарда</a></li>
			<li data-value="basement"><input value="basement" type="checkbox"><a href="#">Подвал</a></li>
			<li data-value="ground"><input value="ground" type="checkbox"><a href="#">Цокольный этаж</a></li>
			<li data-value="garage"><input value="garage" type="checkbox"><a href="#">Встроенный гараж</a></li>
		</ul>
		<?php echo CHtml::hiddenField('room', $selected['room'], array('id' => 'object_room'));?>
	</div>

	<div class="room_color filter_item">
		<p>Цвета</p>
		<ul class="colors_list">
			<?php
			$cnt=0;
			foreach ($colors as $color) {
				$cnt++;
				echo CHtml::tag('li',
					array('id'=>'c_'.$cnt, 'class'=>$color->param),
					CHtml::tag('p', array('class'=>'hide'), $color->option_value)
						.CHtml::tag('div')
				);
			}
			?>
		</ul>
		<?php echo CHtml::hiddenField( 'color', $selected['color'], array('id'=>'colors_input') ); ?>
		<div class="clear"></div>
		<div class="checked_color"></div>
	</div>


	<input type="hidden" name="elements_on_page" id= "elements_on_page" value=""/>
	<input type="hidden" name="sort_elements" id= "sort_elements" value=""/>
	<div class="btn_conteiner yellow">
		<a class="btn_grey" onclick="return formSend();" href="#">Показать<span><?php echo $ideaCount;?></span><?php echo CFormatterEx::formatNumeral($ideaCount, array('идею', 'идеи', 'идей'), true); ?></a>
	</div>
</div>


</form>