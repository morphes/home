<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>
	<?php echo $form->textFieldRow($model,'mallId',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'user_id',array('class'=>'span5')); ?>

	<?php echo $form->dropDownListRow($model,'status',array(''=>'Все') + CatFolders::$statusLabels,array('class'=>'span5')); ?>


	<div class="actions">
		<?php echo CHtml::submitButton('Search',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
