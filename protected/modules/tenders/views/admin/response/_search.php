<div class="row">
        
        <?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
                                'action'=>Yii::app()->createUrl($this->route),
                                'method'=>'get',
                        )); ?>

	<?php echo $form->textFieldRow($model,'id',array('size'=>15, 'hint'=>'Можно указать несколько ID, разделив их запятой')); ?>

	<?php echo $form->textFieldRow($model,'tender_id',array('size'=>15)); ?>

	<div class="clearfix">
		<?php echo CHtml::label('Логин / ФИО', 'author'); ?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
					'name'		=> 'author',
					'sourceUrl'	=> '/utility/autocompleteuser',
					'value'        => isset($model->user->name) ? $model->user->name . " ({$model->user->login})" : '',
					'options'	=> array(
						'showAnim'	=> 'fold',
						'delay'		=> 0,
						'autoFocus'	=> true,
						'select'	=> 'js:function(event, ui) {$("#response_author_id").val(ui.item.id); }',
					),
				'htmlOptions' => array('size'=>15)
			));?>

			<?php echo $form->hiddenField($model,'author_id',array('size'=>15, 'id'=>'response_author_id')); ?>

			<?php
			Yii::app()->clientScript->registerScript('loginType', '
				$("#author").keydown(function(event){
					if (
						event.keyCode != 27 && event.keyCode != 9 && event.keyCode != 13
						&& event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40
						&& event.keyCode != 35 && event.keyCode != 36
					) {
						$("#response_author_id").val("");
					}
				});
			', CClientScript::POS_READY);
			?>
		</div>
	</div>

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
		<?php echo CHtml::submitButton('Найти', array('class' => 'btn')); ?>
	</div>

<?php $this->endWidget(); ?>
	
</div>