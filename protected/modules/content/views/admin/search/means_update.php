<?php
$this->breadcrumbs=array(
        'Общий поиск ("Возможно вы искали")'=>array('admin'),
        'Редактирование',
);
?>

<h1>Редактирование "<?php echo $model->name; ?>"</h1>

<?php echo $this->renderPartial('_means_form',array('model'=>$model)); ?>