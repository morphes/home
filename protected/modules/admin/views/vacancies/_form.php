<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'vacancies-form',
	'enableAjaxValidation'=>false,
	'htmlOptions' => array('class' => 'form-stacked')
)); ?>

	<p class="help-block">Поля с символом <span class="required">*</span> обязательны.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<div class="span2">
			<div class="clearfix <?php if ($form->error($model, 'position')) echo 'error';?>">
				<?php echo $form->labelEx($model, 'position');?>
				<div class="input">
					<?php
					$posOptions = array('class'=>'span1', 'maxlength' => 3);
					if ($model->isNewRecord)
						$posOptions['value'] = $countVacancies + 1;
					echo $form->textField($model,'position',$posOptions);
					?>
					из <?php echo $countVacancies?>
				</div>
				<?php echo $form->error($model, 'position');?>
			</div>
		</div>
		<div class="span5">
			<?php echo $form->textFieldRow($model,'key',array('class'=>'span5','maxlength'=>255)); ?>
		</div>
	</div>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span7','maxlength'=>255)); ?>

	<?php echo $form->textAreaRow($model,'anons',array('class'=>'span7', 'rows' => '4', 'maxlength'=>500)); ?>

	<div class="clearfix <?php if ($form->error($model, 'text')) echo 'error' ;?>">
                <?php echo $form->labelEx($model,'text'); ?>
		<div class="input">
			<?php
			$this->widget('application.extensions.tinymce.ETinyMce', array(
				'model'=>$model,
				'attribute'=>'text',
				'options'=>array(
					'theme'=>'advanced',
					'forced_root_block' => false,
					'force_br_newlines' => true,
					'force_p_newlines' => false,
					'width'=>'640px',
					'height'=>'320px',
					'theme_advanced_toolbar_location'=>'top',
					'language'=>'ru',
				),
			));
			?>
			<?php echo $form->error($model,'text'); ?>
		</div>
		
        </div>
	

	<?php echo $form->textFieldRow($model,'wage',array('class'=>'span7','maxlength'=>100)); ?>

	<?php echo $form->dropDownListRow($model, 'status', Vacancies::$nameStatus,  array('class'=>'span7')); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn large primary')); ?>
		<?php
		if ($model->isNewRecord)
			$cancelUrl = array('index');
		else
			$cancelUrl = array('view', 'id' => $model->id);
		
		echo CHtml::link('Отменить', $cancelUrl, array('class' => 'btn large'));
		?>
		
		<?php
		if ( ! $model->isNewRecord) {
			echo CHtml::link(
				'Удалить',
				array('delete', 'id' => $model->id),
				array(
					'class' => 'btn danger large',
					'style' => 'float: right;',
					'onclick' => '
						if (confirm("Удалить вакансию?"))
							return true;
						else
							return false;
					'
			));
		}
		?>
	</div>

<?php $this->endWidget(); ?>
