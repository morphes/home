<?php
$this->breadcrumbs=array(
	'Медиа события'=>array('index'),
	'Управление',
);

Yii::app()->clientScript->registerScript('search', "
	$('.search-button').click(function(){
		$('.search-form').toggle();
			return false;
		});
		$('.search-form form').submit(function(){
			$.fn.yiiGridView.update('media-event-type-grid', {
			data: $(this).serialize()
		});
		return false;
	});
");
?>

<h1>Медиа события</h1>


<div style="margin-bottom: 15px;"><?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?></div>

<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
	'model'=>$model,
	'themes'=>$themes,
	'eventTypes'=>$eventTypes,
)); ?>
</div>

<?php echo CHtml::link('Создать событие', array('/media/admin/mediaEvent/create'), array('class' => 'primary btn')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'media-event-type-grid',
	'updateSelector'=> '#dummy', // Устанавливаем пустым, чтобы без ajax'а была постраничка
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		array(
			'name'=>'name',
			'type'=>'raw',
			'value'=>'CHtml::link($data->name, $data->getElementLink())',
			'sortable'=>true,
		),
		array(
			'name'=>'status',
			'value'=>'MediaEvent::$statusNames[$data->status]',
			'sortable'=>true,
		),
		array(
			'name'=>'author_id',
			'value'=>'$data->author->name',
			'sortable'=>true,
		),
		array(
			'name'	=> 'public_time',
			'value' => 'date("d.m.Y H:i:s", $data->public_time)',
			'sortable' => true
		),
		array(
			'name'	=> 'start_time',
			'value' => 'date("d.m.Y H:i:s", $data->create_time)',
			'sortable' => true
		),
		array(
			'name'	=> 'end_time',
			'value' => 'date("d.m.Y H:i:s", $data->update_time)',
			'sortable' => true
		),
		array(
			'class'=>'CButtonColumn',
			'template' => '{update} {delete}'
		),
	),
)); ?>
