<?php
$this->breadcrumbs=array(
	'Категории контента'=>array('index'),
	'Редактирование',
);
?>

<h1>Редактирование категории</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model, 'categories'=>$categories, 'root_id'=>$root_id)); ?>