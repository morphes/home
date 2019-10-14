<?php
$this->breadcrumbs=array(
	'Управление пользователями' => array('/admin/user/userlist'),
	'Группы пользователей' => array('admin'),
	'Просмотр'
);
?>


<h1>Просмотр группы #<?php echo $model->id; ?> &mdash; <?php echo CHtml::value($model, 'name');?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'desc',
		array(
                    'name'=>'create_time',
                    'value'=>date("d.m.Y H:i", $model->create_time)
                ),
		array(
                    'name'=>'update_time',
                    'value'=>date("d.m.Y H:i", $model->update_time)
                ),
	),
)); ?>

<div class="actions">
	<?php echo CHtml::link('Редактировать', array('update', 'id' => $model->id), array('class' => 'btn primary')); ?>
</div>

<h3>Список пользователей группы</h3>

<?php $this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'=>'users-grid',
	'dataProvider'=>$users,
	'columns'=>array(
                array(
                    'name'=>'id',
                    'type'=>'raw',
                    'value'=>'is_null($data->users) ? "" : $data->users->id'
                ),
                array(
                    'name'=>'login',
                    'type'=>'raw',
                    'value'=>'is_null($data->users) ? "" : $data->users->login'
                ),
                array(
                    'name'=>'email',
                    'type'=>'raw',
                    'value'=>'is_null($data->users) ? "" : $data->users->email'
                ),
		array(
                    'name'=>'name',
                    'type'=>'raw',
                    'value'=>'is_null($data->users) ? "" : $data->users->name'
                ),
		array(
                    'class'=>'CButtonColumn',
                    'template'=>'{delete}',
                    'deleteButtonUrl'=>'Yii::app()->controller->createUrl("deleteuser",array("uid"=>$data->user_id, "gid"=>$data->group_id))',
		),
	),
)); ?>