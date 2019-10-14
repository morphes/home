<?php
$this->breadcrumbs=array(
	'Store Offers'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Редактирование',
);
?>

<h1>Редактирование StoreOffer <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>