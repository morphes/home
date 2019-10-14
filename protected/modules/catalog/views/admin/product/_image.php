<li class="span5" id="uploaded-file-<?php echo $file->id; ?>">
        <div class="thumbnail">

                <?php echo CHtml::openTag('a', array('href'=>$this->createUrl('showOriginalImage', array('file_id'=>$file->id)), 'target'=>'_blank')); ?>
                    <?php echo CHtml::image('/' . $file->getPreviewName(Config::$preview['crop_150'])); ?>
                <?php echo CHtml::closeTag('a'); ?>

                <div class="caption">
                        <p><?php //echo CHtml::activeTextArea($file, "[$file->id]desc", array('class'=>'span4')); ?></p>
                        <?php if(isset($value)) :?>
                                <p><?php echo CHtml::button('Удалить', array('class'=>'btn small danger deleteFile', 'file_id'=>$file->id, 'vid'=>$value->id, 'ftype'=>$type, 'style'=>'width: 60px;')); ?></p>
                        <?php else : ?>
                                <p>
					<?php echo CHtml::button('Удалить', array('class'=>'btn small danger deleteFile', 'file_id'=>$file->id, 'pid'=>$product->id, 'ftype'=>$type, 'style'=>'width: 60px;')); ?>
					<?php if ($type == 'cover' || $type == 'image') :?>
						<?php echo CHtml::link('Обрезать', '#', array('class' => 'btn info small crop_img', 'data-pid' => $product->id, 'data-type' => $type, 'data-fileid' => $file->id));?>
					<?php endif; ?>
				</p>
                        <?php endif; ?>
                </div>
        </div>
</li>