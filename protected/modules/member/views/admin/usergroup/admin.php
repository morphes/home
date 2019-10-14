<?php
$this->breadcrumbs=array(
	'Управление пользователями' => array('/admin/user/userlist'),
	'Группы пользователей'
);
?>


<?php

Yii::app()->clientScript->registerScript('search', "
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('usergroup-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Группы пользователей</h1>

<?php echo CHtml::link('Создать группу', $this->createUrl('create'), array('class' => 'btn primary'))?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'=>'usergroup-grid',
	'dataProvider'=>$model->search(),
	'itemsCssClass' => 'zebra-striped',
	'columns'=>array(
		'id',
		'name',
		'desc',
		array(
			'name' => 'Человек',
			'value' => '$data->userCount'
		),
		array(
                    'name'=>'create_time',
                    'type'=>'raw',
                    'value'=>'date("d.m.Y H:i", $data->create_time)'
                ),
		array(
                    'name'=>'update_time',
                    'type'=>'raw',
                    'value'=>'date("d.m.Y H:i", $data->update_time)'
                ),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
