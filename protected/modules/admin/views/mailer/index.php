<?php
$this->breadcrumbs=array(
	'Управление почтой' => array('/admin/mailtemplate/index'),
	'Почтовые рассылки'
);
?>

<h1>Почтовые рассылки</h1>

<?php echo CHtml::button('Новая рассылка', array('onclick' => 'document.location = \'' . $this->createUrl('create') . '\'', 'class' => 'btn primary')) ?>


<?php
$this->widget('ext.bootstrap.widgets.BootGridView', array(
    'dataProvider' => $dataProvider,
    'id' => 'mailer-grid',
    //'ajaxUrl' => $this->createUrl($this->id . '/' . $this->action->id),
    'itemsCssClass' => 'condensed-table zebra-striped',
    'columns' => array(
	'id',
	array(
	    'name' => 'status',
	    'value' => 'isset(Mailer::$statusNames[$data->status]) ? Mailer::$statusNames[$data->status] : "Invalid status"',
	),
	array(
	    'name' => 'group_id',
	    'value' => 'isset($data->groups[$data->group_id]) ? $data->groups[$data->group_id] : "Invalid group"',
	),
	array(
	    'name' => 'user_status',
	    'value' => '$data->userStatus[$data->user_status]',
	),
	array(
	    'name' => 'role',
	    'value' => 'isset(Config::$userRoles[$data->role]) ? Config::$userRoles[$data->role] : "Invalid role"',
	),
	'author',
	'from',
	'subject',
	
	array(// display a column with "view", "update" and "delete" buttons
	    'class' => 'CButtonColumn',
	    'template' => '{view} {update}',
	),
    ),
));
?>
