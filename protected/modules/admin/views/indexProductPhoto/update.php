<?php
$this->breadcrumbs=array(
	'Изображения для вкладок'=>array('index'),
	'Редактирование',
);
?>

<h1>Редактирование IndexProductPhoto <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model, 'imgData' => $imgData)); ?>