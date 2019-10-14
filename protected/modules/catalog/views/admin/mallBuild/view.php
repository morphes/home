<?php
$this->breadcrumbs=array(
	'Торговые центры'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List MallBuild','url'=>array('index')),
	array('label'=>'Create MallBuild','url'=>array('create')),
	array('label'=>'Update MallBuild','url'=>array('update','id'=>$model->id)),
	array('label'=>'Delete MallBuild','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage MallBuild','url'=>array('admin')),
);
?>

<h1>ТЦ #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		array(
			'name' => 'city_id',
			'value' => $model->city->name.' ('.$model->city->region->name.', '.$model->city->country->name.')'
		),
		array(
			'name' => 'image_id',
			'type' => 'raw',
			'value' => ($model->logoFile) ? '<img src="/'.$model->logoFile->getPreviewName(MallBuild::$preview['resize_140']).'">' : 'незагружен'
		),

		'phone',
		'site',
		'address',

		array(
			'name' => 'user_id',
			'value' => $model->author->name
		),
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

<h3>Этажи</h3>

<?php
foreach ($model->floors as $floor)
{
	$this->renderPartial('_floorItemView', array('floor' => $floor));
}
?>
<div class="clear"></div>

<div class="actions">
	<?php echo CHtml::link('Редкатировать', array('/catalog/admin/mallBuild/update/', 'id' => $model->id), array('class' => 'btn primary')); ?>
</div>
