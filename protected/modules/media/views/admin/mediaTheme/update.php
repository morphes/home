<?php
$this->breadcrumbs=array(
	'Медиа тематики'=>array('index'),
	'Редактирование',
);
?>

<h1>Редактирование тематики #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>