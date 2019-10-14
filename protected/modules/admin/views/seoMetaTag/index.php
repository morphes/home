<?php
$this->breadcrumbs=array(
	'Seo Meta Tags'=>array('index'),
	'Список',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('seo-meta-tag-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>SEO. Мета теги (title, description, keywords, h1)</h1>


<div style="margin-bottom: 15px;"><?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?></div>

<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div>


<?php echo CHtml::link('Добавить запись', array('/admin/seoMetaTag/create'), array('class' => 'primary btn')); ?>


<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'seo-meta-tag-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		array(
			'name' => 'url',
			'value' => '"<a href=\"".$data->url."\">".$data->url."</a>"',
			'type' => 'raw',
		),
		'url_crc32',
		'page_title',
		'description',
		'keywords',
		'h1',
		array(
			'class'=>'CButtonColumn',
			'template' => '{update}{delete}'
		),

	),
)); ?>
