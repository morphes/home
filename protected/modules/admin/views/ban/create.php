<div class="form">
	
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'mailer-delivery-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'ip'); ?>
		<?php echo $form->textField($model,'ip'); ?>
		<?php echo $form->error($model,'ip'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'bansize'); ?>
		<?php echo $form->textField($model,'expire'); ?>
		<?php echo $form->error($model,'expire'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Create'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->