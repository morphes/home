<?php
$this->breadcrumbs=array(
	'Новости'=>array('admin'),
	$model->title=>array('view','id'=>$model->id),
	'Редактирование',
);
?>

<h1>Редактирование новости #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>