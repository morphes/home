<div class="clearfix photos">
	<label>Фото</label>

	<div class="input">
		<ul class="media-grid">
			<?php /** @var $image UploadedFile */
			$image = UploadedFile::model()->findByPk($user->image_id);

			$originalSize = $image->getOriginalImageSize('array');

			echo CHtml::tag('li', array(),
				CHtml::link(
					CHtml::image('/' . $image->getPreviewName(User::$preview['crop_80']),
						'',
						array(
							'class'                => 'thumbnail',
							'data-url'             => '/' . $image->getPreviewName(User::$preview['resize_540']),
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