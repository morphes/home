<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'specialist-rate-city-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'rate_id',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'city_id',array('class'=>'span5')); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
