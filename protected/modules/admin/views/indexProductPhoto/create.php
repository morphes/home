<?php
$this->breadcrumbs=array(
	'Изображения для вкладок'=>array('index'),
	'Создание',
);

?>

<h1>Добавление изображения</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model, 'imgData' => $imgData)); ?>