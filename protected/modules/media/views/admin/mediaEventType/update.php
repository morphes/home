<?php
$this->breadcrumbs=array(
	'Медиа событя'=>array('index'),
	'Редактирование',
);
?>

<h1>Редактирование события #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>