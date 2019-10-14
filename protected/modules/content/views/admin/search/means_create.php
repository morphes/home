<?php
$this->breadcrumbs=array(
        'Общий поиск ("Возможно вы искали")'=>array('admin'),
	'Добавление',
);
?>

<h1>Добавление в базу "возможно вы искали"</h1>

<?php echo $this->renderPartial('_means_form', array('model'=>$model)); ?>