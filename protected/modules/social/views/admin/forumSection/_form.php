<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'forum-section-form',
	'enableAjaxValidation'=>false,
	'stacked' => true
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model,'key',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->dropDownListRow($model,'theme_id', array(''=>'') + MediaTheme::getThemes()); ?>

	<?php echo $form->dropDownListRow($model, 'status', ForumSection::$statusNames); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
