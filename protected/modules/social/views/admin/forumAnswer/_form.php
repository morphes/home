<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'                  => 'forum-answer-form',
	'enableAjaxValidation'=> false,
	'stacked'             => true,
	'htmlOptions'         => array('enctype' => 'multipart/form-data')
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="clearfix">
		<?php echo $form->label($model, 'topic_id');?>
		<div class="input">
			<?php echo ($m = ForumTopic::model()->findByPk((int)$model->topic_id)) ? $m->name : 'неизвестно' ; ?>
		</div>
	</div>

	<div class="clearfix">
		<label>Автор <span class="required">*</span></label>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
			'name'                => 'author',
			'sourceUrl'           => $this->createUrl('/admin/user/autocomplete'),
			'value'               => isset($model->author->login) ? $model->author->login . " ({$model->author->name})" : '',
			'options'             => array(
				'showAnim'             => 'fold',
				'delay'                => 0,
				'autoFocus'            => true,
				'select'               => 'js:function(event, ui) {$("#ForumAnswer_author_id").val(ui.item.id); }',
			),
			'htmlOptions'         => array('class'=> 'span7')
		));?>

			<?php echo $form->hiddenField($model, 'author_id', array('size'=> 15)); ?>

			<?php
			Yii::app()->clientScript->registerScript('loginType', '
			$("#author").keydown(function(event){
				if (
					event.keyCode != 27 && event.keyCode != 9 && event.keyCode != 13
					&& event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40
					&& event.keyCode != 35 && event.keyCode != 36
				) {
					$("#ForumAnswer_author_id").val("");
				}
			});
			', CClientScript::POS_READY
			);
			?>
		</div>
	</div>

	<?php echo $form->textAreaRow($model,'answer',array('class'=>'span10', 'rows' => 12, 'maxlength'=>3000)); ?>

	<div class="clearfix">
		<label>Прикрепленные файлы</label>
		<div class="input">
			<?php
			$this->widget('CMultiFileUpload', array(
				'model'    => $model,
				'attribute'=> 'files',
				'accept'   => 'zip|7z|rar|jpg|jpeg|png|txt|doc|docx|xls|xlsx|rtf|pdf',
				'duplicate'=>'Уже выбран',
				'max'	   => 5,
				'denied'   =>'Данный тип файла запрещен к загрузке',
				'options'  => array(),
			));
			?>
			<?php echo $form->error($model,'files'); ?>
		</div>

		<div class="input">
			<?php
			foreach($model->files as $file) {
				$delLink = CHtml::link('x', '#', array('data-id' => $file['file_id'], 'class' => 'del_file_topic'));
				echo CHtml::tag('div', array(), $delLink.' '.$file['name'], true);
			}
			?>
		</div>
	</div>


	<?php echo $form->dropDownListRow($model, 'status', ForumAnswer::$statusNames); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
