<div class="clearfix photos">
	<label>Фото</label>

	<div class="input">
		<ul class="media-grid">
			<?php /** @var $image UploadedFile */
			$image = UploadedFile::model()->findByPk($interior->image_id);

			$originalSize = $image->getOriginalImageSize('array');

			echo CHtml::tag('li', array(),
				CHtml::link(
					CHtml::image('/' . $image->getPreviewName(Interior::$preview['crop_80']),
						'',
						array(
							'class'                => 'thumbnail',
							'data-url'             => '/' . $image->getPreviewName(IndexIdeaPhoto::$preview['resize_540']),
							'data-id'              => $image->id,
							'width'                => 80,
							'height'               => 80,
							'data-original_width'  => isset($originalSize['width'])
										  ? $originalSize['width']
										  : 0,
							'data-original_height' => isset($originalSize['height'])
										  ? $originalSize['height']
										  : 0
						)
					)
				)
			);
			?>
		</ul>
	</div>
</div>