<?php
$this->breadcrumbs=array(
	'Отзывы на заказы',
);
?>

<?php Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
");?>


<h1>Отзывы на заказы</h1>


<div style="margin-bottom: 15px;"><?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?></div>

<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
		'model'=>$model,
		'date_from' => $date_from,
		'date_to' => $date_to,
	)); ?>
</div><!-- search-form -->
        
	<?php
	$this->widget('ext.bootstrap.widgets.BootGridView', array(
		'id'		=> 'tender-response-grid',
		'updateSelector'=> '#dummy', // Устанавливаем пустым, чтобы без ajax'а была постраничка
		'dataProvider'	=> $dataProvider,
		'selectableRows'=> 2, // Multiple selection
		'columns' => array(
			array(
				'name' => 'Id',
				'value' => '$data->id',
			),
			array(
			    'name' => 'tenderId',
			    'value' => '$data->tender_id',
			),
			array(            
                                'name'=>'Заказ',
                                'type'=>'raw',
                                'value' => 'CHtml::link(CHtml::encode($data->tender->name), Yii::app()->createUrl("/tenders/admin/tender/view/", array("id"=>$data->tender_id)))',
                        ),
                        array(            
                                'name'=>'Автор',
                                'type'=>'raw',
                                'value' => 'CHtml::link(CHtml::encode($data->user->login), Yii::app()->createUrl("/admin/user/view/", array("id"=>$data->author_id)))',
                        ),
			array(
				'name' => 'Отклик',
				'value' => '$data->content',
			),
			array(
				'name'	=> 'Добавлен',
				'value' => 'date("d.m.Y", $data->create_time)',
				'sortable' => true
			),
			array(
				'class' => 'CButtonColumn',
				//'template' => '{view} {update}',
				'viewButtonUrl'   => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/response/view/", array("id" => $data->id))',
				'updateButtonUrl' => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/response/update/", array("id" => $data->id))',
				'deleteButtonUrl' => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/response/delete/", array("id" => $data->id))',
			),
		),
	));
	?>