<?php
$this->breadcrumbs=array(
	'Помощь' => array('/help/admin/help/list'),
	Help::$baseNames[$baseId] => array('/help/admin/help/list', 'base'=>$baseId),
	'Популярные вопросы' => array('list', 'base'=>$baseId),

);
?>
<?php if ($faq->getIsNewRecord()) : ?>
	<h1>Добавление вопроса</h1>
<?php
	$this->breadcrumbs[] = 'Добавление вопроса';
else : ?>
	<h1>Редактирование вопроса</h1>
<?php
	$this->breadcrumbs[] = 'Редактирование вопроса';
endif; ?>



<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'help-faq-form',
	'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->errorSummary($faq); ?>

<?php echo $form->textAreaRow($faq,'question',array('class'=>'span5','maxlength'=>512)); ?>

<?php echo $form->textFieldRow($faq,'link',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->dropDownListRow($faq, 'status', HelpFaq::$statusNames, array('class'=>'span5')); ?>

<div class="actions">
	<?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary')); ?>
	<?php echo CHtml::button('Отмена', array('class'=>'btn default','onclick' => "document.location = '".$this->createUrl('/help/admin/faq/list', array('base' => $baseId))."'"));?>
</div>

<?php $this->endWidget(); ?>
