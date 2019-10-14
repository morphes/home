<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'                   => 'index-idea-link-form',
	'enableAjaxValidation' => false,
)); ?>

	<p class="help-block">Поля, отмеченные <span class="required">*</span>, обязательны.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model,'url',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model, 'position', array('class'=>'span5', 'hint' => 'от 1 до '.$model->getMaxPos())); ?>


	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
