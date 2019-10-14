<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

<?php
	$themes = CHtml::listData($themes, 'id', 'name');
	$types = CHtml::listData($eventTypes, 'id', 'name');
?>

	<?php /** @var $form BootActiveForm */
	echo $form->textFieldRow($model,'id',array('class'=>'span5')); ?>

	<?php echo $form->dropDownListRow($model,'status', array(''=>'')+MediaEvent::$statusNames, array('class'=>'span5')); ?>

	<?php echo $form->dropDownListRow($model,'event_type', array(''=>'')+$types, array('class'=>'span5')); ?>

	<?php echo $form->dropDownListRow($model,'theme', array(''=>'')+$themes, array('class'=>'span5')); ?>

	<?php

	?>

	<div class="clearfix">
		<?php echo CHtml::label('Логин / ФИО', 'author'); ?>

		<div class="input">
			<?php
			$this->widget('application.components.widgets.EAutoComplete', array(
				'valueName'	=> !empty($model->author) ? $model->author->name . " ({$model->author->login})" : '',
				'sourceUrl'	=> '/utility/autocompleteuser',
				'value'		=> $model->author_id,
				'options'	=> array(
					'showAnim'	=>'fold',
					'delay' => 1,
				),
				'htmlOptions'	=> array('id'=>'user-id', 'name'=>'MediaEvent[author_id]', 'size'=>15),
			));
			?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Город', ''); ?>

		<div class="input">
		<?php
		$cityId = Yii::app()->getRequest()->getParam('MediaEvent[city_id]');
		$this->widget('application.components.widgets.EAutoComplete', array(
			'valueName'	=> City::getNameById($cityId),
			'sourceUrl'	=> '/utility/autocompletecity',
			'value'		=> $cityId,
			'options'	=> array(
				'showAnim'  =>'fold',
				'minLength' => 3
			),
			'htmlOptions'	=> array('id'=>'city-id', 'name'=>'MediaEvent[city_id]')
		));
		?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Дата проведения от:', 'start_time')?>
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
			echo CHtml::hiddenField('MediaEvent[start_time]', $model->start_time, array('id'=>'start_time'));
			?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Дата проведения до:', 'end_time')?>
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
			echo CHtml::hiddenField('MediaEvent[end_time]', $model->end_time, array('id'=>'end_time'));
			?>
		</div>
	</div>

	<div class="actions">
		<?php echo CHtml::submitButton('Найти',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
