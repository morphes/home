<?php
$this->breadcrumbs=array(
	'Adv Questions'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List AdvQuestion','url'=>array('index')),
	array('label'=>'Create AdvQuestion','url'=>array('create')),
	array('label'=>'View AdvQuestion','url'=>array('view','id'=>$model->id)),
	array('label'=>'Manage AdvQuestion','url'=>array('admin')),
);
?>

<h1>Update AdvQuestion <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>