<div class="row">

<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->textFieldRow($model, 'id'); ?>
	<?php echo $form->textFieldRow($model, 'name'); ?>
	<?php echo $form->textFieldRow($model, 'url'); ?>
	<?php echo $form->textFieldRow($model, 'desc'); ?>

	<div class="actions">
		<?php echo CHtml::submitButton('Найти', array('class' => 'btn')); ?>
	</div>

<?php $this->endWidget(); ?>

	
</div>