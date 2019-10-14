<?php
$this->breadcrumbs=array(
	'Комментарии' => array('index'),
	'Просмотр'
);
?>

<h1>Просмотр комментария #<?php echo $model->id;?></h1>


<?php $this->widget('ext.bootstrap.widgets.BootDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'author.name',
		'message',
		'formated_create_time',
	),
)); ?>

<div class="actions">
	<?php echo CHtml::button('Редактировать', array('onclick' => 'location="'.$this->createUrl('update', array('id' => $model->id)).'"', 'class' => 'btn large primary')) ?>
	<?php echo CHtml::button('Удалить', array('onclick' => 'if (confirm("Удалить?")) { location="'.$this->createUrl('delete', array('id' => $model->id)).'" }', 'class' => 'btn large danger')) ?>
</div>
