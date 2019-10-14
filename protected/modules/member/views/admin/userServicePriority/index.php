<?php
$this->breadcrumbs=array(
	'User Service Priorities',
);

$this->menu=array(
	array('label'=>'Create UserServicePriority','url'=>array('create')),
	array('label'=>'Manage UserServicePriority','url'=>array('admin')),
);
?>


<h1>Покупки тарифов специалистами</h1>

<?php
$this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'		=> 'review-grid',
	'updateSelector'=> '#dummy', // Устанавливаем пустым, чтобы без ajax'а была постраничка
	'dataProvider'	=> $dataProvider,
	'columns' => array(
		array(
			'name' => 'id',
			'value' => '$data->id',
			'sortable' => false
		),
		array(
			'name'=>'user_id',
			'value' => 'User::model()->findByPk($data->user_id)->name',
		),
		array(
			'name'=>'rate_id',
			'value' => '(SpecialistRate::model()->findByPk($data->rate_id)) ? SpecialistRate::model()->findByPk($data->rate_id)->name : null',
		),

		array(
			'name'=>'packet',
			'value' => '$data->packet',
		),

		array(
			'name'=>'service_id',
			'value' => '(Service::model()->findByPk($data->service_id)) ? Service::model()->findByPk($data->service_id)->name : null',

		),

		array(
			'name'=>'city_id',
			'value' => '(City::model()->findByPk($data->city_id)) ? City::model()->findByPk($data->city_id)->name : null',

		),

		array(
			'name'=>'date_start',
			'value' => 'date("H:i d.m.Y", $data->date_start)',
		),

		array(
			'name'=>'date_end',
			'value' => 'date("H:i d.m.Y", $data->date_end)',
		),

		array(
			'name'=>'status',
			'value' => 'isset(UserServicePriority::$statuses[$data->status]) ? UserServicePriority::$statuses[$data->status] : null ',
		),

	),
));


?>