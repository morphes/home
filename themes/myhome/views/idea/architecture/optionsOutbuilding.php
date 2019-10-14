<div class="shadow_block padding-18 project_add">
	<h5 class="block_headline">Характеристики</h5>

	<div class="input_row">
		<div class="input_conteiner">
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

			<label>Материал <span class="required">*</span></label>
			<div class="build_type drop_down <?php if ($model->getError('material_id')) echo 'error';?>">
				<span class="exp_current"><?php echo $materialCurrentName;?><i></i></span>
				<ul>
					<?php echo $materialLi; ?>
				</ul>
				<?php echo CHtml::activeHiddenField($model, 'material_id'); ?>
			</div>

		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>

</div>
<div class="spacer-18"></div>