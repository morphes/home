<?php
$this->breadcrumbs=array(
	'Specialist Rate Cities'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List SpecialistRateCity','url'=>array('index')),
	array('label'=>'Create SpecialistRateCity','url'=>array('create')),
	array('label'=>'View SpecialistRateCity','url'=>array('view','id'=>$model->id)),
	array('label'=>'Manage SpecialistRateCity','url'=>array('admin')),
);
?>

<h1>Update SpecialistRateCity <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>