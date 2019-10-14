<?php
$this->breadcrumbs = array(
	'Идеи' => array('/idea/admin/interior/list'),
	'Свойства «' . Config::$ideaTypesName[$model->idea_type_id] . '»' => $this->createUrl($this->id . '/index', array('idea_type_id' => $model->idea_type_id)),
	'Добавление свойства'
);
?>


<?php echo CHtml::tag('h2', array(),
	$model->isNewRecord
		? 'Добавление свойства «' . Config::$ideaTypesName[$model->idea_type_id] . '»'
		: 'Редактирование свойства «' . Config::$ideaTypesName[$model->idea_type_id] . '»'
	, true
);?>

<?php
// Если у текущего элемента есть родитель, выводим о нем информацию
if (!is_null($parent_model)) {
	$link = CHtml::link(
		Config::$ideaTypesName[$parent_model->idea_type_id],
		$this->createUrl($this->id . '/index', array('idea_type_id' => $parent_model->idea_type_id)),
		array('style' => 'text-decoration: none;')
	);
	echo CHtml::tag('h2', array('style' => 'border-bottom: 2px solid black;'), $link . ' / ' . $parent_model->option_value, true);
}
?>




<?php $form = $this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id' => 'room-form',
	'enableAjaxValidation' => false,
	'stacked' => true,
)); ?>


<?php echo $form->errorSummary($model); ?>

<?php echo $form->uneditableRow($model, 'option_key'); ?>

<?php echo $form->textFieldRow($model, 'option_value', array('class' => 'span6')); ?>

<?php echo $form->textAreaRow($model, 'desc', array('class' => 'span6', 'rows' => 10)); ?>

<?php echo $form->textFieldRow($model, 'param', array('class' => 'span6', 'maxlength' => 255)); ?>


<div class="actions">
	<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', array('class' => 'btn primary large')); ?>
	<?php echo CHtml::link('Отменить', $this->createUrl($this->id . '/index', array('idea_type_id' => $model->idea_type_id)), array('class' => 'btn large'));?>
</div>

<?php $this->endWidget(); ?>

