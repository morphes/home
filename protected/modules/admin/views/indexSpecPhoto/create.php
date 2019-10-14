<?php
$this->breadcrumbs=array(
	'Блок специалистов. Фотографии'=>array('index'),
	'Создание',
);

?>

<h1>Добавление фотографии</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>