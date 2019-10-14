<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

<?php
$models = MediaTheme::model()->findAllByAttributes(array('status' => MediaTheme::STATUS_ACTIVE));
$themes = CHtml::listData($models, 'id', 'name');
?>

	<?php echo $form->textFieldRow($model,'id',array('class'=>'span5')); ?>

	<?php echo $form->dropDownListRow($model,'status', array(''=>'')+MediaNew::$statusNames, array('class'=>'span5')); ?>

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
					'select'	=> 'js:function(event, ui) {$("#MediaNew_author_id").val(ui.item.id); }',


				),
				'htmlOptions' => array('size'=>15)
			));?>

			<?php echo $form->hiddenField($model,'author_id',array('size'=>15)); ?>

			<?php
			Yii::app()->clientScript->registerScript('loginType', '
				$("#author").keydown(function(event){
					if (
						event.keyCode != 27 && event.keyCode != 9 && event.keyCode != 13
						&& event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40
						&& event.keyCode != 35 && event.keyCode != 36
					) {
						$("#MediaNew_author_id").val("");
					}
				});
			', CClientScript::POS_READY);
			?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Опубликован от', 'public_from')?>
		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'name'=>'date_from',
			'value'	=> Yii::app()->getRequest()->getParam('date_from'),
			'language'	=> 'ru',
			'options'=>array('dateFormat'=>'dd.mm.yy'),
			'htmlOptions'=>array(
				'style'=>'width:150px;'
			),
		));?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Опубикован до', 'public_to')?>
		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'name'=>'date_to',
			'value'=> Yii::app()->getRequest()->getParam('date_to'),
			'language'	=> 'ru',
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
