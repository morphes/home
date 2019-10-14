<?php
$this->breadcrumbs=array(
	'Новости магазинов'=>array('index'),
	'Редактирование',
);
?>

<h1>Редактирование новости магазина <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>