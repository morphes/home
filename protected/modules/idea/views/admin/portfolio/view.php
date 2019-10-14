<?php
$this->breadcrumbs=array(
	'Портфолио'=>array('index'),
	'Список' => array('index'),
	'Просмотр'
);
?>

<?php
$this->menu=array(
	array('label'=>'List Portfolio','url'=>array('index')),
	array('label'=>'Create Portfolio','url'=>array('create')),
	array('label'=>'Update Portfolio','url'=>array('update','id'=>$model->id)),
	array('label'=>'Delete Portfolio','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Portfolio','url'=>array('admin')),
);
?>

<h1>Портфолио #<?php echo $model->id;?> - "<?php echo $model->name; ?>"</h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'name',
		array(
			'label'=>'Автор',
			'type'=>'html',
			'value'=>CHtml::link($model->author->login.' ('.$model->author->name.')', $this->createUrl('/member/profile/user/', array('id' => $model->author->id))),
		),
		array(
			'name' => 'status',
			'type' => 'html',
			'value' => "<span class='label success'>".Portfolio::$statusNames[$model->status].'</span>',
		),
		array(
			'label'=>'Дата создания',
			'type'=>'raw',
			'value'=>date('d.m.Y H:i', $model->create_time),
		),
		array(
			'label'=>'Дата обновления',
			'type'=>'raw',
			'value'=>date('d.m.Y H:i', $model->update_time),
		),
		array(
			'name' => 'Услуга',
			'value' => $model->getServiceName($model->service_id)
		),

		'desc',
	),
)); ?>

<div class="well">

	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Фотографии', true); ?>

	<?php foreach($model->images as $img) : ?>
	<div class="row" style="margin-bottom: 20px;">
		<div class="span3">
			<img style="width:131px;" src="/<?php echo $img->file->getPreviewName(array('131', '131', 'crop', 100)); ?>">

		</div>
		<div class="span6">
			<strong>Описание</strong><br>
			<?php echo nl2br(CHtml::encode($img->file->desc)); ?>
		</div>
	</div>
	<?php endforeach; ?>

</div>

<div class="actions">

	<?php echo CHtml::button('Редактировать', array('class'=>'primary btn',
		'onclick' => "document.location='{$this->createUrl('/idea/admin/portfolio/update', array('id' => $model->id))}'"
	)); ?>
	<?php echo CHtml::button('Удалить', array('class'=>'danger btn','onclick' => 'if (!confirm("Удалить")) { return false; } else { document.location="/idea/admin/portfolio/delete/id/'.$model->id.'" }')); ?>
</div>