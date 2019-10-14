<?php
$this->breadcrumbs=array(
	'Торговые центры'=>array('index'),
	'Добавление',
);
?>

<h1>Добавление ТЦ</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model, 'workTime' => $workTime)); ?>