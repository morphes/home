<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->textFieldRow($model,'id',array('class'=>'span5')); ?>

	<?php echo $form->dropDownListRow($model, 'status', array(''=>'Все')+MainUnit::$statusNames, array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'origin_id',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<div class="clearfix">
		<?php echo CHtml::label($model->getAttributeLabel('start_time'), 'start_time')?>
		<div class="input">
			<?php
			$this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'htmlOptions' => array('class'=>'span5'),
				'name'=>'date_to',
				'value'=> empty($model->start_time) ? '' : date('d.m.Y', $model->start_time),
				'options' => array(
					'autoLanguage' => false,
					'dateFormat' => 'dd.mm.yy',
					'timeFormat' => 'hh:mm',
					'changeMonth' => true,
					'changeYear' => true,
				),
			));
			?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label($model->getAttributeLabel('end_time'), 'end_time')?>
		<div class="input">
			<?php
			$this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'name'=>'date_from',
				'value'=> empty($model->end_time) ? '' : date('d.m.Y', $model->end_time),
				'htmlOptions' => array('class'=>'span5'),
				'options' => array(
					'autoLanguage' => false,
					'dateFormat' => 'dd.mm.yy',
					'timeFormat' => 'hh:mm',
					'changeMonth' => true,
					'changeYear' => true,
				),
			));
			?>
		</div>
	</div>

	<div class="actions">
		<?php echo CHtml::submitButton('Search',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
