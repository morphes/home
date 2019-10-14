<?php
$this->breadcrumbs=array(
	'Новости'=>array('admin'),
	'Добавление',
);
?>

<h1>Добавление новости</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>