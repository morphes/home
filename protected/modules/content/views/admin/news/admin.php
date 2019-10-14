<?php
$this->breadcrumbs=array(
	'Новости'=>array('admin'),
	'Список',
);
?>

<h1>Новости</h1>

<?php echo CHtml::link('Добавить новость', array('create'), array('class' => 'btn primary')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'news-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
                'title',
		array(
                    'name'=>'status',
                    'type'=>'raw',
                    'value'=>'News::$status[$data->status]',
                ),
                array(
                    'name'=>'user_id',
                    'type'=>'raw',
                    'value'=>'$data->user->login',
                ),
		array(
                    'name'=>'create_time',
                    'type'=>'raw',
                    'value'=>'date("d.m.Y H:i", $data->create_time)',
                ),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
