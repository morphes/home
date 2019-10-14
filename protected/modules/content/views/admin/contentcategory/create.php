<?php
$this->breadcrumbs=array(
	'Категории контента'=>array('index'),
	'Добавление',
);

$this->menu=array(
	array('label'=>'List ContentCategory', 'url'=>array('index')),
	array('label'=>'Manage ContentCategory', 'url'=>array('admin')),
);
?>

<h1>Добавление категории</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model, 'categories'=>$categories, 'root_id'=>$root_id)); ?>