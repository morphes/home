<?php
$this->breadcrumbs=array(
	'Медиа тематики'=>array('index'),
	'Создание',
);

?>

<h1>Создание тематики</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>