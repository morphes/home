<?php
$this->breadcrumbs=array(
        'Каталог товаров'=>array('#'),
        'Категории'=>array('index'),
        'Редактирование',
);
?>

<h1>Редактирование категории #<?php echo $model->id; ?> (<?php echo $model->name; ?>) </h1>

<?php echo $this->renderPartial('_form',array('model'=>$model,'root'=>$root, 'errors'=>$errors)); ?>

