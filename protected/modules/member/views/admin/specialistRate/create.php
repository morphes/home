<?php
$this->breadcrumbs=array(
	'Specialist Rates'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List SpecialistRate','url'=>array('index')),
	array('label'=>'Manage SpecialistRate','url'=>array('admin')),
);
?>

<h1>Create SpecialistRate</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>