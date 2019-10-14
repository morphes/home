<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'specialist-rate-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model,'packet_3',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'discount_3',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'packet_7',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'discount_7',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'packet_14',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'discount_14',array('class'=>'span5')); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
