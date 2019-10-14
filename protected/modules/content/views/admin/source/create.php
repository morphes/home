<?php
$this->breadcrumbs=array(
	'Источники'=>array('admin'),
	'Добавление',
);

$this->menu=array(
	array('label'=>'Список Источников', 'url'=>array('index')),
	array('label'=>'Управление Источниками', 'url'=>array('admin')),
);
?>

<h1>Добавление Источника</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>