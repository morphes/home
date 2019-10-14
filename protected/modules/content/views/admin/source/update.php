<?php
$this->breadcrumbs=array(
	'Источники'=>array('admin'),
	$model->name=>array('view','id'=>$model->id),
	'Редактирование',
);

$this->menu=array(
	array('label'=>'List Source', 'url'=>array('index')),
	array('label'=>'Create Source', 'url'=>array('create')),
	array('label'=>'View Source', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Source', 'url'=>array('admin')),
);
?>

<h1>Редактирование Источника #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>