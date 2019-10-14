<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'                   => 'mall-service-form',
	'enableAjaxValidation' => false,
	'focus'                => array($model, 'name')
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model,'pos',array('class'=>'span2', 'hint' => 'от 1 до '.$model->getMaxPos())); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
