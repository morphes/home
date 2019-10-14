<?php
$this->breadcrumbs=array(
	'Комментарии' => array('index'),
	'Редактирование'
);
?>

<h1>Редактирование комментария #<?php echo $model->id;?></h1>

	


<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id'=>'idea-type-form',
	'enableAjaxValidation'=>false,
)); ?>


	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'id', array('disabled' => 'disabled')); ?>

	<?php echo $form->dropDownListRow($model,'status', Comment::$statusLabels,array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model->author,'name', array('disabled' => 'disabled')); ?>
	
	<?php echo $form->textAreaRow($model,'message',array('rows'=>5,'class'=>'span6')); ?>
	

	<div class="actions">
		<?php echo CHtml::submitButton('Сохранить', array('class' => 'btn large primary')); ?>
		<?php echo CHtml::button('Отменить', array('onclick' => 'window.history.back()', 'class' => 'btn large')); ?>
	</div>

<?php $this->endWidget(); ?>

		