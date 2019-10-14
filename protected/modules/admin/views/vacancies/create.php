<?php
$this->breadcrumbs=array(
	'Вакансии'=>array('index'),
	'Добавление',
);
?>

<h1>Добавление вакансии</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model, 'countVacancies' => $countVacancies)); ?>