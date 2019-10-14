<?php
$this->breadcrumbs=array(
	'PDF-свидетельства'=>array('index'),
	'Добавление',
);

$this->menu=array(
	array('label'=>'List CopyrightFile','url'=>array('index')),
	array('label'=>'Manage CopyrightFile','url'=>array('admin')),
);
?>

<h1>Добавление PDF-свидетельства</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>