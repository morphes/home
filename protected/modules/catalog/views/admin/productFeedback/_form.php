<?php
/** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'feedback-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block">Поля, отмеченные <span class="required">*</span>, обязательны.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="clearfix">
		<label>Товар</label>
		<div class="input" style="padding-top: 10px;">
			<?php
			echo CHtml::link($model->product->name, Product::getLink($model->product->id));
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

	<?php echo $form->textFieldRow($model,'mark',array('class'=>'span1')); ?>

	<?php echo $form->textAreaRow($model,'merits',array('class'=>'span7', 'rows' => '6', 'maxlength'=>3000)); ?>

	<?php echo $form->textAreaRow($model,'limitations',array('class'=>'span7', 'rows' => '6', 'maxlength'=>3000)); ?>

	<?php echo $form->textAreaRow($model,'message',array('class'=>'span7', 'rows' => '6', 'maxlength'=>3000)); ?>


	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
