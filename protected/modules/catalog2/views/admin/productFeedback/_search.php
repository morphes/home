<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->textFieldRow($model,'id',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'product_id',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'parent_id',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'user_id',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'mark',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'merits',array('class'=>'span5','maxlength'=>3000)); ?>

	<?php echo $form->textFieldRow($model,'limitations',array('class'=>'span5','maxlength'=>3000)); ?>

	<?php echo $form->textFieldRow($model,'message',array('class'=>'span5','maxlength'=>3000)); ?>

	<?php echo $form->textFieldRow($model,'create_time',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'update_time',array('class'=>'span5')); ?>

	<div class="actions">
		<?php echo CHtml::submitButton('Найти',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
