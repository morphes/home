<div class="well" style="background-color: #F9F9F9;">
	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Характеристики', true); ?>


	<?php echo $form->dropDownListRow($model, 'style_id', array('' => '')+CHtml::listData($styles, 'id', 'option_value'), array('class' => 'span7')); ?>

	<div class="row">
		<div class="span5">
			<?php echo $form->dropDownListRow($model, 'material_id', array('' => '')+CHtml::listData($materials, 'id', 'option_value'), array('class' => 'span5'));?>
		</div>
		<div class="span2">
			<?php echo $form->dropDownListRow($model, 'floor_id', array('' => '')+CHtml::listData($floors, 'id', 'option_value'), array('class' => 'span2'));?>
		</div>
	</div>

	<div class="row">
		<div class="span7">
			<?php echo $form->dropDownListRow($model, 'color_id', array('' => '')+CHtml::listData($colors, 'id', 'option_value'), array('class' => 'span7'));?>
		</div>

		<div class="span3">
			<?php echo $form->dropDownListRow($addColors[0], "[$model->id][0]color_id", array('' => '')+CHtml::listData($colors, 'id', 'option_value'), array('class' => 'span3'));?>
		</div>

		<div class="span3">
			<?php echo $form->dropDownListRow($addColors[1], "[$model->id][1]color_id", array('' => '')+CHtml::listData($colors, 'id', 'option_value'), array('class' => 'span3'));?>
		</div>
	</div>



	<div class="row" style="margin-top: 20px;">

		<div class="span13">
			<div class="row">
				<div class="span4">
					<?php echo $form->checkBoxRow($model, 'room_mansard');?>
					<?php echo $form->checkBoxRow($model, 'room_garage');?>
				</div>
				<div class="span4">
					<?php echo $form->checkBoxRow($model, 'room_ground');?>
					<?php echo $form->checkBoxRow($model, 'room_basement');?>
				</div>
			</div>
		</div>
	</div>
</div>
