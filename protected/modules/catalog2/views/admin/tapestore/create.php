<?php
$this->breadcrumbs=array(
	'Лента логотипов'=>array('index'),
	'Создание',
);

?>

<h1>Создать логотип</h1>

<?php echo $this->renderPartial('_form', array(
	'model'=>$model,
	'sCategory'=>$sCategory,
)); ?>