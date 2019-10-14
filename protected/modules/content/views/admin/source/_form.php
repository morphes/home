<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id' => 'source-form',
	'stacked' => true,
	'errorMessageType' => 'inline',
	'enableAjaxValidation' => false,
)); ?>

	<p class="note">Поля, отмеченные <span class="required">*</span> обязательны.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span6')); ?>

	<?php echo $form->textFieldRow($model,'url',array('class'=>'span6')); ?>

	<?php echo $form->textAreaRow($model,'desc',array('class'=>'span6')); ?>
	
	<div class="actions">
		<?php echo CHtml::submitButton(
			$model->isNewRecord ? 'Создать' : 'Сохранить',
			array('class' => 'btn primary')
		);?>
		
		<?php echo CHtml::button(
			'Отменить',
			array('class' => 'btn', 'onclick' => 'document.location="'.$this->createUrl('admin').'";')
		);?>
	</div>

<?php $this->endWidget(); ?>

