<?php
$this->breadcrumbs=array(
	'Идеи'=>array('list'),
	'Общественные интерьеры',
);
?>

<?php Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
");?>


<h1>Общественные интерьеры</h1>


<div style="margin-bottom: 15px;"><?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?></div>

<div class="search-form" style="display:none">
	<?php $this->renderPartial('_search',array(
		'model'=>$model,
                'date_from'=>$date_from,
                'date_to'=>$date_to,
	)); ?>
</div><!-- search-form -->

	<?php echo CHtml::link('Создать интерьер (обществ.)', array('/idea/admin/interiorpublic/create'), array('class' => 'primary btn')); ?>
        
	<?php
	$this->widget('ext.bootstrap.widgets.BootGridView', array(
		'id'		=> 'interiorpublic-grid',
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
                                'value' => 'CHtml::link(CHtml::encode($data->name), Yii::app()->createUrl("/idea/admin/interiorpublic/update/", array("id"=>$data->id)))',
                        ),
                        array(            
                                'name'=>'author',
                                'type'=>'raw',
                                'value' => 'CHtml::link(CHtml::encode($data->author->login), Yii::app()->createUrl("/admin/user/view/", array("id"=>$data->author_id)))',
                        ),
			array(
				'name' => 'Тип строения',
				'value' => '"<strong>".$data->getObject()->option_value."</strong><br>".$data->getBuild()->option_value',
				'type' => 'raw'
			),
			array(
				'name' => 'status',
				'value' => 'Interiorpublic::$statusNames[$data->status]',
			),
			array(
				'name'	=> 'Добавлен',
				'value' => 'date("d.m.Y", $data->create_time)',
				'sortable' => true
			),
			array(
				'class' => 'CButtonColumn',
				'viewButtonUrl'   => 'Yii::app()->createUrl("/'.$this->module->id.'/'.$this->id.'/view/",	array("id" => $data->id))',
				'updateButtonUrl' => "Yii::app()->createUrl('/".$this->module->id."/admin/interiorpublic/update/',	array('id' => \$data->id))",
				'deleteButtonUrl' => "Yii::app()->createUrl('/".$this->module->id."/admin/interiorpublic/delete/',	array('id' => \$data->id))"
			),
		),
	));
	?>
	
	<?php echo CHtml::link('Удалить отмеченные', '#', array('class' => 'group_action group_delete'));?><br /><br />
	


<?php
Yii::app()->clientScript->registerScript('groupDelete', "

	$('.group_delete').click(function(){

		if (confirm('Удалить отмеченные?'))
		{
			// Получаем список id'шек выделенных записей и отправляем.
			var arr = $.fn.yiiGridView.getSelection('interiorpublic-grid');

			$.post('".$this->createUrl('admin/interiorpublic/delete', array('id' => 0))."', {ids:arr}, function(){

				// После успешных операций, обновляем табличку.
				$.fn.yiiGridView.update('interiorpublic-grid');
			});
		}
		return false;
	});
");
?>