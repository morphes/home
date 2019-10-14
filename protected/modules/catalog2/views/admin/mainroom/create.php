<?php

$this->breadcrumbs=array(
	'Каталог товаров'=>array('/catalog2/admin/catgory/index'),
	'Главная товаров, список помещений'=>array('/catalog2/admin/mainroom/index'),
	'Добавление помещения',
);

?>

<h1>Добавление помещения</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>