<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->dropdownListRow($model, 'status', array(''=>'')+Tapestore::$statusNames); ?>

	<div class="clearfix">
		<?php echo CHtml::label('Обновлен от', 'reg');?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'name'=>'update_from',
				'value'=> '',
				'options'=>array('dateFormat'=>'dd.mm.yy'),
				'htmlOptions'=>array(
					'style'=>'width:150px;'
				),
			));?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Обновлен до', 'reg');?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'name'=>'update_to',
				'value' => '',
				'options'=>array('dateFormat'=>'dd.mm.yy'),
				'htmlOptions'=>array(
					'style'=>'width:150px;'
				),
			));?>
		</div>
	</div>

	<div class="actions">
		<?php echo CHtml::submitButton('Найти',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
