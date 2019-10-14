<?php
$this->breadcrumbs=array(
	'Seo Meta Tags'=>array('index'),
	'Редактирование',
);
?>

<h1>Редактирование SeoMetaTag <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>