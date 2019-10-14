<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->textFieldRow($model,'model_id',array('class'=>'span5', 'hint' => 'Можно через запятую')); ?>

	<div class="clearfix">
		<?php echo CHtml::label('Логин / ФИО', 'author'); ?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'        => 'author',
				'sourceUrl'   => '/utility/autocompleteuser',
				'value'       => '',
				'options'     => array(
					'showAnim'  => 'fold',
					'delay'     => 0,
					'autoFocus' => true,
					'select'    => 'js:function(event, ui) {$("#user_id").val(ui.item.id); }',
				),
				'htmlOptions' => array('size' => 15)
			));?>

			<?php echo $form->hiddenField($model, 'model_id', array('size' => 15, 'id' => 'user_id')); ?>

			<?php
			Yii::app()->clientScript->registerScript('loginType', '
				$("#author").keydown(function(event){
					if (
						event.keyCode != 27 && event.keyCode != 9 && event.keyCode != 13
						&& event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40
						&& event.keyCode != 35 && event.keyCode != 36
					) {
						$("#user_id").val("");
					}
				});
			', CClientScript::POS_READY);
			?>
		</div>
	</div>


	<?php echo $form->dropdownListRow($model,'status', array('' => '') + IndexSpecPhoto::$statusName, array('class'=>'span5')); ?>

	<div class="clearfix">
		<label>Блок (ссылка)</label>
		<div class="input">
			<?php
			$data = Yii::app()->db->createCommand('SELECT id, name FROM index_spec_block')->setFetchMode(PDO::FETCH_KEY_PAIR)->queryAll();
			echo CHtml::dropDownList('block_id', '', array('' => '') + $data);
			?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Обновлен от', 'reg') ?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'name'        => 'update_from',
				'value'       => '',
				'options'     => array('dateFormat' => 'dd.mm.yy'),
				'htmlOptions' => array(
					'style' => 'width:150px;'
				),
			));?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Обновлен до', 'reg') ?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'name'        => 'update_to',
				'value'       => '',
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
