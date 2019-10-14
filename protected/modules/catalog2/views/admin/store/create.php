<?php
$this->breadcrumbs=array(
	'Магазины'=>array('index'),
	'Новый',
);
?>

<h1>Новый магазин</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model, 'changeTypeUrl'=>$changeTypeUrl)); ?>