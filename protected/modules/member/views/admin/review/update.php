<?php
$this->breadcrumbs=array(
	'Отзывы и рекомендации'=>array('index'),
	'Редактирование отзыва',
);
?>

<h1>Редактирование отзыва #<?php echo $review->id; ?>


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
	),
));
?>


<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'review-form',
	'enableAjaxValidation'=>false,
	'htmlOptions' => array('class' => 'form-project-add'),
	'stacked'=>true,
)); ?>

        <?php echo $form->errorSummary($review); ?>

                <div class="well" style="background-color: #F9F9F9;">

			<?php echo $form->textAreaRow($review, 'message', array('class'=>'span12', 'style'=>'height:150px;')); ?>

		</div>

        <?php echo $form->dropDownListRow($review, 'status', Review::$statusNames); ?>

	<?php echo CHtml::tag('hr', array('style' => 'height: 2px; background-color: black;')); ?>

        <div class="actions">
		<?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary large')); ?>
		<?php echo CHtml::button('Отмена', array('class'=>'btn default large','onclick' => "document.location = '".$this->createUrl('/member/admin/review/view', array('id' => $review->id))."'"));?>
	</div>

<?php $this->endWidget(); ?>