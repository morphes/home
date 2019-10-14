<?php
$this->breadcrumbs=array(
	'Вакансии'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List Vacancies','url'=>array('index')),
	array('label'=>'Create Vacancies','url'=>array('create')),
	array('label'=>'Update Vacancies','url'=>array('update','id'=>$model->id)),
	array('label'=>'Delete Vacancies','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Vacancies','url'=>array('admin')),
);
?>

<h1>Просмотр вакансии #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'position',
		'key',
		'name',
		array(
			'name' => 'text',
			'type' => 'html',
		),
		
		'wage',
		array(
			'name' => 'status',
			'type' => 'raw',
			'value' => '<span class="label notice">'.Vacancies::$nameStatus[ $model->status ].'</span>'
		),
	),
)); ?>

<div class="actions">
	<?php echo CHtml::link('Редактировать', array('update', 'id' => $model->id), array('class' => 'btn primary') );?>
	
	<?php
	echo CHtml::link(
		'Удалить',
		array('delete', 'id' => $model->id),
		array(
			'class' => 'btn danger',
			'style' => 'float: right;',
			'onclick' => '
				if (confirm("Удалить вакансию?"))
					return true;
				else
					return false;
			'
	));
	?>
</div>
