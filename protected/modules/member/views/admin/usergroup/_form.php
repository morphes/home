

<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id'=>'usergroup-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model); ?>

	
	<?php echo $form->textFieldRow($model,'name', array('maxlength'=>255, 'class' => 'span6')); ?>
	

	
	<?php echo $form->textFieldRow($model,'desc',array('maxlength'=>255, 'class' => 'span6')); ?>
	

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', array('class' => 'btn primary')); ?>
		<?php echo CHtml::link('Отменить', $this->createUrl('admin'), array('class' => 'btn'));?>
	</div>

<?php $this->endWidget(); ?>

