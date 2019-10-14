<?php
$this->breadcrumbs=array(
	'Вакансии'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Редактирование',
);

?>

<h1>Редактирование вакансии #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model, 'countVacancies' => $countVacancies)); ?>