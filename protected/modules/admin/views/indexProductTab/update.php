<?php
$this->breadcrumbs=array(
	'Промоблок. Товары. Вкладки'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Редактирование',
);

?>

<h1>Редактирование вкладки #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>