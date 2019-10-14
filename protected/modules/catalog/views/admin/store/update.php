<?php
$this->breadcrumbs=array(
	'Магазины'=>array('index'),
	'Редактирование',
);
?>

<h1>Редактирование магазина "<?php echo $model->name; ?>"</h1>

<?php echo $this->renderPartial(
	'_form',
	array(
		'model'         => $model,
		'changeTypeUrl' => $changeTypeUrl,
		'city'          => $city
	)
); ?>