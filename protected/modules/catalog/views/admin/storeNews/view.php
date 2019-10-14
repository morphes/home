<?php
$this->breadcrumbs=array(
	'Новости магазинов'=>array('index'),
	$model->title,
);
?>

<h1>Просмотр новости #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		array(
			'name'  => 'status',
			'value' => $model->getStatusHtml(),
			'type'  => 'raw'
		),
		array(
			'name' => 'user_id',
			'value' => ($model->user_id) ? $model->author->name : "—"
		),
		array(
			'name' => 'image_id',
			'value' => ($model->image_id) ? "<img width=\"100\" src=\"/".$model->preview->getPreviewName(StoreNews::$preview["crop_140"])."\" >" : "—",
			'type' => 'raw'
		),
		array(
			'name' => 'store_id',
			'value' => Store::model()->findByPk($model->store_id)->name
		),
		'rating',
		'title',
		'content',
		array(
			'name'	=> 'create_time',
			'value' => date("d.m.Y H:i", $model->create_time),

		),
		array(
			'name'	=> 'update_time',
			'value' => date("d.m.Y H:i", $model->update_time),
		),
	),
)); ?>