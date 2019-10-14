<?php /** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'main-room-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->dropDownListRow($model, 'status', MainRoom::$statusNames, array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model,'genetive',array('class'=>'span5','maxlength'=>255)); ?>

	<div class="actions">
		<?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary')); ?>
		<?php echo CHtml::button('Отмена', array('class'=>'btn default','onclick' => "document.location = '".$this->createUrl('/catalog2/admin/mainroom/index')."'"));?>
	</div>
<?php $this->endWidget(); ?>
