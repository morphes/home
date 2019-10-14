<?php
$this->breadcrumbs=array(
	'Сети магазинов'=>array('index'),
	'Редактирование',
);
?>

<h1>Редактирование сети магазинов "<?php echo $model->name; ?>"</h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>