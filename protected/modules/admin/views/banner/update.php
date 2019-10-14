<?php
$this->breadcrumbs=array(
	'Управление баннерами'=>array('index'),
	'Новый баннер',
);
?>

<h1>Редактирование баннера</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>