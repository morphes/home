<?php
$this->breadcrumbs=array(
	'Помощь' => array('/help/admin/help/list'),
	Help::$baseNames[$baseId] => array('/help/admin/help/list', 'base'=>$baseId),
	'Разделы' => array('list', 'base'=>$baseId),

);
?>
<h2><?php echo Help::$baseNames[$baseId]; ?> / </h2>
<?php if ($section->getIsNewRecord()) : ?>
	<h2>Добавление раздела</h2>
<?php
	$this->breadcrumbs[] = 'Добавление раздела';
else : ?>
	<h2>Редактирование раздела</h2>
<?php
	$this->breadcrumbs[] = 'Редактирование раздела';
endif; ?>



<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'help-section-form',
	'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->errorSummary($section); ?>

<?php echo $form->textFieldRow($section,'name',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->dropDownListRow($section, 'status', HelpSection::$statusNames, array('class'=>'span5')); ?>

<div class="actions">
	<?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary')); ?>
	<?php echo CHtml::button('Отмена', array('class'=>'btn default','onclick' => "document.location = '".$this->createUrl('/help/admin/section/list', array('base' => $baseId))."'"));?>
</div>

<?php $this->endWidget(); ?>
