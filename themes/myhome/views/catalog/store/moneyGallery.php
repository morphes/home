<?php $this->pageTitle = 'Фотогалерея — ' . $store->name; ?>

<?php Yii::app()->clientScript->registerScriptFile('/js-new/jquery.popup.carousel.js'); ?>


<div class="-grid-wrapper page-content">
	<div class="-grid">
		<div class="-col-8">

			<input type="hidden" name="storeId" id="storeId" value="<?php echo $store->id;?>">

			<h2>Фотогалерея</h2>
			<div class="-grid photo-list">
				<?php
				/** @var $photos StoreGallery[] */
				$photos = $photosProvider->getData();
				foreach ($photos as $photo) { ?>
					<div class="-col-2">
						<span class="thumb">
							<?php echo CHtml::image(
								'/' . $photo->preview->getPreviewName(StoreGallery::$preview['crop_140']),
								$photo->name,
								array('class' => '-quad-140')
							);?>
						</span>
						<span class="controls" data-photoId="<?php echo $photo->id;?>">
							<i class="-icon-pencil-xs"></i>
							<i class="-icon-cross-circle-xs"></i>
						</span>
					</div>
				<?php } ?>
			</div>

			<div class="-grid page-controls-bottom">
				<!-- pager -->
				<div class="-col-wrap -push-right">
					<?php $this->widget('application.components.widgets.CustomListPager', array(
						'pages'          => $photosProvider->pagination,
						'htmlOptions'    => array('class' => '-menu-inline -inline -pager'),
						'maxButtonCount' => 5,
					)); ?>
				</div>
				<!-- eof pager -->
			</div>

			<div class="-col-8 photo-add-form -hidden">
				<?php
				/* -----------------------------------------------------
				 *  Попап для добавления новой фотографии
				 * -----------------------------------------------------
				 */
				$this->renderPartial('//catalog/store/_moneyGalleryPopup', array(
					'data' => new StoreGallery()
				));
				?>
			</div>

			<script>

				$(function(){
					$('body').on('change', '.photo_for_gallery', function() {
						// This starts
						if (this.files != undefined) {
							createImage(this.files[0]);
						}
						else {
							showMessage('Ваш браузер не поддреживает HTML5. Предпросмотр фотографии невозможен.');
						}
					});


					function createImage(file) {

						var 	image = new Image()
							, preview = $('.photo-preview');

						var reader;
						try
						{
							reader = new FileReader();
							var max_file_size = 1048576 * 2;
							if (file.size > max_file_size) {
								showMessage('Размер файла слишком большоий, максимум 2MB');
								return;
							}
						} catch (err) {
							showMessage('Ваш браузер не поддерживает HTML5. Предпросмотр невозможен.');
							return;
						}


						reader.onload = function(e){

							image.src = e.target.result;
							setTimeout(function(){
								var perc, offset;

								if (image.naturalWidth > image.naturalHeight) {
									image.setAttribute('height', 140);

									// Вычисляем смещение фотографии, чтобы она встала по центру
									if (image.naturalHeight > 0) {
										perc = image.naturalHeight / image.naturalWidth * 100;
										offset = parseInt((140 * perc / 100) / 4);
										image.style.marginLeft = '-' + offset + 'px';
									}

								} else {
									image.setAttribute('width', 140);

									// Вычисляем смещение фотографии, чтобы она встала по центру
									if (image.naturalWidth > 0) {
										perc = image.naturalWidth / image.naturalHeight * 100;
										offset = parseInt((140 * perc / 100) / 4);
										image.style.marginTop = '-' + offset + 'px';
									}
								}

								preview.removeClass('-loading').append(image);
							}, 300);
						};

						preview.html('');
						preview.addClass('-loading');

						// Reading the file as a DataURL. When finished,
						// this will trigger the onload function above:
						reader.readAsDataURL(file);

					}

					function showMessage(msg) {
						alert(msg);
					}
				});

			</script>


			<?php // --- "Телевизор" — попап для вывода слайдера --- ?>

			<div class="photogallery-view -hidden">
				<div class="-col-wrap image-container">
					<ul class="">
						<?php foreach ($photos as $photo) { ?>
							<li class="-col-wrap">
								<div>
									<?php echo CHtml::image(
										'/' . $photo->preview->getPreviewName(StoreGallery::$preview['resize_1920x1080']),
										$photo->name
									);?>
								</div>
							</li>
						<?php } ?>
					</ul>
					<i class="arrow -slider-prev -disabled"></i>
					<i class="arrow -slider-next "></i>
				</div>
				<div class="-col-3 image-info -relative">
					<div class="photos-descriptions">
						<?php foreach ($photos as $index => $photo) { ?>
							<div id="ph-<?php echo $index;?>"
							     class="<?php if ($index > 0) echo '-hidden';?>">
								<p class="-large -semibold"><?php echo $photo->name;?></p>
								<p><?php echo $photo->description;?></p>
							</div>

						<?php } ?>
					</div>
					<div class="photos-preview -inset-bottom -border-dotted-bottom">

						<?php if (count($photos) > 9) { ?>

							<div class="-col-wrap arrow -slider-prev -disabled"></div>

							<?php foreach ($photos as $index => $photo) { ?>
								<div id="ph-<?php echo $index;?>"
								     class="-col-wrap <?php if ($index == 0) echo 'current';?> <?php if ($index >= 7) echo '-hidden';?>">
									<?php echo CHtml::image(
										'/' . $photo->preview->getPreviewName(StoreGallery::$preview['crop_60']),
										$photo->name,
										array('class' => '-quad-60')
									);?>
								</div>

							<?php } ?>

							<div class="-col-wrap arrow -slider-next"></div>

						<?php } else { ?>

							<?php foreach ($photos as $index => $photo) { ?>
								<div id="ph-<?php echo $index;?>"
								     class="-col-wrap <?php if ($index == 0) echo 'current';?>">
									<?php echo CHtml::image(
										'/' . $photo->preview->getPreviewName(StoreGallery::$preview['crop_60']),
										$photo->name,
										array('class' => '-quad-60')
									);?>
								</div>
							<?php } ?>

						<?php } ?>

					</div>
				</div>
			</div>

		</div>
		<div class="-col-3 -skip-1">
			<?php $this->renderPartial('_moneyRightSidebar', array(
				'store'        => $store,
				'showAddPhoto' => true
			)); ?>
		</div>

		<script>
			minisite.photogalleryActions();
		</script>
	</div>
</div>