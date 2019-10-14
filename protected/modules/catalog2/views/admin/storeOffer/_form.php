<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'store-offer-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block">Поля, отмеченные <span class="required">*</span>, обязательны.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'company',array('class'=>'span5','maxlength'=>50)); ?>

	<?php echo $form->textFieldRow($model,'city_name',array('class'=>'span5','maxlength'=>50)); ?>

	<?php echo $form->textFieldRow($model,'company_phone',array('class'=>'span5','maxlength'=>50)); ?>

	<?php echo $form->textFieldRow($model,'email',array('class'=>'span5','maxlength'=>50)); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>70)); ?>

	<?php echo $form->textFieldRow($model,'job',array('class'=>'span5','maxlength'=>50)); ?>

	<?php echo $form->textFieldRow($model,'site',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textAreaRow($model,'comment',array('rows'=>6, 'cols'=>50, 'class'=>'span8')); ?>

	<?php echo $form->textFieldRow($model,'accept_rule',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'selected_services',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'status',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'create_time',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'update_time',array('class'=>'span5')); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
