<?php
/**
 * @var $model SeoRewrite
 * @var $form BootActiveForm
 */
$this->breadcrumbs=array(
	'Seo Rewrites'=>array('index'),
	'Создание',
);

?>

<h1>Создание Seo Rewrite</h1>

<?php
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'seo-rewrite-form',
	'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->errorSummary($model); ?>

<?php echo $form->textFieldRow($model,'seo_url',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->dropDownListRow($model,'status', SeoRewrite::$statusNames, array('class'=>'span5')); ?>

<?php echo $form->textAreaRow($model,'desc',array('class'=>'span5')); ?>

<?php
echo $form->uneditableRow($model,'normal_url',array('class'=>'span5'));
echo $form->hiddenField($model,'normal_url');
?>

<div class="actions">
	<?php echo CHtml::submitButton('Создать', array('class'=>'btn primary')); ?>

	<?php echo CHtml::submitButton('Создать и перейти', array('class'=>'btn info span4', 'name' => 'forward'));?>

	<?php echo CHtml::link('Отменить', array('index'), array('class' => 'btn')); ?>
</div>

<?php $this->endWidget(); ?>
