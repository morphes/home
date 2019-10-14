<?php
$this->breadcrumbs=array(
	'Управление почтой' => array('index'),
	'Шаблоны сообщений'
);
?>

<h1>Шаблоны сообщений</h1>

<?php echo CHtml::link('Новый шаблон', array('create'), array('class' => 'btn primary'));?>

<?php
$this->widget('ext.bootstrap.widgets.BootGridView', array(
	'dataProvider' => $dataProvider,
	'id' => 'template-grid',
	'itemsCssClass' => 'condensed-table zebra-striped',
	'columns' => array(
		'key',
		'name',
                'author',
		'from',
		'subject',
                
		array(            // display a column with "view", "update" and "delete" buttons
			'class'=>'CButtonColumn',
			'updateButtonUrl'=>'Yii::app()->controller->createUrl("update",array("key"=>$data->primaryKey))',
			'deleteButtonUrl'=>'Yii::app()->controller->createUrl("delete",array("key"=>$data->primaryKey))',
			'viewButtonUrl'=>'Yii::app()->controller->createUrl("view",array("key"=>$data->primaryKey))',
		),
	),
));
?>
