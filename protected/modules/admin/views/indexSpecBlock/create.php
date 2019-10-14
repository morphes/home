<?php
$this->breadcrumbs=array(
	'Блок специалистов. Ссылки'=>array('index'),
	'Создание',
);

?>

<h1>Создание ссылки</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>