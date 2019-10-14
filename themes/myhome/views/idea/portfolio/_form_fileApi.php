<script type="text/javascript">
    $(document).ready(function(){
        /*
        * Отправка формы
        */
        $('#portfolio-submit').click(function(){
            $('#portfolio-form').submit();
        });

        /*
        * Удаление файла
        */
        $('.del_img a').live('click',function(){
            $(this).parents('.uploaded').remove();
            $('<input type="hidden" name="Portfolio[delete][]" value="' + $(this).attr('file_id') + '">').appendTo('#portfolio-form');
            return false;
        });

        $('.service_choice').live('click',function(){
            $('.servise_choice_list').removeClass('hide');
            return false;
        });

        $('#deleteProject').click(function(){
            if (confirm("Вы действительно хотите удалить текущий проект?"))
            {
                window.location="<?php echo $this->createUrl('/idea/portfolio/delete', array('id'=>$model->id));?>";
            }
        });

        $('#saveToDraft').click(function(){
            $('#portfolio-form').append('<input type="hidden" value="true" name="toDraft">');
            $('#portfolio-submit').click();
        });


    });
</script>


<?php $form=$this->beginWidget('CActiveForm', array(
        'id'=>'portfolio-form',
        'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->hiddenField($model,'service_id'); ?>

<?php if($model->hasErrors()) : ?>
<div class="error_conteiner project-add">
        <?php echo $form->errorSummary($model); ?>
</div>
<div class="spacer-18"></div>
<?php endif; ?>

<div class="shadow_block padding-18 ">
    <div class="progressbar hide">
    </div>
    <div class="input_row">
        <div class="input_conteiner">
                <?php echo $form->labelEx($model,'name'); ?>
                <?php echo $form->textField($model,'name',array('class'=>'textInput','size'=>45,'maxlength'=>45)); ?>
        </div>
        <div class="hint_conteiner">
        </div>
        <div class="clear"></div>
    </div>
    <div class="input_row">
        <div class="input_conteiner">
                <?php echo $form->labelEx($model,'desc'); ?>
                <?php echo $form->textArea($model,'desc',array('class'=>'textInput','size'=>60,'maxlength'=>2000)); ?>
        </div>
        <div class="hint_conteiner">
        </div>
        <div class="clear"></div>
    </div>
    <p class="proj_images_head">Изображения проекта</p>
    <div class="image_uploaded">

            <?php foreach($model->images as $img) : ?>
        <div class="uploaded">
            <div class="input_row image_inp">
                <div class="input_conteiner">
                    <div class="input_conteiner_img">
                        <img style="width:131px;" src="/<?php echo $img->file->getPreviewName(Portfolio::$preview['crop_131']); ?>">
                    </div>
                    <textarea class="textInput img_descript" name="Portfolio[filedesc][<?php echo $img->file->id; ?>]"><?php echo $img->file->desc; ?></textarea>
                </div>
            </div>

            <div class="hint_conteiner">
                <div class="del_img page_fu">
                    <span></span>
                    <a id="" file_id="<?php echo $img->file->id; ?>" href="#">Удалить</a>
                </div>
            </div>
        </div>
            <?php endforeach; ?>
    </div>
    <div class="image_to_upload">
        <div class="input_row image_inp">

            <div class="input_conteiner">

                <label class=""></label>
                    <?php $this->widget('ext.FileUpload.FileUpload', array(
                    'url'=> $this->createUrl('upload', array('pid'=>$model->id)),
                    'postParams'=>array(),
                    'config'=> array(
                            'fileName' => 'Portfolio[file]',
                            'onSuccess'=>'js:function(response){ $("#portfolio-form .image_uploaded").append(response.html); }',
                            'onStart' => 'js:function(data){ $("#load_img").show(); }',
                            'onFinished' => 'js:function(data){ $("#load_img").hide(); }'
                    ),
                    'htmlOptions'=>array('size'=>61, 'accept'=>'image', 'class'=>'img_input'),
            )); ?>
                <div class="img_mask">
                    <input type="text" class="textInput img_input_text" />
                </div>

                <img src="/img/loaderT.gif" alt="" id="load_img" style="margin-top: 8px; margin-left: 6px; width: 16px; display: none;">

                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>
<div class="spacer-18"></div>
<div class="proj_form_actions">
    <div class="btn_conteiner small">
            <?php echo CHtml::button('Опубликовать', array('class'=>'btn_grey', 'id'=>'portfolio-submit')); ?>
    </div>
    <a href="javascript:void(0)" id="saveToDraft">Сохранить и продолжить позже</a>
    <a href="javascript:void(0)" id="deleteProject">Удалить</a>
</div>
<?php $this->endWidget(); ?>

