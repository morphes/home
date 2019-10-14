<?php
$this->breadcrumbs=array(
	'Specialist Rates',
);

$this->menu=array(
	array('label'=>'Create SpecialistRate','url'=>array('create')),
	array('label'=>'Manage SpecialistRate','url'=>array('admin')),
);
?>



<h1>Тарифы приоритезации специалистов</h1>

<div>
	<?php echo CHtml::button('Новый тариф', array('class'=>'btn primary', 'onclick'=>'document.location = "/member/admin/specialistRate/create"'))?>
</div>

<?php
$this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'		=> 'review-grid',
	'updateSelector'=> '#dummy', // Устанавливаем пустым, чтобы без ajax'а была постраничка
	'dataProvider'	=> $dataProvider,
	'selectableRows'=> 2, // Multiple selection
	'columns' => array(
		array(
			'name' => 'id',
			'value' => '$data->id',
			'sortable' => false
		),
		array(
			'name'=>'name',
			'value' => '$data->name',
		),
		array(
			'name'=>'packet_3',
			'value' => '$data->packet_3',
		),
		array(
			'name' => 'discount_3',
			'value' => '$data->discount_3',

		),
		array(
			'name'=>'packet_7',
			'value' => '$data->packet_7',
		),
		array(
			'name' => 'discount_7',
			'value' => '$data->discount_7',

		),
		array(
			'name'=>'packet_14',
			'value' => '$data->packet_14',
		),
		array(
			'name' => 'discount_14',
			'value' => '$data->discount_14',

		),
		array(
			'class'=>'CButtonColumn',
			'template'=>'{update} {delete} {service}',
			'buttons' => array(
				'service' => array(
					'label'=>'Привязать услуги, города.',
					'imageUrl' => '/img/admin/small/circle_green.png',
					'url'=>'Yii::app()->createUrl("/member/admin/specialistRateCity/index", array("id"=>$data->id))',
				),
			),
			'htmlOptions' => array('style' => 'width: 80px;')
		),



	),
));
?>
