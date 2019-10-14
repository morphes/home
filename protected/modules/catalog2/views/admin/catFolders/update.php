<?php
$this->breadcrumbs=array(
	'папки и миры'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Редактирование',
);

$this->menu=array(
	array('label'=>'List CatFolders','url'=>array('index')),
	array('label'=>'Create CatFolders','url'=>array('create')),
	array('label'=>'View CatFolders','url'=>array('view','id'=>$model->id)),
	array('label'=>'Manage CatFolders','url'=>array('admin')),
);
?>

<h1>Редактирование папки <?php echo $model->name; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>