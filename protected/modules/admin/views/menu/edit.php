<?php
$this->breadcrumbs=array(
	'Управление меню' => array('index'),
);

if ( ! is_null($parentModel))
{
	$this->breadcrumbs[ Menu::$menuNames[$parentModel->type_id] ] = $this->createUrl('index', array('type_id' => $parentModel->type_id));
	$this->breadcrumbs[ $parentModel->label  ] = $this->createUrl("/admin/menu/submenu", array("parent_id" => $parentModel->id));
} else {
	$this->breadcrumbs[ Menu::$menuNames[$model->type_id] ] = $this->createUrl('index', array('type_id' => $model->type_id));
}

$this->breadcrumbs[] = 'Редактирование';
?>

<?php echo CHtml::tag('h1', array(), $model->isNewRecord ? 'Добавление пункта' : 'Редактирование пункта #'.$model->id, true );?>





<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id'=>'room-form',
	'enableAjaxValidation'=>false,
)); ?>


	<?php
	if ( ! $model->isNewRecord)
		echo $form->textFieldRow($model, 'id', array('disabled' => 'disabled', 'class' => 'span2'));
	?>
	
	<?php echo $form->textFieldRow($model,'key',array('class'=>'span6', 'maxlength'=>255)); ?>
	
	<?php echo $form->textFieldRow($model,'label',array('class'=>'span6','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model,'label_hidden',array('class'=>'span8','maxlength'=>255)); ?>

	<?php echo $form->dropDownListRow($model, 'status', Menu::$statusNames); ?>
	
	<?php echo $form->textFieldRow($model,'url',array('class'=>'span6','maxlength'=>255)); ?>
	
	<?php echo $form->textAreaRow($model,'no_active_text',array('class'=>'span6', 'rows' => 5, 'maxlength'=>255)); ?>
	
	
	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', array('class' => 'btn large primary')); ?>
		<?php echo CHtml::link('Отменить', array('index'), array('class' => 'btn large'));?>
	</div>

<?php $this->endWidget(); ?>
