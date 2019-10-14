<?php
$this->breadcrumbs=array(
	'Каталог товаров'=>array('/catalog/admin/catgory/index'),
	'Главная товаров, список помещений'=>array('/catalog/admin/mainroom/index'),
	'Редактирование помещения',
);

?>

<h1>Редактирование помещения "<?php echo $model->name; ?>"</h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>