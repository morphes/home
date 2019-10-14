<?php
$this->breadcrumbs=array(
	'Новости'=>array('admin'),
	$model->title,
);
?>

<h1>Просмотр новости #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		array(
                    'label'=>$model->getAttributeLabel('public_time'),
                    'type'=>'raw',
                    'value'=>date('d.m.Y H:i', $model->public_time),
                ),
                array(
                    'label'=>$model->getAttributeLabel('status'),
                    'type'=>'raw',
                    'value'=>News::$status[$model->status],
                ),
		array(
                    'label'=>$model->getAttributeLabel('user_id'),
                    'type'=>'raw',
                    'value'=>$model->user->login,
                ),
		'title',
		array(
                    'label'=>$model->getAttributeLabel('content'),
                    'type'=>'html',
                    'value'=>$model->content,
                ),
		array(
                    'label'=>$model->getAttributeLabel('create_time'),
                    'type'=>'raw',
                    'value'=>date('d.m.Y H:i', $model->create_time),
                ),
		array(
                    'label'=>$model->getAttributeLabel('update_time'),
                    'type'=>'raw',
                    'value'=>date('d.m.Y H:i', $model->update_time),
                ),
	),
)); ?>


<div class="actions">
	<?php echo CHtml::link('Редактировать', array('update', 'id' => $model->id), array('class' => 'btn primary'));?>
</div>