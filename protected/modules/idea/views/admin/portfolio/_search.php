<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<?php echo $form->textFieldRow($model,'id',array('class'=>'span5', 'hint'=>'Можно указать несколько ID, разделив их запятой')); ?>

	<div class="clearfix">
		<?php echo CHtml::label('Логин / ФИО', 'author'); ?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'		=> 'author',
				'id'		=> 'author',
				'sourceUrl'	=> '/utility/autocompleteuser',
				'value'        => isset($model->author->name) ? $model->author->name . " ({$model->author->login})" : '',
				'options'	=> array(
					'showAnim'	=> 'fold',
					'delay'		=> 0,
					'autoFocus'	=> true,
					'select'	=> 'js:function(event, ui) {$("#Portfolio_author_id").val(ui.item.id); }',


				),
				'htmlOptions' => array('class'=>'span5')
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
						$("#Portfolio_author_id").val("");
					}
				});
			', CClientScript::POS_READY);
			?>
		</div>
	</div>

	<?php
	// Форимируем вложенный массив списка сервисов, сгруппированных по родителю
	// В $arr[0] лежат все родительские сервисы
	$arr = CHtml::listData( Service::model()->findAll(''), 'id', 'name', 'parent_id') ;
	foreach($arr[0] as $parent_id=>$parent_name) {
		$arr[ $parent_name ] = $arr[ $parent_id ];
		unset( $arr[$parent_id] );
	}
	unset($arr[0]);
	echo $form->dropDownListRow($model, 'service_id', array('0' => 'Все')+$arr, array('class' => 'span5'));
	?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>45)); ?>

	<?php echo $form->dropDownListRow($model,'status', array('0' => 'Все')+Portfolio::$statusNames, array('class' => 'span5')); ?>

	<div class="clearfix">
		<?php echo CHtml::label('Добавлено от', 'reg_from')?>
		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'name'=>'date_from',
			'value'	=> $date_from,
			'language'	=> 'ru',
			'options'=>array('dateFormat'=>'dd.mm.yy'),
			'htmlOptions'=>array(
				'class'=>'span2'
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
				'class'=>'span2'
			),
		));?>
		</div>
	</div>

	<div class="actions">
		<?php echo CHtml::submitButton('Найти',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
