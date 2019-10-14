<?php
$this->breadcrumbs=array(
	'Заказы',
);
?>

<?php Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
");?>

<style type="text/css">
	.current-status{ color: red; font-weight: bold; }
	.tender-status, .bottom-buttons span { border-bottom: 1px dashed blue; border-color: #555555; cursor: pointer; }
	.tender-status:hover, .bottom-buttons span:hover { color: black; border-color: black; }
</style>

<h1>Заказы</h1>

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
		'id'		=> 'tender-grid',
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
                                'value' => 'CHtml::link(CHtml::encode($data->name), Yii::app()->createUrl("/tenders/admin/tender/view/", array("id"=>$data->id)))',
                        ),
                        array(            
                                'name'=>'author',
                                'type'=>'raw',
                                'value' => '(is_null($data->author_id)) ? "Гость" : CHtml::link(CHtml::encode($data->user->login), Yii::app()->createUrl("/admin/user/view/", array("id"=>$data->author_id)))',
                        ),
			array(
				'name' => 'response_count',
				'value' => '$data->response_count',
			),
			array(
				'name' => 'status',
				'value' => 'Tender::$statusNames[$data->status]',
			),
			array(
				'name'=>'send_notify',
				'value'=>'Tender::$notifyNames[$data->send_notify]',
				'type'=>'raw',
				'cssClassExpression'=>'"cell-status-tender-".$data->id',
			),
			array(
				'name'	=> 'Срок действия',
				'value' => 'date("d.m.Y", $data->expire)',
				'sortable' => true
			),
			array(
				'name'	=> 'Добавлен',
				'value' => 'date("d.m.Y", $data->create_time)',
				'sortable' => true
			),
			array(
				'class' => 'CButtonColumn',
				'viewButtonUrl'   => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/tender/view/", array("id" => $data->id))',
				'updateButtonUrl' => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/tender/update/", array("id" => $data->id))',
				'deleteButtonUrl' => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/tender/delete/", array("id" => $data->id))',
			),
		),
	));
	?>