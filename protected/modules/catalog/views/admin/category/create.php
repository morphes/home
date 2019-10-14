<?php
$this->breadcrumbs=array(
        'Каталог товаров'=>array('#'),
        'Категории'=>array('index'),
        'Создание',
);
?>

<h1>Создание категории</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model, 'root'=>$root)); ?>