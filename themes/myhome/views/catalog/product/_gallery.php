<div class="-col-wrap image-container">
	<h1><?php echo $model->name ?></h1>
	<ul class="">


		<li class="-col-wrap">
			<div>
				<?php $size = UploadedFile::getImageSize($model->cover->getPreviewName(Product::$preview['resize_960']), 'str'); ?>
				<img <?php echo $size; ?> src="<?php echo '/' . $model->cover->getPreviewName(Product::$preview['resize_960'], 'default', true) ?>">
			</div>
		</li>
		<?php $images = $model->getImages(true);

		if ($images) :
			foreach ($images as $image) : ?>
				<?php $size = UploadedFile::getImageSize($image->getPreviewName(Product::$preview['resize_960']), 'str');
				?>
				<li class="-col-wrap">
					<div>
						<img <?php echo $size; ?> src="<?php echo '/' . $image->getPreviewName(Product::$preview['resize_960']) ?>">
					</div>
				</li>
			<?php
			endforeach;
		endif;
		?>
	</ul>
	<?php if (count($images) > 0) : ?>
		<i class="arrow -slider-prev -disabled"></i>c
		<i class="arrow -slider-next "></i>
	<?php endif; ?>
</div>
<div class="-col-3 image-info -relative"
     id="image-info">
	<div class="list-inner">
		<div class="scrollbar">
			<div class="track">
				<div class="thumb"></div>
			</div>
		</div>
		<div class="viewport">
			<div class="overview">
				<div class="photos-descriptions summary">


				</div>
				<div class="photos-preview -inset-bottom">
					<?php if (count($images) > 6) { ?>
						<div class="-col-wrap arrow -slider-prev -disabled"></div>
					<?php } ?>
					<div class="-col-wrap current">
						<img class="-quad-60"
						     src="<?php echo '/' . $model->cover->getPreviewName(Product::$preview['resize_60'], 'default', true); ?>">
					</div>
					<?php if ($images) {
						foreach ($images as $image) {
							?>
							<div class="-col-wrap">
								<img class="-quad-60"
								     src="<?php echo '/' . $image->getPreviewName(Product::$preview['resize_60']); ?>">
							</div>
						<?php
						}
						?>
						<?php if (count($images) > 6) { ?>
							<div class="-col-wrap arrow -slider-next"></div>
						<?php } ?>
					<?php } ?>


				</div>
				<div class="social">
					<?php $this->widget('ext.sharebox.EShareBox', array(
						'view'             => 'productGrid',
						// url to share, required.
						'url'              => Yii::app()->request->hostInfo . Yii::app()->request->requestUri,
						'imgUrl'           => Yii::app()->request->hostInfo . '/' . $model->cover->getPreviewName(Product::$preview['resize_960']),

						// A title to describe your link, required.
						'title'            => !empty($model->name)
							? $model->name
							: 'товар',

						// A small message for post
						'message'          => Amputate::getLimb($model->desc, 500, '...'),
						'classDefinitions' => array(
							'facebook'  => '-icon-facebook -icon-softgray',
							'vkontakte' => '-icon-vkontakte -icon-softgray',
							'twitter'   => '-icon-twitter -icon-softgray',
							'google+'   => '-icon-google-plus -icon-softgray',
							'odkl'      => '-icon-odnoklassniki -icon-softgray',
							'pinterest' => '-icon-pinme -icon-softgray',
						),
						'exclude'          => array('livejournal'),
						'htmlOptions'      => array(),
					));?>

					<?php // Подключаем виджет для добавления в избранное
					$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
						'modelId'   => $model->id,
						'modelName' => get_class($model),
						'viewHeart' => 'favoriteProductCard',
					));?>

				</div>
			</div>
		</div>
	</div>
</div>