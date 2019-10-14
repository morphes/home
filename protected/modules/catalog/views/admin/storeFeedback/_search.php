<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->textFieldRow($model,'id',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'store_id',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'user_id',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'mark',array('class'=>'span1	')); ?>


	<div class="clearfix">
		<?php echo CHtml::label('Добавлена от', 'reg_from')?>
		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'name'        => 'date_from',
				'value'       => '',
				'language'    => 'ru',
				'options'     => array('dateFormat' => 'dd.mm.yy'),
				'htmlOptions' => array(
					'style' => 'width:150px;'
				),
			));?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Добавлена до', 'reg_to')?>
		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'name'        => 'date_to',
				'value'       => '',
				'language'    => 'ru',
				'options'     => array('dateFormat' => 'dd.mm.yy'),
				'htmlOptions' => array(
					'style' => 'width:150px;'
				),
			));?>
		</div>
	</div>


	<div class="actions">
		<?php echo CHtml::submitButton('Найти',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
