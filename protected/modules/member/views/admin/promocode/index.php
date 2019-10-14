<?php
$this->breadcrumbs=array(
	'Promocodes',
);

$this->menu=array(
	array('label'=>'Create Promocode', 'url'=>array('create')),
	array('label'=>'Manage Promocode', 'url'=>array('admin')),
);
?>

<h1>Промокоды</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
