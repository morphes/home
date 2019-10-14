<?php echo CHtml::fileField('optionImage', '', array("size"=>1, "multiple"=>true, "class"=>"optionImage", 'pid'=>$value->product_id, 'oid'=>$value->option_id, 'style'=>'margin-bottom:10px;')); ?>

<p>
        <a style="cursor: pointer" onclick="showHideSelector($(this), 'option-preview-<?php echo $value->product_id; ?>-<?php echo $value->option_id; ?>')">скрыть</a>
</p>

<ul class="thumbnails" id="option-preview-<?php echo $value->product_id; ?>-<?php echo $value->option_id; ?>">
        <?php
                foreach($value->value as $val) {
                        $file = UploadedFile::model()->findByPk($val);
                        if(!$file)
                                continue;
                        ?>

                        <li class="span5" id="uploaded-file-<?php echo $file->id; ?>">
                                <div class="thumbnail">
                                        <?php echo CHtml::image('/' . $file->getPreviewName(Config::$preview['crop_150'])); ?>
                                        <div class="caption">
                                                <p><?php //echo CHtml::activeTextArea($file, "[$file->id]desc", array('class'=>'span4')); ?></p>
                                                <p><?php echo CHtml::button('Удалить', array('class'=>'btn small danger deleteFile', 'file_id'=>$file->id, 'ftype'=>'value', 'vid'=>$value->id, 'style'=>'width: 60px;')); ?></p>
                                        </div>
                                </div>
                        </li>

                        <?php
                }
        ?>
</ul>

