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
		'model'     => $model,
		'date_from' => $date_from,
		'date_to'   => $date_to,
		'is_bind'   => $is_bind,
		'prod_id'   => $prod_id,
		'rooms'     => $rooms,
		'room_id'   => $room_id,
	)); ?>
</div><!-- search-form -->

	<?php echo CHtml::link('Создать интерьер', array('/idea/admin/create/index'), array('class' => 'primary btn')); ?>
        
	<?php
	$this->widget('ext.bootstrap.widgets.BootGridView', array(
		'id'		=> 'interior-grid',
                'ajaxUrl'	=> $this->createUrl($this->id.'/'.$this->action->id),
		'updateSelector'=> '#dummy', // Устанавливаем пустым, чтобы без ajax'а была постраничка
		'dataProvider'	=> $dataProvider,
		'selectableRows'=> 2, // Multiple selection
		'columns' => array(
			array(
				'class' => 'CCheckBoxColumn'
			),
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
				'viewButtonUrl'   => 'Yii::app()->createUrl("/'.$this->module->id.'/'.$this->id.'/view/",	array("interior_id" => $data->id))',
				'updateButtonUrl' => "Yii::app()->createUrl('/".$this->module->id."/admin/create/interior/',	array('id' => \$data->id))",
				'deleteButtonUrl' => "Yii::app()->createUrl('/".$this->module->id."/admin/create/delete/',	array('id' => \$data->id))"
			),
		),
	));
	?>
	
	<?php echo CHtml::link('Удалить отмеченные', '#', array('class' => 'group_action group_delete'));?><br /><br />
	


<?php
Yii::app()->clientScript->registerScript('groupDelete', "

	$('.group_delete').click(function(){
	
		// Получаем список id'шек выделенных записей и отправляем.
		var arr = $.fn.yiiGridView.getSelection('interior-grid');
		
		$.post('".$this->createUrl('admin/create/delete', array('id' => 0))."', {ids:arr}, function(){
		
			// После успешных операций, обновляем табличку.
			$.fn.yiiGridView.update('interior-grid');
		});
		
		return false;
	});
");
?>