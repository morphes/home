<?php
$this->breadcrumbs=array(
	'Идеи'=>array('list'),
	'Интерьеры',
);
?>

<?php Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
");?>


<h1>Интерьеры</h1>


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
		'id'		=> 'interior-grid',
                'ajaxUrl'	=> $this->createUrl($this->id.'/'.$this->action->id),
		'updateSelector'=> '#dummy', // Устанавливаем пустым, чтобы без ajax'а была постраничка
		'dataProvider'	=> $dataProvider,
		'selectableRows'=> 2, // Multiple selection
		'columns' => array(
			array(
				'name' => 'id',
				'value' => '$data->id',
			),
			array(            
                                'name'=>'name',
                                'type'=>'raw',
                                'value' => 'CHtml::link(CHtml::encode($data->name), Yii::app()->createUrl("/idea/admin/interior/view/", array("interior_id"=>$data->id)))',
                        ),
                        array(            
                                'name'=>'author',
                                'type'=>'raw',
                                'value' => 'CHtml::link(CHtml::encode($data->author->login), Yii::app()->createUrl("/admin/user/view/", array("id"=>$data->author_id)))',
                        ),
			array(
				'name' => 'Объект',
				'value' => '"Интерьер"',
			),
			array(
				'name' => 'status',
				'value' => 'Interior::$statusNames[$data->status]',
			),
			array(
				'name'	=> 'Добавлен',
				'value' => 'date("d.m.Y", $data->create_time)',
				'sortable' => true
			),
			array(
				'class' => 'CButtonColumn',
				'template' => '{view} {update}',
				'viewButtonUrl'   => 'Yii::app()->createUrl("/'.$this->module->id.'/'.$this->id.'/view/",	array("interior_id" => $data->id))',
				'updateButtonUrl' => "Yii::app()->createUrl('/".$this->module->id."/admin/create/interior/',	array('id' => \$data->id))",
			),
		),
	));
	?>