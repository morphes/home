<?php Yii::app()->clientScript->registerScriptFile('/js/jquery.Jcrop.min.js');?>
<?php Yii::app()->clientScript->registerCssFile('/css/jquery.Jcrop.css');?>

<?php
$this->breadcrumbs=array(
	'Юнит Товары'=>array('index'),
	'Создание',
);
?>

<h1>Редактирование <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>