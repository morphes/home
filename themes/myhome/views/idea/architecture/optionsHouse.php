<div class="shadow_block padding-18 project_add">
	<h5 class="block_headline">Характеристики</h5>
	<div class="input_row">
		<div class="input_conteiner">
			<?php
			// ФОрмируем строку для стилей и определяем текущий элемент
			$styleLi = '';
			$styleCurrentName = '';
			if ($styles) {
				foreach($styles as $style) {
					$styleLi .= CHtml::tag('li', array('data-value' => $style->id), $style->option_value, true);
					if ($style->id == $model->style_id)
						$styleCurrentName = $style->option_value;
				}
			}
			?>

			<label>Архитектурный стиль <span class="required">*</span></label>
			<div class="build_type drop_down <?php if ($model->getError('style_id')) echo 'error';?>">
				<span class="exp_current"><?php echo $styleCurrentName;?><i></i></span>
				<ul>
					<?php echo $styleLi; ?>
				</ul>
				<?php echo CHtml::activeHiddenField($model, 'style_id'); ?>
			</div>
		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>
	<div class="input_row">
		<div class="input_conteiner">
			<div class="materials">
				<?php
				// ФОрмируем строку для стилей и определяем текущий элемент
				$materialLi = '';
				$materialCurrentName = '';
				if ($materials) {
					foreach($materials as $material) {
						$materialLi .= CHtml::tag('li', array('data-value' => $material->id), $material->option_value, true);
						if ($material->id == $model->material_id)
							$materialCurrentName = $material->option_value;
					}
				}
				?>

				<label>Материалы несущих конструкций <span class="required">*</span></label>
				<div class="build_type drop_down <?php if ($model->getError('material_id')) echo 'error';?>">
					<span class="exp_current"><?php echo $materialCurrentName;?><i></i></span>
					<ul>
						<?php echo $materialLi; ?>
					</ul>
					<?php echo CHtml::activeHiddenField($model, 'material_id'); ?>
				</div>
			</div>

			<div class="flors">
				<?php
				// ФОрмируем строку для стилей и определяем текущий элемент
				$floorLi = '';
				$floorCurrentName = '';
				if ($floors) {
					foreach($floors as $floor) {
						$floorLi .= CHtml::tag('li', array('data-value' => $floor->id), $floor->option_value, true);
						if ($floor->id == $model->floor_id)
							$floorCurrentName = $floor->option_value;
					}
				}
				?>
				
				<label>Этажность <span class="required">*</span></label>
				<div class="build_flors drop_down <?php if ($model->getError('floor_id')) echo 'error';?>">
					<span class="exp_current"><?php echo $floorCurrentName; ?><i></i></span>
					<ul>
						<?php echo $floorLi; ?>
					</ul>
					<?php echo CHtml::activeHiddenField($model, 'floor_id'); ?>
				</div>
			</div>
		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>

	<div class="input_row">
		<div class="input_conteiner">
			<div class="colors">
				<?php
				// Формируем строку для стилей и определяем текущий элемент
				$colorLi = '';
				$colorCurrentName = '';
				if ($colors) {
					foreach($colors as $color) {
						$colorLi .= CHtml::tag('li', array('data-value' => $color->id), $color->option_value, true);
						if ($color->id == $model->color_id)
							$colorCurrentName = $color->option_value;
					}
				}
				?>
				
				<label>Основной цвет <span class="required">*</span></label>
				<div class="build_type drop_down <?php if ($model->getError('color_id')) echo 'error';?>">
					<span class="exp_current"><?php echo $colorCurrentName;?><i></i></span>
					<ul>
						<?php echo $colorLi;?>
					</ul>
					<?php echo CHtml::activeHiddenField($model, 'color_id'); ?>
				</div>
			</div>

			<div class="colors">
				<?php
				// Формируем строку для стилей и определяем текущий элемент
				$colorLi = '';
				$colorCurrentName = '';
				if ($colors) {
					foreach($colors as $color) {
						$colorLi .= CHtml::tag('li', array('data-value' => $color->id), $color->option_value, true);
						if ($color->id == $addColors[0]->color_id)
							$colorCurrentName = $color->option_value;
					}
				}
				?>

				<label>Дополнительный</label>
				<div class="build_type drop_down <?php if (isset($errorsSaveColors[$model->id][0]['color_id'])) echo 'error';?>">
					<span class="exp_current"><?php echo $colorCurrentName;?><i></i></span>
					<ul>
						<li value="">&nbsp;</li>
						<?php echo $colorLi; ?>
					</ul>
					<?php echo CHtml::activeHiddenField($addColors[0], "[$model->id][0]color_id"); ?>
				</div>
			</div>

			<div class="colors last">
				<?php
				// Формируем строку для стилей и определяем текущий элемент
				$colorLi = '';
				$colorCurrentName = '';
				if ($colors) {
					foreach($colors as $color) {
						$colorLi .= CHtml::tag('li', array('data-value' => $color->id), $color->option_value, true);
						if ($color->id == $addColors[1]->color_id)
							$colorCurrentName = $color->option_value;
					}
				}
				?>

				<label>Дополнительный</label>
				<div class="build_type drop_down <?php if (isset($errorsSaveColors[$model->id][1]['color_id'])) echo 'error';?>">
					<span class="exp_current"><?php echo $colorCurrentName;?><i></i></span>
					<ul>
						<li value="">&nbsp;</li>
						<?php echo $colorLi; ?>
					</ul>
					<?php echo CHtml::activeHiddenField($addColors[1], "[$model->id][1]color_id"); ?>
				</div>
			</div>
		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>

	<div class="input_row">
		<div class="input_conteiner ">
			<label>Наличие дополнительных строений</label>
			<div class="inputs_list">
				<ul class="">

					<li><label><?php echo CHtml::activeCheckBox($model, 'room_mansard'); ?>Мансарда</label></li>
					<li><label><?php echo CHtml::activeCheckBox($model, 'room_garage'); ?>Встроенный гараж</label></li>
					<li><label><?php echo CHtml::activeCheckBox($model, 'room_ground'); ?>Цокольный этаж</label></li>
					<li><label><?php echo CHtml::activeCheckBox($model, 'room_basement'); ?>Подвал</label></li>
				</ul>
				<input type="hidden" name="" value="">
				<div class="clear"></div>
			</div>
		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="spacer-18"></div>