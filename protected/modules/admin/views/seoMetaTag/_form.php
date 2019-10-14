<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'seo-meta-tag-form',
	'enableAjaxValidation'=>false,
	'stacked' => true
)); ?>


	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'url',array('class'=>'span7','maxlength'=>255)); ?>

	<?php echo $form->uneditableRow($model, 'url_crc32', array('class'=>'span7')); ?>

	<?php echo $form->textFieldRow($model,'page_title',array('class'=>'span7','maxlength'=>255)); ?>

	<?php echo $form->textAreaRow($model,'description',array('class'=>'span7', 'rows' => '5', 'maxlength'=>1000)); ?>

	<?php echo $form->textAreaRow($model,'keywords',array('class'=>'span7', 'rows' => '5', 'maxlength'=>1000)); ?>

	<?php echo $form->textFieldRow($model,'h1',array('class'=>'span7','maxlength'=>255)); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary span4'));?>

		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать и перейти' : 'Сохранить и перейти', array('class'=>'btn info span4', 'name' => 'forward'));?>

		<?php
		if ( ! $model->isNewRecord)
			echo CHtml::link('Удалить', SeoMetaTag::getLink('delete', array('id' => $model->id)), array('class'=>'btn danger', 'onclick' => 'if (!confirm("Удалить?")) return false;'));
		else
			echo CHtml::link('Отменить', array('index'), array('class' => 'btn'));
		?>
	</div>

<?php $this->endWidget(); ?>
