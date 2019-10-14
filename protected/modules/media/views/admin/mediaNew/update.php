<?php
$this->breadcrumbs=array(
	'Медиа новости'=>array('index'),
	$model->title
);
?>

<h1>Редактирование новости #<?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model, 'authorPhoto' => $authorPhoto,'sCategory' => $sCategory)); ?>