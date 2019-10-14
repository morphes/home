<?php
$this->breadcrumbs=array(
	'Галереи магазинов'=>array('index'),
	'Создание',
);

?>

<h1>Добавление фотографии</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>