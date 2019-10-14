<?php
$this->breadcrumbs=array(
	'Разделы форума'=>array('index'),
	'Редактирование «'.$model->name.'»',
);
?>

<h1>Редактирование раздела #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>