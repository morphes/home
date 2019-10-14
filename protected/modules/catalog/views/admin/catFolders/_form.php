<?php $form = $this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id'                   => 'cat-folders-form',
	'enableAjaxValidation' => false,
)); ?>

<?php echo $form->errorSummary($model); ?>

<?php echo $form->textFieldRow($model, 'name', array('disabled' => true, 'class' => 'span5', 'maxlength' => 255)); ?>

<div class="clearfix">
	<label>Сообщение</label>

	<div class="input">
		<?php echo $form->textArea($model, 'description', array('class' => 'span7', 'style' => 'height:100px;')) ?>
	</div>
</div>


<div class="actions">
	<?php echo CHtml::submitButton($model->isNewRecord ? 'Create'
		: 'Save', array('class' => 'btn primary')); ?>
</div>

<?php $this->endWidget(); ?>
