<?php
$this->breadcrumbs=array(
	'Блок идей. Фотографии'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Редактирование',
);
?>

<h1>Редактирование фотографии <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>