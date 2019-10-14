<?php
$this->rightbar = null;

$form = $this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
    'id' => 'news-form',
    'enableAjaxValidation' => false,
        ));
?>

<?php echo $form->errorSummary($model); ?>



<div class="clearfix">
        <label for="News_public_time"><?php echo $model->getAttributeLabel('public_time'); ?></label>
        <div class="input">
                <?php
                $this->widget('application.extensions.timepicker.EJuiDateTimePicker', array(
                    'model' => $model,
                    'attribute' => 'public_time',
                    'options' => array(
                        'autoLanguage' => false,
                        'dateFormat' => 'dd.mm.yy',
                        'timeFormat' => 'hh:mm',
                        'changeMonth' => true,
                        'changeYear' => false,
                        'timeOnlyTitle' => 'Выберите время',
                        'timeText' => 'Время',
                        'hourText' => 'Часы',
                        'minuteText' => 'Минуты',
                        'secondText' => 'Секунды',
                        'closeText' => 'Закрыть'
                    ),
                ));
                ?>
        </div>        
</div>

<?php echo $form->textFieldRow($model,'title',array('class'=>'span8','maxlength'=>500)); ?>


<div class="clearfix">
        <label for="News_public_time"><?php echo $model->getAttributeLabel('content'); ?></label>
        <div class="input">
                <?php
                $this->widget('ext.editMe.ExtEditMe', array(
                    'model' => $model,
                    'attribute' => 'content',
                    'htmlOptions' => array('class' => 'span8'),
                    'contentsCss' => array('/css/ckeditor.css'),
                    'filebrowserImageUploadUrl' => '/content/admin/news/upload',
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
        </div>
</div>

        <?php echo $form->dropDownListRow($model, 'status', News::$status, array('class' => 'span5')); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn large primary')); ?>
		<?php
		if ($model->isNewRecord)
			$cancelUrl = array('admin');
		else
			$cancelUrl = array('view', 'id' => $model->id);
		
		echo CHtml::link('Отменить', $cancelUrl, array('class' => 'btn large'));
		?>
	</div>


<?php $this->endWidget(); ?>
