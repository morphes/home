<?php
$this->breadcrumbs=array(
	'Promocodes'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Promocode', 'url'=>array('index')),
	array('label'=>'Manage Promocode', 'url'=>array('admin')),
);
?>

<h1>Create Promocode</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>