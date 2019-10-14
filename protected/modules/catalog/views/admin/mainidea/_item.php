<div class="clearfix">
	<label>Название товара</label>
	<div class="input">
		<?php echo CHtml::textField('MainUnit[name]', $product->name, array('class'=>'span5', 'maxlength'=>255)); ?>
	</div>
</div>

<div class="clearfix photos">
	<label>Автор</label>
	<div class="input"><span class="uneditable-input"><?php echo $interior->author->name; ?></span></div>
</div>


<div class="clearfix photos">
	<label>Помещение</label>
	<div class="input">
		<?php echo CHtml::dropDownList('', '', $contentNames, array('class'=>'span5', 'id'=>'room_select')); ?>
	</div>
</div>

<div class="clearfix photos">
	<label>Фоточке</label>
	<div class="input">
		<?php
		$cnt=0;
		/** @var $content InteriorContent */
		foreach ($contents as $content) {
			$htmlOptions = array('class'=>'media-grid', 'id'=>'room_'.$content->id);
			if ($cnt === 0) {
				$cnt++;
			} else {
				$htmlOptions['style'] = 'display:none;';
			}
			echo CHtml::openTag('ul', $htmlOptions);
			$images = $content->getAllPhotos();

			/** @var $image UploadedFile */
			foreach ($images as $image) {

				$src = $image->getPreviewName(InteriorContent::$preview['height_420']);
				$size = $image->getImageSize($src);
				if ($size['width'] < 620) {
					continue;
				}

				echo CHtml::openTag('li', array());
					echo CHtml::link(
						CHtml::image('/'.$src,
							'',
							array('class'=>'thumbnail',
							      'data-id'=>$image->id,
							      'height'=>150,
							)
						).ProductOnPhotos::getQntProducts($image->id).'<br />'.CHtml::tag('span', array(), $size['width'] . 'x' . $size['height'])
					);
				echo CHtml::closeTag('li');
			}

			echo CHtml::closeTag('ul');
		}
		?>
	</div>
</div>