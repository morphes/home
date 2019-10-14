<?php
$this->breadcrumbs=array(
	'Отзывы и рекомендации',
);
?>

<h1>Отзывы и рекомендации</h1>

<?php Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
");?>

<div style="margin-bottom: 15px;"><?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?></div>

<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
	'model'=>$model,
	'date_from'=>$date_from,
	'date_to'=>$date_to,
)); ?>
</div><!-- search-form -->

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
			'name'=>'spec_id',
			'type'=>'raw',
			'value' => 'CHtml::link(CHtml::encode($data->getSpecialist()->login), Yii::app()->createUrl("/admin/user/view/", array("id"=>$data->spec_id)))',
			'sortable' => false
		),
		array(
			'name'=>'author_id',
			'type'=>'raw',
			'value' => 'CHtml::link(CHtml::encode($data->getAuthor()->login), Yii::app()->createUrl("/admin/user/view/", array("id"=>$data->author_id)))',
			'sortable' => false
		),
		array(
			'name' => 'status',
			'value' => 'Review::$statusNames[$data->status]',
			'sortable' => false
		),
		array(
			'name' => 'type',
			'value' => 'Review::$typeNames[$data->type]',
			'sortable' => false
		),
		array(
			'name' => 'rating',
			'value' => '$data->rating',
			'sortable' => false,
		),
		array(
			'name'	=> 'Добавлен',
			'value' => 'date("d.m.Y", $data->create_time)',
			'sortable' => false
		),
		array(
			'name'	=> 'Обновлен',
			'value' => 'date("d.m.Y", $data->update_time)',
			'sortable' => false
		),
		array(
			'class' => 'CButtonColumn',
			'viewButtonUrl'   => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/review/view/", array("id" => $data->id))',
			'updateButtonUrl' => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/review/update/", array("id" => $data->id))',
			'deleteButtonUrl' => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/review/delete/", array("id" => $data->id))',
		),
	),
));
?>
