<?php
$this->breadcrumbs=array(
	'Услуги'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Редактирование',
);

$this->menu=array(
	array('label'=>'List Service','url'=>array('index')),
	array('label'=>'Create Service','url'=>array('create')),
	array('label'=>'View Service','url'=>array('view','id'=>$model->id)),
	array('label'=>'Manage Service','url'=>array('admin')),
);
?>

<h1>Update Service <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>