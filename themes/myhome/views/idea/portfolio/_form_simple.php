<script type="text/javascript">
        $(document).ready(function(){
                var cnt = 1,input="";
                $('.img_input').live('change',function(){
                        $(this).next('.img_mask').children('input').val($(this).val());
                        $('.image_to_upload .del_img,.image_to_upload .input_spacer').removeClass('hide');

                        input = $('#input_clone .img_input_conteiner').clone();
                        input.appendTo('.image_to_upload');


                        input.find('.input_row input').val("");
                        input.find('.input_row input.img_input').attr("name","Portfolio[file_"+cnt+"]");
                        input.find('.input_row textarea').attr("name","Portfolio[new]["+cnt+"][filedesc]");
                        input.find('.del_img').addClass("hide");
                        input.find('.input_spacer').addClass("hide");
                        cnt++;
                });
                $('.del_img a').live('click',function(){
			$(this).parents('.to_del').remove();
                        $('<input type="hidden" name="Portfolio[delete][]" value="' + $(this).attr('file_id') + '">').appendTo('#portfolio-form');
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
        })
</script>


<?php
$form = $this->beginWidget('CActiveForm', array(
        'id' => 'portfolio-form',
        'enableAjaxValidation' => false,
        'htmlOptions' => array(
                'enctype' => 'multipart/form-data'
        ),
        ));
?>


<?php if($model->hasErrors()) : ?>
        <div class="error_conteiner project-add">
                <?php echo $form->errorSummary($model); ?>
        </div>
        <div class="spacer-18"></div>
<?php endif; ?>

<div class="shadow_block padding-18 ">
        <div class="input_row">
                <div class="input_conteiner">
                        <?php echo $form->labelEx($model, 'name'); ?>
                        <?php echo $form->textField($model, 'name', array('class' => 'textInput', 'size' => 45, 'maxlength' => 45)); ?>
                </div>
                <div class="hint_conteiner">
                </div>
                <div class="clear"></div>
        </div>
        <div class="input_row">
                <div class="input_conteiner">
                        <?php echo $form->labelEx($model, 'desc'); ?>
                        <?php echo $form->textArea($model, 'desc', array('class' => 'textInput', 'size' => 60, 'maxlength' => 2000)); ?>
                </div>
                <div class="hint_conteiner">
                </div>
                <div class="clear"></div>
        </div>
</div>
<div class="spacer-10"></div>
<div class="shadow_block padding-18 img_inputs">

        <?php if (!empty($images)) : ?>
                <p class="proj_images_head">Изображения проекта</p>
        <?php endif; ?>
        
        <?php foreach($model->images as $img) : ?>
        
                <div class="image_uploaded">
                        <div class="uploaded to_del">
                                <div class="input_row image_inp">
                                        <div class="input_conteiner">
                                                <div class="input_conteiner_img">
                                                        <img src="/<?php echo $img->file->getPreviewName(Portfolio::$preview['crop_131']); ?>" style="width:131px;" />
                                                </div>
                                                <textarea  name="Portfolio[filedesc][<?php echo $img->file->id; ?>]"  class="textInput img_descript"><?php echo $img->file->desc; ?></textarea>
                                        </div>
                                </div>
                                <div class="hint_conteiner">

                                        <div class="del_img page_fu">
                                                <span></span><a id = '' file_id="<?php echo $img->file->id; ?>" href="#">Удалить</a>
                                        </div>
                                </div>
                        </div>
                </div>
        
        <?php endforeach; ?>
        
        <div class="clear"></div>
        
        <div class="image_to_upload">
                <div class="img_input_conteiner to_del">
                        <div class="input_row">
                                <div class="input_conteiner">
                                        <label>Изображение</label>
                                        <input  name="Portfolio[file_0]" type="file" class="img_input" size="61" />
                                        <div class="img_mask">
                                                <input type="text" class="textInput img_input_text" />
                                        </div>
                                        <div class="clear"></div>
                                </div>
                                <div class="hint_conteiner">
                                        <div class="del_img hide">
                                                <span></span><a href="#">Удалить</a>
                                        </div>
                                </div>
                                <div class="clear"></div>
                        </div>
                        <div class="input_row">
                                <div class="input_conteiner">
                                        <label>Описание изображения</label>
                                        <textarea  name="Portfolio[new][0][filedesc]" class="textInput"></textarea>
                                </div>
                                <div class="hint_conteiner">

                                </div>
                                <div class="clear"></div>
                        </div>
                        <div class="input_spacer hide"></div>
                </div>
        </div>
</div>
<div class="spacer-18"></div>
<div class="proj_form_actions">
        <div class="btn_conteiner small enabled">
                <?php echo CHtml::submitButton('Опубликовать', array('class' => 'btn_grey', 'id'=>'portfolio-submit')); ?>
        </div>
        <a href="javascript:void(0)" id="saveToDraft">Сохранить и продолжить позже</a>
        <a href="javascript:void(0)" id="deleteProject">Удалить</a>
</div>


<?php $this->endWidget(); ?>

<div class="hide" id="input_clone">
        <div class="img_input_conteiner to_del">
                <div class="input_row">
                        <div class="input_conteiner">
                                <label>Изображение</label>
                                <input  name="proj_img[0]" type="file" class="img_input" size="61" />
                                <div class="img_mask">
                                        <input type="text" class="textInput img_input_text" />
                                </div>
                                <div class="clear"></div>
                        </div>
                        <div class="hint_conteiner">
                                <div class="del_img hide">
                                        <span></span><a href="#">Удалить</a>
                                </div>
                        </div>
                        <div class="clear"></div>
                </div>
                <div class="input_row">
                        <div class="input_conteiner">
                                <label>Описание изображения</label>
                                <textarea  name="proj_img_desc[0]" class="textInput"></textarea>
                        </div>
                        <div class="hint_conteiner">

                        </div>
                        <div class="clear"></div>
                </div>
                <div class="input_spacer hide"></div>
        </div>
</div>
