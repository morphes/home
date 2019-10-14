<?php
$this->breadcrumbs=array(
	'Галереи магазинов'=>array('index'),
	'Редактирование',
);
?>

<h1>Редактирование фотографии <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>