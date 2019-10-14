<?php
$this->breadcrumbs=array(
	'Отзывы на магазины'=>array('index'),
	$model->id,
);
?>

<h1>Просмотр StoreFeedback #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		array(
			'label' => 'Автор',
			'type'  => 'html',
			'value' => CHtml::link($model->author->login . ' (' . $model->author->name . ')', Yii::app()->createUrl("/admin/user/view/", array("id" => $model->user_id))),
		),
		array(
			'name'  => 'store_id',
			'value' => ($s = Store::model()->findByPk($model->store_id))
				   ? CHtml::link($s->name, $s->getLink($s->id))
				   : "—",
			'type'  => 'raw'
		),
		array(
			'name' => 'parent_id',
			'value' => ($model->parent_id > 0)
				   ? CHtml::link("сообщение", array("/catalog/admin/storeFeedback/view/", "id" => $model->parent_id))
				   : "",
			'type' => 'raw'
		),
		'mark',
		array(
			'name'  => 'message',
			'value' => nl2br(CHtml::encode($model->message)),
			'type' => 'raw'
		),
		array(
			'name'  => 'create_time',
			'value' => date("d.m.Y H:i:s", $model->create_time)
		),
		array(
			'name'  => 'update_time',
			'value' => date("d.m.Y H:i:s", $model->update_time),
		),
	),
)); ?>


<div class="actions">
	<?php echo CHtml::button(
		'Редактировать',
		array(
			'onclick' => 'document.location = \''.$this->createUrl($this->id.'/update', array('id'=>$model->id)).'\'',
			'class' => 'btn primary'
		)
	);?>
</div>