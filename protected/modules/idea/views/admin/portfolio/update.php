<?php
$this->breadcrumbs=array(
	'Портфолио'=>array('index'),
	'Список' => array('index'),
	'Редактирование'
);
?>


<h1>Редактирование портфолио #<?php echo $model->id; ?></h1>

<?php
$this->widget('ext.bootstrap.widgets.BootDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		array(
			'label'=>'Автор',
			'type'=>'html',
			'value'=>CHtml::link($model->author->login.' ('.$model->author->name.')', $this->createUrl('/member/profile/user/', array('id' => $model->author->id))),
		),
		array(
			'label'=>'Дата создания',
			'type'=>'raw',
			'value'=>date('d.m.Y H:i', $model->create_time),
		),
		array(
			'label'=>'Статус',
			'type'=>'html',
			'value'=>"<span class='label success'>".Portfolio::$statusNames[$model->status]."</span>",
		),
	),
));
?>


<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>