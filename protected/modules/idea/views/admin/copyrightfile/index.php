<?php
$this->breadcrumbs=array(
	'PDF-свидетельства'=>array('index'),
	'Список',
);

$this->menu=array(
	array('label'=>'List CopyrightFile','url'=>array('index')),
	array('label'=>'Create CopyrightFile','url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('copyright-file-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>PDF-свидетельства</h1>


<?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'copyright-file-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		'number',
		'name',
		
		array(
			'name' => 'author_id',
			'value' => '$data->author->getName()." (".$data->author->login.")"' ,
		),
		'interior_id',
		
		array(
			'name' => 'create_time',
			'value' => 'date("d.m.Y", $data->create_time)'
		),
		array(
			'class'=>'CButtonColumn',
			'template' => '{download} {view} {delete}',
			'buttons' => array(
				'download' => array(
					'label' => 'Скачать',
					'url' => '"/download/pdfdeposition/".$data->id',
					'imageUrl' => '/img/download.png', 
				)
			),
			
			
		),
	),
)); ?>
