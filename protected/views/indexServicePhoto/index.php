<?php
/* @var $this IndexServicePhotoController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Index Service Photos',
);

$this->menu=array(
	array('label'=>'Create IndexServicePhoto', 'url'=>array('create')),
	array('label'=>'Manage IndexServicePhoto', 'url'=>array('admin')),
);
?>

<h1>Index Service Photos</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
