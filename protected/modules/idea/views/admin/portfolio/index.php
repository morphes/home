<?php
$this->breadcrumbs=array(
	'Портфолио'=>array('index'),
	'Список',
);


Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
");
?>

<h1>Портфолио</h1>

<div style="margin-bottom: 15px;"><?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?></div>

<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
		'model'		=> $model,
		'date_from' 	=> $date_from,
		'date_to' 	=> $date_to,
	)); ?>
</div><!-- search-form -->

<?php echo CHtml::link('Добавить портфолио', $this->createUrl('/idea/admin/portfolio/create'), array('class'=>'primary btn')); ?>


<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'portfolio-grid',
	'dataProvider'=>$dataProvider,
	'updateSelector'=> '#dummy', // Устанавливаем пустым, чтобы без ajax'а была постраничка
	'selectableRows'=> 2, // Multiple selection
	'columns'=>array(
		array(
			'class' => 'CCheckBoxColumn'
		),
		'id',
		array(
			'name'=>'Автор',
			'type'=>'raw',
			'value' => 'CHtml::link(CHtml::encode($data->author->login), Yii::app()->createUrl("/admin/user/view/", array("id"=>$data->author_id)))',
		),
		array(
			'name' => 'Услуга',
			'value' => '$data->getServiceName($data->service_id)'
		),
		'name',
		array(
			'name' => 'status',
			'value' => 'Portfolio::$statusNames[$data->status]',
		),
		array(
			'name'	=> 'Добавлен',
			'value' => 'date("d.m.Y", $data->create_time)',
			'sortable' => true
		),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
