<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route).'/id/'.$model->id,
	'method'=>'get',
)); ?>

	<div class="clearfix">
		<?php echo CHtml::label('Статистика от', 'timeFrom')?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'name' => 'timeFrom',
				'value' => $timeFrom,
				'options'=>array('dateFormat'=>'dd.mm.yy'),
				'htmlOptions'=>array(
					'style'=>'width:150px;'
				),
			));?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Статистика до', 'timeTo')?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'name' => 'timeTo',
				'value' => $timeTo,
				'options'=>array('dateFormat'=>'dd.mm.yy'),
				'htmlOptions'=>array(
					'style'=>'width:150px;'
				),
			));?>
		</div>
	</div>

	<?php echo CHtml::label('Выбор города','')?>
	<div class="input">
		<?php echo CHtml::dropDownList('city',$city,array('null'=>'Все города')+array('0'=>'Без города')+$listCity); ?>
	</div>

	<div class="actions">
		<?php echo CHtml::submitButton('Найти',array('class'=>'btn primary')); ?>
		<?php echo CHtml::button('Сбросить фильтр', array('class'=>'primary btn','onclick'=>'document.location = \''.$this->createUrl('/member/admin/statSpecialist/statistic/id/'.$model->id).'\''))?>
	</div>

<?php $this->endWidget(); ?>