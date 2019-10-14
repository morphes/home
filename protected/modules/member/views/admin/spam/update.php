<?php
$this->breadcrumbs=array(
	'Spams'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);
?>

<h1>Обработка пометки о спаме <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>