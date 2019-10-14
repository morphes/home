<?php
/**
 * @var $model Contractor
 */
/** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->textFieldRow($model,'id',array('class'=>'span5')); ?>

	<?php echo $form->dropDownListRow($model, 'status', array(''=>'Все')+$model->getPublicStatuses()); ?>

	<?php echo $form->dropDownListRow($model, 'worker_id', Contractor::getSalesList()); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model,'email',array('class'=>'span5','maxlength'=>50)); ?>

	<div class="clearfix">
		<?php echo CHtml::label('Добавлен от:', 'start_time')?>
		<div class="input">
			<?php
			$this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'value'=>empty($model->start_time) ? '' : date('d.m.Y', $model->start_time),
				'htmlOptions' => array('name'=>'', 'id'=>'date1', 'class'=>'span5'),
				'options' => array(
					'autoLanguage' => false,
					'dateFormat' => 'dd.mm.yy',
					'timeFormat' => 'hh:mm',
					'changeMonth' => true,
					'changeYear' => true,
					'onSelect'=>'js:function(){
							var epoch = $.datepicker.formatDate("@", $(this).datepicker("getDate")) / 1000+10800;
							$("#start_time").val(epoch);
						}',
				),
			));
			echo CHtml::hiddenField('Contractor[start_time]', $model->start_time, array('id'=>'start_time'));
			?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Добавлен до:', 'end_time')?>
		<div class="input">
			<?php
			$this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'value'=> empty($model->end_time) ? '' : date('d.m.Y', $model->end_time),
				'htmlOptions' => array('name'=>'', 'id'=>'date2', 'class'=>'span5'),
				'options' => array(
					'autoLanguage' => false,
					'dateFormat' => 'dd.mm.yy',
					'timeFormat' => 'hh:mm',
					'changeMonth' => true,
					'changeYear' => true,
					'onSelect'=>'js:function(){
							var epoch = $.datepicker.formatDate("@", $(this).datepicker("getDate")) / 1000+10800;
							$("#end_time").val(epoch);
						}',
				),
			));
			echo CHtml::hiddenField('Contractor[end_time]', $model->end_time, array('id'=>'end_time'));
			?>
		</div>
	</div>

	<div class="actions">
		<?php echo CHtml::submitButton('Search',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
