<?php
$this->breadcrumbs=array(
	'Управление почтой' => array('/admin/mailtemplate/index'),
	'Почтовые рассылки' => array('index'),
	'Просмотр'
);
?>

<h1>Просмотр рассылки #<?php echo $model->id; ?></h1>

<?php $groups = array(0=>'Все')+CHtml::listData($groupList, 'id', 'name'); ?>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		array(
		    'label' => $model->getAttributeLabel('status'),
		    'value' => isset(Mailer::$statusNames[$model->status]) ? Mailer::$statusNames[$model->status] : 'Invalid status',
		),
		array(
		    'name' => 'group_id',
		    'value' => isset($groups[$model->group_id]) ? $groups[$model->group_id] : "Invalid group",
		),
		array(
		    'name' => 'user_status',
		    'value' => $model->userStatus[$model->user_status],
		),
		array(
		    'name' => 'role',
		    'value' => isset(Config::$userRoles[$model->role]) ? Config::$userRoles[$model->role] : "Invalid role",
		),
		'author',
		'from',
		'subject',
		'data:html',
		array(
			'label' => $model->getAttributeLabel('create_time'),
			'value' => date("d.m.Y", $model->create_time),
		),
		array(
			'label' => $model->getAttributeLabel('update_time'),
			'value' => date("d.m.Y", $model->update_time),
		),
	),
)); ?>

<div class="actions">
	<?php echo CHtml::button('Редактировать', array('onclick'=>'document.location = \''.$this->createUrl('update', array('id'=>$model->id)).'\'', 'class' => 'btn primary'))?>
</div>