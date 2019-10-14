<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->textFieldRow($model,'id',array('class'=>'span5')); ?>

	<?php echo $form->dropDownListRow($model,'status', array('' => 'Все')+Review::$statusNames, array('size' => 1)); ?>

	<?php echo $form->dropDownListRow($model,'type', array('' => 'Все')+Review::$typeNames, array('size' => 1)); ?>

	<?php echo $form->textFieldRow($model,'rating',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'spec_login',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'author_login',array('class'=>'span5')); ?>

	<div class="clearfix">
		<?php echo CHtml::label('Добавлена от', 'reg_from')?>
		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'name'=>'date_from',
			'value'	=> $date_from,
			'language'	=> 'ru',
			'options'=>array('dateFormat'=>'dd.mm.yy'),
			'htmlOptions'=>array(
				'style'=>'width:150px;'
			),
		));?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Добавлена до', 'reg_to')?>
		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'name'=>'date_to',
			'value'=> $date_to,
			'language'	=> 'ru',
			'options'=>array('dateFormat'=>'dd.mm.yy'),
			'htmlOptions'=>array(
				'style'=>'width:150px;'
			),
		));?>
		</div>
	</div>

	<div class="actions">
		<?php echo CHtml::submitButton('Search',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
