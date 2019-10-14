<div class="well" style="background-color: #F9F9F9;">
	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Характеристики', true); ?>

	<?php echo $form->dropDownListRow($model, 'material_id', array('' => '')+CHtml::listData($materials, 'id', 'option_value'), array('class' => 'span7'));?>
</div>
