<?php
$this->breadcrumbs=array(
	'Лента брендов'=>array('index'),
	'Редактирование',
);
?>

<h1>Редактирование бренда <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>