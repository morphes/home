<?php
$this->breadcrumbs=array(
	'Seo Rewrites'=>array('index'),
	'Список',
);

$this->menu=array(
	array('label'=>'List SeoRewrite','url'=>array('index')),
	array('label'=>'Create SeoRewrite','url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('seo-rewrite-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Seo Rewrites</h1>

<?php echo CHtml::link('Расширенный поиск','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'seo-rewrite-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'seo_url',
		array(
			'name' => 'status',
			'type' => 'raw',
			'value' => '($data->status == SeoRewrite::STATUS_ERROR) ? "<span class=\"label important\">".SeoRewrite::$statusNames[$data->status]."</span>" : "<span class=\"label\">".SeoRewrite::$statusNames[$data->status]."</span>"',
		),
		'normal_md5',
		'subdomain',
		array(
			'name'=>'desc',
			'value'=>'Amputate::getLimb($data->desc, 50);'
		),
		array(
			'name' => 'create_time',
			'value' => 'date("d.m.Y", $data->create_time)',
		),
		array(
			'name' => 'update_time',
			'value' => 'date("d.m.Y", $data->update_time)',
		),
		array(
			'class'=>'CButtonColumn',
			'template'=>'{update} {delete}',
			'updateButtonUrl'=>'SeoRewrite::getLink("update", array("normal_md5"=>$data->normal_md5));',
			'deleteButtonUrl'=>'SeoRewrite::getLink("delete", array("normal_md5"=>$data->normal_md5));',
		),
	),
)); ?>
