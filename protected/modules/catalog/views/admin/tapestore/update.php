<?php
$this->breadcrumbs=array(
	'Лента логотипов'=>array('index'),
	'Редактирование',
);
?>

<h1>Редактирование логотипов <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array(
	'model'=>$model,
	'sCategory'=>$sCategory,
)); ?>