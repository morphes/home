<?php
$this->breadcrumbs=array(
	'Контент'=>array('index'),
	'Список страниц' => array('admin'),
	'Редактирование',
);
?>

<h1>Редактирование &mdash; <?php echo $model->title;?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model, 'cats' => $cats)); ?>