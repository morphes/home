<?php
/* @var $this IndexServicePhotoController */
/* @var $model IndexServicePhoto */

$this->breadcrumbs=array(
	'Index Service Photos'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List IndexServicePhoto', 'url'=>array('index')),
	array('label'=>'Manage IndexServicePhoto', 'url'=>array('admin')),
);
?>

<h1>Create IndexServicePhoto</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>