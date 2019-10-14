<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->textFieldRow($model,'id',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>32)); ?>

	<?php echo $form->textFieldRow($model,'url',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model,'position',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'rubric',array('class'=>'span5','maxlength'=>3000)); ?>

	<div class="actions">
		<?php echo CHtml::submitButton('Search',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
