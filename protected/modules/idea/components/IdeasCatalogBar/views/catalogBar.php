<div class="ideas_showed">
	<?php echo CFormatterEx::formatNumeral($ideaCount, array('Показан', 'Показано', 'Показано'), true); ?>
	<span><?php echo $ideaCount; ?></span>
	<?php echo CFormatterEx::formatNumeral($ideaCount, array('вариант', 'варианта', 'вариантов'), true); ?>
</div>

<?php echo CHtml::form('/idea/catalog/index', 'get', array('id' => 'filter_form')); ?>
<div class="shadow_block padding-18 ideas_filter">
	<?php
	echo CHtml::hiddenField('filter', '0');
	echo CHtml::hiddenField('ideatype', $ideaType);
	echo CHtml::hiddenField('sortby', $sortType, array('id' => 'sort_elements'));
	echo CHtml::hiddenField('pagesize', $pageSize, array('id' => 'elements_on_page'));
	echo CHtml::hiddenField('page', Yii::app()->request->getParam('page'));
	?>

	<div class="filter_hint">
		<i></i>
		Показать <a class=""
			    href="#"
			    onclick="$('#filter_form').submit(); return false;"><span>22</span>
			варианта</a>
	</div>

	<div class="drop_down filter_item">
		<p>Тип объекта</p>
		<?php echo CHtml::tag('span', array('class' => 'exp_current'), $objectType->option_value . '<i></i>'); ?>
		<ul>
			<?php
			foreach ($objects as $object) {
				echo CHtml::tag('li', array('data-rel' => $object->id), $object->option_value);
			}
			?>
		</ul>
		<?php echo CHtml::hiddenField('objecttype', $objectType->id, array('id' => 'object_type')); ?>
	</div>
	<div class="room_type filter_item">
		<p>Помещения <span></span></p>
		<?php
		$cnt = 0;
		echo CHtml::openTag('ul', array('class' => 'visible_types'));
		foreach ($rooms as $room) {
			if ($cnt == $visibleRooms) {
				echo CHtml::closeTag('ul');
				echo CHtml::openTag('ul', array('class' => 'hide_types hide'));
			}
			echo CHtml::tag('li', array(), CHtml::checkBox('', false, array(
					'value' =>
					$room->option_value
				))
				. CHtml::link($room->option_value)
			);
			$cnt++;
		}
		echo CHtml::closeTag('ul');
		if ($cnt > $visibleRooms) {
			$hiddenRooms = $cnt - $visibleRooms;
			echo CHtml::link('еще ' . CFormatterEx::formatNumeral($hiddenRooms, array('помещение', 'помещения', 'помещений')),
				'#', array('class' => 'show_all', 'onclick' => "_gaq.push(['_trackEvent','Filter','Помещения']); yaCounter11382007.reachGoal('fpom'); return true;"));
		}
		?>
		<?php echo CHtml::hiddenField('room', $selected['room'], array('id' => 'room_type')); ?>
	</div>
	<div class="room_style filter_item">
		<p>Стили <span></span></p>
		<ul class="visible_types">
			<?php
			foreach (Config::$ideaStyleGroups as $key => $value) {
				echo CHtml::openTag('li', array('class' => 'parent'));

				echo CHtml::checkBox('', false, array('class' => 'check_all', 'value' => ''));
				echo CHtml::link($value);
				echo CHtml::openTag('ul', array('class' => 'level2 hide'));
				foreach ($styles as $styleKey => $style) {
					if ($style->param != $key)
						continue;
					echo CHtml::tag('li', array(),
						CHtml::checkBox('', false, array('value' => $style->option_value))
						. CHtml::link($style->option_value, '#')
					);
					unset($styles[$styleKey]);
				}
				echo CHtml::closeTag('ul');
				echo CHtml::closeTag('li');
			}

			foreach ($styles as $style) {
				echo CHtml::tag('li', array(),
					CHtml::checkBox('', false, array('value' => $style->option_value))
					. CHtml::link($style->option_value, '#')
				);
			}
			?>
		</ul>
		<a href="#"
		   class="show_all"
		   onclick="_gaq.push(['_trackEvent','Filter','Стили']); yaCounter11382007.reachGoal('fstil'); return true;">развернуть
															     список</a>
		<?php echo CHtml::hiddenField('style', $selected['style'], array('id' => 'styles_input')); ?>
	</div>
	<div class="room_color filter_item">
		<p>Цвета <span></span></p>
		<ul class="colors_list">
			<?php
			$cnt = 0;
			foreach ($colors as $color) {
				$cnt++;
				echo CHtml::tag('li',
					array('id' => 'c_' . $cnt, 'class' => $color->param),
					CHtml::tag('p', array('class' => 'hide'), $color->option_value)
					. CHtml::tag('div')
				);
			}
			?>
		</ul>
		<?php echo CHtml::hiddenField('color', $selected['color-data'], array('id' => 'colors_input')); ?>
		<div class="clear"></div>
		<div class="checked_color"></div>
	</div>
	<div class="filter_tags filter_item">
		<div class="some_tags">
			<p>Ключевое слово</p>
			<input class="textInput tags_area"
			       id="tags_input"
			       type="text"/>

			<div class="tags_list">
				<ul class="filter_items">

				</ul>
			</div>
		</div>
		<a href="#"
		   class="clear_tags">Сбросить список тегов</a>
		<input type="hidden"
		       name="tags-list"
		       id="tags_list"
		       value="<?php echo $selected['tags-list']; ?>">

		<div class="clear"></div>
	</div>
	<div class="btn_conteiner yellow">
		<a class="btn_grey"
		   onclick="return formSend();"
		   href="#">Показать<span><?php echo $ideaCount; ?></span>идей</a>
	</div>
</div>
<?php echo CHtml::endForm(); ?>
