<?php
$this->breadcrumbs=array(
	'Stat Specialists',
);

$this->menu=array(
	array('label'=>'Create StatSpecialist','url'=>array('create')),
	array('label'=>'Manage StatSpecialist','url'=>array('admin')),
);
?>

<h1>Stat Specialists</h1>

<?php $this->widget('ext.bootstrap.widgets.BootListView',array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
