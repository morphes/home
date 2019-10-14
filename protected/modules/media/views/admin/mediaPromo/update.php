<?php
$this->breadcrumbs=array(
	'Медиа промоблок'=>array('index'),
	$model->title
);
?>

<h1>Редактирование промоблока #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>