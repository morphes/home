<?php
$this->breadcrumbs=array(
	'Отзывы на товары'=>array('index'),
	$model->id,
);
?>

<h1>Просмотр отзыва на товар #<?php echo $model->id; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'       => $model,
	'attributes' => array(
		'id',
		array(
			'label' => 'product_id',
			'type'  => 'html',
			'value' => CHtml::link($model->product->name, Product::getLink($model->product->id)),
		),
		array(
			'label' => 'Автор',
			'type'  => 'html',
			'value' => CHtml::link($model->author->login . ' (' . $model->author->name . ')', Yii::app()->createUrl("/admin/user/view/", array("id" => $model->user_id))),
		),
		'mark',
		array(
			'name'  => 'merits',
			'value' => nl2br(CHtml::encode($model->merits)),
			'type' => 'raw'
		),
		array(
			'name'  => 'limitations',
			'value' => nl2br(CHtml::encode($model->limitations)),
			'type' => 'raw'
		),
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