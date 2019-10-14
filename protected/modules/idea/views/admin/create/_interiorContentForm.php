<?php
/**
 * @var $content InteriorContent
 */
?>
<div class="fpa-space form-stacked" id="interior_content_id_<?php echo $content->id ?>">
        
        <?php echo CHtml::activeHiddenField($content, "[{$content->id}]id");?>
          
        <div class="clearfix <?php $error = $content->getError('room_id'); echo !empty($error) ? ' error' : '';?>">      
                <?php echo CHtml::activeLabel($content, "[{$content->id}]room_id");?>
                <div class="input">
                        <?php echo CHtml::activeDropDownList($content, "[{$content->id}]room_id", $rooms, array('class'=>'rooms span8', 'data-id'=>$content->id));?>
                        <?php echo CHtml::error($content, "[{$content->id}]room_id" , array('class'=>'help-inline'));?>
                </div>  
        </div>

        <div class="clearfix <?php if ($content->getError('tag')) echo 'error'; ?>">
                <?php echo CHtml::label('Теги', ''); ?>
                <div class="input">
                        <?php echo CHtml::activeTextArea($content, "[{$content->id}]tag", array('class'=>'span14 tag-area', 'style'=>'height: 60px; font-size: 14px;')); ?>
			<?php echo CHtml::error($content, "[{$content->id}]tag", array('class' => 'help-inline'));?>
                </div>  
        </div>

        <div class="clearfix <?php $error = $content->getError('style_id'); echo !empty($error) ? ' error' : '';?>">      
                <?php echo CHtml::activeLabel($content, "[{$content->id}]style_id"); ?>
                <div class="input">
                        <?php echo CHtml::activeDropDownList($content, "[{$content->id}]style_id", $styles, array('class'=>'styles span8')); ?>
                        <?php echo CHtml::error($content, "[{$content->id}]style_id", array('class'=>'help-inline'));?>
                </div>  
        </div>

        <div class="clearfix <?php $error = $content->getError('color_id'); echo !empty($error) ? ' error' : '';?>">      
                <?php echo CHtml::activeLabel($content, "[{$content->id}]color_id"); ?>
                <div class="input">
                        <?php echo CHtml::activeDropDownList($content, "[{$content->id}]color_id", $colors, array('class'=>'colors span8')); ?>
                        <?php echo CHtml::error($content, "[{$content->id}]color_id", array('class'=>'help-inline'));?>
                </div>  
        </div>

        <div class="clearfix">
                
                
                <div id="additional-color-<?php echo $content->id ?>" class="" style="margin-bottom: 10px;">
                        <?php 
                                if(!empty($additional_colors)) {
                                        foreach($additional_colors as $color){
                                                if ($color->getIsNewRecord())
                                                        continue;

                                                $color->addErrors(!empty($errors['additional_colors'][$content->id][$color->position]) ? $errors['additional_colors'][$content->id][$color->position] : array());
                                                $error = $color->getErrors(); $errorClass = !empty($error) ? ' error' : '';
                                                echo '<div class="clearfix'.$errorClass.'" id="additional-color-'.$content->id.'-'.$color->position.'">';
                                                echo CHtml::activeLabel($color, "[{$content->id}][{$color->position}]color_id");
                                                echo CHtml::openTag('div', array('class'=>'input'));
                                                echo CHtml::activeDropDownList($color, "[{$content->id}][{$color->position}]color_id", $colors, array('class'=>'span8'));
                                                echo CHtml::error($color, "[{$content->id}][{$color->position}]color_id", array('class'=>'help-inline'));
                                                echo '<span style="cursor:pointer" onclick="remove_scc('.$content->id.','.$color->position.')"> Удалить </span>';
                                                echo CHtml::closeTag('div').CHtml::closeTag('div');
                                        }
                                }
                        ?>
                </div>  
                <?php echo Chtml::button('Добавить дополнительный цвет', array('class'=>'btn primary','id'=>'additional-color-button', 'onclick'=>"append_color({$content->id})"));?>
        </div>
        
        <?php 
                if(isset($additional_colors)){
                        ?>
                                <script type="text/javascript">  
                                        color_counter[<?php echo $content->id; ?>] = <?php echo ++end($additional_colors)->position; ?>;
                                </script>
                        <?php
                }
        ?>
        
        
        
	
	
	<div class="clearfix well">
                <?php echo CHtml::tag('h3', array('style' => 'margin-bottom: 15px;'), 'Фотографии интерьера', true); ?>
		<?php echo CHtml::error($content, 'image_id'); ?>
		<div id=intcontent_<?php echo $content->id; ?> class="fpa-space-images">
			<?php /** @var $uFile UploadedFile */
			foreach($uploadedFiles as $uFile) : ?>
				<div class="row">
					<span class="span3" style="margin-bottom: 10px;">
						<a href="<?php echo '/'.$uFile->getPreviewName(InteriorContent::$preview['resize_710x475']); ?>" class="preview" title="<?php echo $uFile->getOriginalImageSize();?>">
							<?php echo CHtml::image('/'.$uFile->getPreviewName(InteriorContent::$preview['crop_150']), '', array('width'=>150, 'height'=>150) ); ?>
						</a>
					</span>
					<div class="span5">
						<?php echo CHtml::link('скачать оригинал', $this->createUrl('/download/productImgOriginal', array('file_id'=>$uFile->id)), array('class' => 'btn success')); ?>
						<div style="margin-top: 10px;"></div>

						<select class="span3">
							<option value="0">— перенести фото в —</option>
							<?php foreach ($tabs as $tab) { ?>
								<?php if ($tab['id'] == $content->id) {
									continue;
								} ?>
								<option value="<?php echo $tab['id'];?>"><?php echo $tab['title'];?></option>
							<?php } ?>
						</select>

						<button class="btn info move_image"
							data-image-id="<?php echo $uFile->id;?>"
							onclick="
								var contentId = $(this).prev('select').val();
								var imageId = $(this).data('image-id');
								if (contentId > 0) {
									$.post(
										'/idea/admin/create/ajaxMoveImage',
										{
											contentId: contentId,
											imageId: imageId
										},
										function(data){
											if (data.success) {
												window.document.location.reload();
											} else {
												alert(data.message);
											}
										}, 'json'
									);
								} else {
									alert('Выберите помещение');
								}

								return false;
							">применить</button>
					</div>
					<div class="span12">
						<label>Описание<br><textarea maxlength="1000" data-id="<?php echo $uFile->id; ?>" rows="6" class="span12 img_desc"><?php echo $uFile->desc; ?></textarea></label>
					</div>

					<div class="span3">
						<a class="img_del btn danger small"
						   data-parent="<?php echo $content->id; ?>"
						   data-type="interiorContent"
						   data-id="<?php echo $uFile->id; ?>"
						   href="#">Удалить изображение</a>
					</div>
					<div style="clear: both"></div>
				</div>
				<div class="clearfix"></div>
				<hr>
			<?php endforeach; ?>
		</div>


		<?php
		/* -------------------------------------------------------------
		 *  Инпут загрузки изображения
		 * -------------------------------------------------------------
		 */
		$this->widget('ext.FileUpload.FileUpload', array(
			'id'          => 'imgUpload_' . $content->id,
			'url'         => '/' . $this->module->id . '/' . $this->id . '/upload',
			'postParams'  => array('id' => $content->id, 'type' => 'interiorContent'),
			'config'      => array(
				'maxConnections' => 1,
				'fileName'       => 'InteriorContent[file]',
				'onSuccess'      => 'js:function(response){ if (response.success) { $("#intcontent_' . $content->id . '").append(response.html); } }',
				'onStart'        => 'js:function(data){ }',
				'onFinished'     => 'js:function(data){ }'
			),
			'htmlOptions' => array('size' => 61, 'accept' => 'image', 'class' => 'img_input'),
		)); ?>
	</div>
          
        <div class="actions">
                <?php echo CHtml::button('Удалить помещение',array('class'=>'btn danger', 'onclick'=>"remove_sc({$content->id});"));?>
        </div>        
</div>
