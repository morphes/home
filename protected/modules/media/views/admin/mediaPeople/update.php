<?php
$this->breadcrumbs=array(
	'Медиа люди говорят'=>array('index'),
	$model->fio
);
?>

<h1>Редактирование «Люди говорят» #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>