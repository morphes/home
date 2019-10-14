<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->dropDownListRow($model,'status',array(''=>'Все') + Spam::$statusLabels,array('class'=>'span5')); ?>


<div class="clearfix">
	<?php echo CHtml::label('Жалоба на','authorName')?>
	<div class="input">
		<?php echo $form->textField($model, 'authorName', array('class'=>'span6'))?>
	</div>
</div>

<div class="clearfix">
	<?php echo CHtml::label('Жалоба от','recipientName')?>
	<div class="input">
		<?php echo $form->textField($model, 'recipientName', array('class'=>'span6'))?>
	</div>
</div>

<div class="clearfix">
	<?php echo CHtml::label('Время от', 'timeFrom')?>

	<div class="input">
		<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'model' => $model,
			'attribute' => 'timeFrom',
			'options'=>array('dateFormat'=>'dd.mm.yy'),
			'htmlOptions'=>array(
				'style'=>'width:150px;'
			),
		));?>
	</div>
</div>

<div class="clearfix">
	<?php echo CHtml::label('Время  до', 'timeTo')?>

	<div class="input">
		<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'model' => $model,
			'attribute' => 'timeTo',
			'options'=>array('dateFormat'=>'dd.mm.yy'),
			'htmlOptions'=>array(
				'style'=>'width:150px;'
			),
		));?>
	</div>
</div>

<div class="clearfix">
	<?php echo CHtml::label('Все сообщения пользователя','allMessageFilter')?>
	<div class="input">
		<?php echo $form->textField($model, 'allMessageFilter', array('class'=>'span6'))?>
	</div>
</div>

<div class="clearfix">
	<?php echo CHtml::label('Количество сообщений >','countMessageFilter')?>
	<div class="input">
		<?php echo $form->textField($model, 'countMessageFilter', array('class'=>'span6'))?>
	</div>
</div>

<div class="clearfix">
	<?php echo CHtml::label('Сообщение содержит','searchString')?>
	<div class="input">
		<?php echo $form->textField($model, 'searchString', array('class'=>'span6'))?>
	</div>
</div>

	<div class="actions">
		<?php echo CHtml::submitButton('Search',array('class'=>'btn primary')); ?>
		<?php echo CHtml::button('Сбросить фильтр', array('class'=>'primary btn','onclick'=>'document.location = \''.$this->createUrl('/member/admin/spam/').'\''))?>
	</div>

<?php $this->endWidget(); ?>
