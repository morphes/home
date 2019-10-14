<?php
$this->breadcrumbs=array(
	'Store Offers'=>array('index'),
	$model->name,
);
?>

<h1>Просмотр StoreOffer #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'company',
		'city_name',
		'company_phone',
		'email',
		'name',
		'job',
		'site',
		'comment',
		'accept_rule',
		'selected_services',
		//'status',
		array(
			'name' => 'create_time',
			'value' => date('d.m.Y H:i', $model->create_time)
		),
		array(
			'name' => 'update_time',
			'value' => date('d.m.Y H:i', $model->update_time)
		),
	),
)); ?>
