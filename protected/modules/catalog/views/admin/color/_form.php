<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'cat-color-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textAreaRow($model,'desc',array('class'=>'span5','maxlength'=>3000)); ?>

        <?php echo $form->textAreaRow($model,'tags',array('class'=>'span5','maxlength'=>3000)); ?>

	<?php echo $form->textFieldRow($model,'param',array('class'=>'span5','maxlength'=>45)); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
