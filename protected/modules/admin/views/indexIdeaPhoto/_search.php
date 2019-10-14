<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->textFieldRow($model,'model_id',array('class'=>'span5', 'hint' => 'Можно через запятую')); ?>

	<?php echo $form->dropdownListRow($model,'status', array('' => '') + IndexIdeaPhoto::$statusName, array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<?php /*echo $form->textFieldRow($model,'user_id',array('class'=>'span5')); */?>

	<div class="clearfix">
		<?php echo CHtml::label('Логин / ФИО', 'author'); ?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'		=> 'author',
				'sourceUrl'	=> '/utility/autocompleteuser',
				'value'        => isset($model->author->name) ? $model->author->name . " ({$model->author->login})" : '',
				'options'	=> array(
					'showAnim'	=> 'fold',
					'delay'		=> 0,
					'autoFocus'	=> true,
					'select'	=> 'js:function(event, ui) {$("#IndexIdeaPhoto_user_id").val(ui.item.id); }',


				),
				'htmlOptions' => array('size'=>15)
			));?>

			<?php echo $form->hiddenField($model,'user_id',array('size'=>15)); ?>

			<?php
			Yii::app()->clientScript->registerScript('loginType', '
						$("#author").keydown(function(event){
							if (
								event.keyCode != 27 && event.keyCode != 9 && event.keyCode != 13
								&& event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40
								&& event.keyCode != 35 && event.keyCode != 36
							) {
								$("#IndexIdeaPhoto_user_id").val("");
							}
						});
					', CClientScript::POS_READY);
			?>
		</div>
	</div>


	<div class="clearfix">
		<?php echo CHtml::label('Обновлен от', 'reg')?>

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
		<?php echo CHtml::label('Обновлен до', 'reg')?>

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
