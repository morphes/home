<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'search-means-form',
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model,'url',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textAreaRow($model,'synonyms',array('class'=>'span8','maxlength'=>3000, 'style'=>'height: 150px;')); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
