<?php
$this->breadcrumbs=array(
	'Promocodes'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List Promocode', 'url'=>array('index')),
	array('label'=>'Create Promocode', 'url'=>array('create')),
	array('label'=>'View Promocode', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Promocode', 'url'=>array('admin')),
);
?>

<h1>Update Promocode <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>