<h1>Бан по IP</h1>
<?php echo CHtml::button('+ Забанить IP', array('onclick' => 'document.location = \'' . $this->createUrl('create') . '\'')) ?>


<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider' => $dataProvider,
    'id' => 'ipban-grid',
    'columns' => array(
	array(
	    'name' => 'ip',
	    'value' => '$data->ip',
	),
	array(
	    'name' => 'expire',
	    'value' => 'Yii::app()->getDateFormatter()->formatDateTime($data->expire)',
	),
	array(
	    'name' => 'create_time',
	    'value' => 'Yii::app()->getDateFormatter()->formatDateTime($data->create_time)',
	),
	array(// display a column with "view", "update" and "delete" buttons
	    'class' => 'CButtonColumn',
	    'template' => '{delete}',
	    'deleteButtonUrl' => 'Yii::app()->controller->createUrl("delete",array("ip"=>$data->ip))',
	),
    ),
));
?>
