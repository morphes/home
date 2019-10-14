<?php /** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->dropDownListRow($model, 'status', array('' => '') + StoreNews::$statuses, array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'user_id',array('class'=>'span5')); ?>


	<?php echo $form->textFieldRow($model,'store_id',array('class'=>'span5')); ?>

	<div class="actions">
		<?php echo CHtml::submitButton('Найти',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
