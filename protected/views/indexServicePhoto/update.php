<?php
/* @var $this IndexServicePhotoController */
/* @var $model IndexServicePhoto */

$this->breadcrumbs=array(
	'Index Service Photos'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List IndexServicePhoto', 'url'=>array('index')),
	array('label'=>'Create IndexServicePhoto', 'url'=>array('create')),
	array('label'=>'View IndexServicePhoto', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage IndexServicePhoto', 'url'=>array('admin')),
);
?>

<h1>Update IndexServicePhoto <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>