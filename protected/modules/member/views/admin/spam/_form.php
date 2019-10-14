<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'spam-form',
	'enableAjaxValidation'=>false,
)); ?>
	<div class="clearfix">
		<label>Жалоба на </label>
		<div class="input">
			<?php echo CHtml::textArea('msg', User::model()->findByPk($model->msgBody->author_id)->login, array('disabled'=>true, 'class'=>'span7', 'style'=>'height:20px;')); ?>
		</div>
	</div>


	<div class="clearfix">
		<label>Сообщение</label>
		<div class="input">
			<?php echo CHtml::textArea('msg', $model->msgBody->message, array('disabled'=>true, 'class'=>'span7', 'style'=>'height:100px;')); ?>
		</div>
	</div>

	<?php echo $form->dropDownListRow($model,'status', Spam::$statusLabels,array('class'=>'span5')); ?>

	<div class="actions">
		<?php echo CHtml::submitButton('Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
