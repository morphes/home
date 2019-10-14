<?php
$this->breadcrumbs = array(
	'Отзывы и рекомендации'=> array('index'),
	$review->id,
);
?>

<h1>Отзыв #<?php echo $review->id; ?></h1>

<?php

$this->widget('ext.bootstrap.widgets.BootDetailView', array(
	'data'      => $review,
	'attributes'=> array(
		array(
			'label'=> 'Автор',
			'type' => 'html',
			'value'=> CHtml::link($review->getAuthor()->login . ' (' . $review->getAuthor()->name . ')', Yii::app()->createUrl("/admin/user/view/", array("id"=>$review->author_id)) ),
		),
		array(
			'label'=>'Специалист',
			'type'=>'html',
			'value' => CHtml::link(CHtml::encode($review->getSpecialist()->login), Yii::app()->createUrl("/admin/user/view/", array("id"=>$review->spec_id))),
		),
		array(
			'label'=> 'Статус',
			'type' => 'html',
			'value'=> "<span class='label success'>" . Review::$statusNames[$review->status] . "</span>",
		),
		array(
			'label' => 'Тип',
			'type' => 'html',
			'value' => Review::$typeNames[$review->type],
		),
		array(
			'name' => 'Оценка',
			'type' => 'html',
			'value' => $review->rating,
		),
		array(
			'label'=> 'Дата создания',
			'type' => 'raw',
			'value'=> date('d.m.Y H:i', $review->create_time),
		),
		array(
			'label'=> 'Дата обновления',
			'type' => 'raw',
			'value'=> date('d.m.Y H:i', $review->update_time),
		),
		array(
			'label'=>'Отзыв',
			'type' => 'html',
			'value' => $review->message,
		),
	),
));
?>