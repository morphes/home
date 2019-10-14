<?php
$this->breadcrumbs=array(
	'Media Themes'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List MediaTheme','url'=>array('index')),
	array('label'=>'Create MediaTheme','url'=>array('create')),
	array('label'=>'View MediaTheme','url'=>array('view','id'=>$model->id)),
	array('label'=>'Manage MediaTheme','url'=>array('admin')),
);
?>

<h1>Update MediaTheme <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>