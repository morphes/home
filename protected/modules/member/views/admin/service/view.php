<?php
$this->breadcrumbs=array(
	'Услуги'=>array('index'),
	$model->name,
);
?>

<h1>View Service #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'parent_id',
		'type',
		'url',
		'name',
		'desc',
		'seo_top_desc',
		'seo_bottom_desc',
		'create_time',
		'update_time',
	),
)); ?>

<div class="actions">
        <?php echo CHtml::button('Редактировать',array('class'=>'btn primary','onclick'=>'document.location="/member/admin/service/update/id/' . $model->id . '"')); ?>
        <?php echo CHtml::button('Отмена',array('class'=>'btn default','onclick'=>'document.location="/member/admin/service/"')); ?>
</div>