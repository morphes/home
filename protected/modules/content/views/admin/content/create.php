<?php
$this->breadcrumbs=array(
	'Контент'=>array('index'),
	'Добавление',
);

?>

<h1>Новая статическая страница</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model, 'cats'=>$cats)); ?>