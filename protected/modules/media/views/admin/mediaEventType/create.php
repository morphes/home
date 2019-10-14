<?php
$this->breadcrumbs=array(
	'Медиа типы событий'=>array('index'),
	'Создание',
);

?>
<h1>Создание типа событий</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>