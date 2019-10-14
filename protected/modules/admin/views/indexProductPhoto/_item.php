<div class="clearfix photos">
	<label>Фото</label>

	<div class="input">
		<ul class="media-grid">
			<?php /** @var $image UploadedFile */
			foreach ($images as $image) {

				$originalSize = $image->getOriginalImageSize('array');

				echo CHtml::tag('li', array(),
					CHtml::link(
						CHtml::image('/' . $image->getPreviewName(Product::$preview['crop_60']),
							'',
							array(
								'class'                => 'thumbnail',
								'data-url'             => '/' . $image->getPreviewName(IndexProductPhoto::$preview['resize_540']),
								'data-id'              => $image->id,
								'width'                => 60,
								'height'               => 60,
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
			} ?>
		</ul>
	</div>
</div>