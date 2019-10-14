<?php
$this->breadcrumbs=array(
	'Media Themes'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List MediaTheme','url'=>array('index')),
	array('label'=>'Manage MediaTheme','url'=>array('admin')),
);
?>

<h1>Create MediaTheme</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>