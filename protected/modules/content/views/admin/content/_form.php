<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id'=>'content-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->checkBoxRow($model,'sharebox', array('style' => 'vertical-align: bottom; margin-bottom: 0;')); ?>
	
	<?php echo $form->dropDownListRow($model,'category_id', $cats); ?>
	
	<?php echo $form->textFieldRow($model,'alias', array('maxlength'=>100, 'class' => 'span6')); ?>
	
	<?php echo $form->textFieldRow($model,'menu_key',array('maxlength'=>100, 'class' => 'span6')); ?>
	
	<?php echo $form->textFieldRow($model,'title',array('maxlength'=>300, 'class' => 'span11')); ?>
        
        <hr />
        
	<?php echo $form->textFieldRow($model,'meta_title',array('maxlength'=>255, 'class'=>'span11')); ?>
        
	<?php echo $form->textFieldRow($model,'meta_desc',array('maxlength'=>255, 'class'=>'span11')); ?>
        
	<?php echo $form->textFieldRow($model,'meta_keyword',array('maxlength'=>255, 'class'=>'span11')); ?>
        
        <hr />

        <div class="clearfix">
                <?php echo $form->labelEx($model,'desc'); ?>
		<div class="input">
                <?php
                $this->widget('application.extensions.tinymce.ETinyMce', array(
                        'model'=>$model,
                        'attribute'=>'desc',
                        'options'=>array(
                                'theme'=>'advanced',
                                'forced_root_block' => false,
                                'force_br_newlines' => true,
                                'force_p_newlines' => false,
                                'width'=>'640px',
                                'height'=>'200px',
                                'theme_advanced_toolbar_location'=>'top',
                                'language'=>'ru',
                        ),
                ));
                ?>
                <?php echo $form->error($model,'desc'); ?>
		</div>
        </div> 

        <div class="clearfix">
                <?php echo $form->labelEx($model,'content'); ?>
		<div class="input">
                <?php
                $this->widget('ext.editMe.ExtEditMe', array(
                    'model' => $model,
                    'attribute' => 'content',
                    'htmlOptions' => array('class' => 'span8'),
                    'contentsCss' => array('/css/ckeditor.css'),
                    'filebrowserImageUploadUrl' => '/content/admin/content/upload',
                    'enabledPlugins' => array('cuttable', 'typograf'),
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
                <?php echo $form->error($model,'content'); ?>
		</div>
        </div> 
        
        
	<?php echo $form->dropDownListRow($model,'status', Content::$statuses); ?>
	
	
	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', array('class' => 'btn large primary')); ?>
		<?php echo CHtml::link('Отменить', array('admin'), array('class' => 'btn large'));?>
	</div>

<?php $this->endWidget(); ?>