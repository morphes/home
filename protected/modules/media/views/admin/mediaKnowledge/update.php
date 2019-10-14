<?php
$this->breadcrumbs=array(
	'Медиа знания'=>array('index'),
	$model->title
);
?>

<h1>Редактирование знания #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model, 'authorPhoto' => $authorPhoto, 'sCategory' => $sCategory)); ?>