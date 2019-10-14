<?php
$this->breadcrumbs=array(
	'Блок идей. Фотографии'=>array('index'),
	'Создание',
);

?>

<h1>Добавление фото</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>