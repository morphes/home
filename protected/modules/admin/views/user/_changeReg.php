<div class="well">
	<h3>Смена даты регистрации</h3>
	<p>Сменить дату регистрации можно только у приглашенных Специалистов (физ. и юр. лиц) со статусом "На подтверждении".</p>


	<?php /** @var $form BootActiveForm */
	$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
		'action'=>$this->createUrl($this->id.'/'.$this->action->id),
		'method'=>'get',
	)); ?>


	<div class="clearfix">
		<?php echo CHtml::label('Регистрация от', 'reg')?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'name'=>'reg_from_2',
			'value'=> $reg_from_2,
			'options'=>array('dateFormat'=>'dd.mm.yy'),
			'htmlOptions'=>array(
				'style'=>'width:150px;'
			),
		));?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Регистрация до', 'reg')?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'name'=>'reg_to_2',
			'value' => $reg_to_2,
			'options'=>array('dateFormat'=>'dd.mm.yy'),
			'htmlOptions'=>array(
				'style'=>'width:150px;'
			),
		));?>
		</div>
	</div>

	<div class="clearfix <?php if (empty($reg_new)) echo 'error';?>">
		<?php echo CHtml::label('Новая регистрация', 'reg')?>

		<div class="input <?php if (empty($reg_new)) echo 'error';?>">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'name'=>'reg_new',
			'value' => $reg_new,
			'options'=>array('dateFormat'=>'dd.mm.yy'),
			'htmlOptions'=>array(
				'style'=>'width:150px;'
			),
		));?>
		</div>
	</div>

	<input type="hidden" name="action" value="change-reg">


	<?php
	if ($qtForChange > 0)
	{
		if (isset($_GET['change_now']) && ! empty($reg_new))
		{
			echo CHtml::tag('p', array('class' => 'alert-message success'), 'Дата регистрации изменена');
		}
		else
		{
			echo CHtml::openTag('p');
			echo 'Для изменения даты регистрации было найдено пользвателей: <strong>'.$qtForChange.'</strong> ';
			echo CHtml::submitButton('Изменить дату регистрации', array('name' => 'change_now', 'class' => 'btn danger'));
			echo CHtml::closeTag('p');
		}

	}
	elseif (Yii::app()->request->getParam('action') == 'change-reg' && $qtForChange == 0)
	{
		echo CHtml::tag('p', array('class' => 'alert-message warning span4'), 'Пользователи не найдены');
	}
	?>

	<div class="actions">
		<?php echo CHtml::submitButton('Найти подходящих пользователей', array('class' => 'btn primary'));?>
	</div>

	<?php $this->endWidget(); ?>
</div>