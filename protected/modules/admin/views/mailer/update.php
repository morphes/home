<?php
$this->breadcrumbs=array(
	'Управление почтой' => array('/admin/mailtemplate/index'),
	'Почтовые рассылки' => array('index'),
	'Редактирование'
);
?>


<h1>Редактирование рассылки #<?php echo $model->id; ?></h1>
	
<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id'=>'mailer-delivery-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model); ?>

	
	<?php echo $form->dropDownListRow($model, 'status', Mailer::$statusNames); ?>
	
	<?php echo $form->dropDownListRow($model, 'group_id', $model->groups); ?>
	
	<?php echo $form->dropDownListRow($model, 'role', Config::$userRoles); ?>

	<?php echo $form->dropDownListRow($model, 'user_status', $model->userStatus); ?>
	
	<?php echo $form->textFieldRow($model,'author'); ?>
	
	<?php echo $form->textFieldRow($model,'from'); ?>
	
	<?php echo $form->textFieldRow($model,'subject'); ?>
	

	<div class="clearfix">
		<?php echo CHtml::activeLabelEx($model, 'data'); ?>
		<div class="input">
			<?php
			$this->widget('ext.editMe.ExtEditMe', array(
				'model' => $model,
				'attribute' => 'data',
				'htmlOptions' => array('class' => 'span8'),
				'contentsCss' => array('/css/ckeditor.css'),
				'filebrowserImageUploadUrl' => '/admin/mailer/upload',
				'enabledPlugins' => array('cuttable'),
				'height' => '600px',
				'width' => '800px',
				'toolbar' =>
				array(
					array(
						'Bold', 'Italic', 'Underline', 'BulletedList', '-', 'Cuttable', '-', 'Typograf', '-',
					),
					array(
						'Link', 'Unlink', 'RemoveFormat',
					),
					array(
						'Image',
					),
					array(
						'Source',
					),
				),
			));
			?>
		</div>
	</div>
	
	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', array('class' => 'btn large primary')); ?>
	</div>

<?php $this->endWidget(); ?>