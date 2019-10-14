<?php /** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'store-feedback-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block">Поля, отмеченные <span class="required">*</span>, обязательны.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="clearfix">
		<label>Магазин</label>
		<div class="input" style="padding-top: 10px;">
			<?php
			echo ($s = Store::model()->findByPk($model->store_id))
				? CHtml::link($s->name, $s->getLink($s->id))
				: "—";
			?>
		</div>
	</div>

	<div class="clearfix">
		<label>Автор</label>
		<div class="input" style="padding-top: 10px;">
			<?php
			echo CHtml::link($model->author->login . ' (' . $model->author->name . ')', Yii::app()->createUrl("/admin/user/view/", array("id" => $model->user_id)));
			?>
		</div>
	</div>


	<div class="clearfix">
		<label>Ответ на</label>
		<div class="input" style="padding-top: 10px;">
			<?php
			echo ($model->parent_id > 0)
				? CHtml::link("сообщение", array("/catalog2/admin/storeFeedback/view/", "id" => $model->parent_id))
				: "—";
			?>
		</div>
	</div>

	<?php echo $form->textFieldRow($model,'mark',array('class'=>'span1')); ?>

	<?php echo $form->textAreaRow($model,'message',array('class'=>'span7','maxlength'=>3000, 'rows' => 10)); ?>


	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
