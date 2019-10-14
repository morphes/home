<?php
$this->breadcrumbs=array(
	'PDF-свидетельства'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Редактирование',
);

$this->menu=array(
	array('label'=>'List CopyrightFile','url'=>array('index')),
	array('label'=>'Create CopyrightFile','url'=>array('create')),
	array('label'=>'View CopyrightFile','url'=>array('view','id'=>$model->id)),
	array('label'=>'Manage CopyrightFile','url'=>array('admin')),
);
?>

<h1>Редактирование PDF-свидетельства <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>