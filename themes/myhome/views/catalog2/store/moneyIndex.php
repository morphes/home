<?php $this->pageTitle = $store->name; ?>

<div class="-grid-wrapper page-content">
	<div class="-grid">
		<div class="-col-8">

			<h1 class="-gutter-bottom-hf"><?php echo $store->name;?>

				<?php
				/* ---------------------------------------------
				 *  Ссылка редактирования описания. Для владельца
				 * ---------------------------------------------
				 */
				if ($isOwner && !empty($store->about)) { ?>
					<a href="<?php echo $this->createUrl('/catalog2/profile/storeUpdate', array('id' => $store->id));?>" class="-push-right -small -normal -gray -icon-pencil-xs">Редактировать описание</a>
				<?php } ?>

			</h1>
			<span class="-block -huge -gutter-bottom-dbl"><?php echo $store->activity;?></span>
			<div class="-grid">
				<?php if (empty($store->about)) { ?>

					<?php if ($isOwner) { ?>
						<div class="-col-6 -tinygray-box -inset-all block-placeholder">
							<p class="-text-align-center -inset-all -gutter-top-hf -large">
								Чем больше информации о
								вас увидят посетители,
								тем больше новых
								покупателей вы можете
								пробрести!<span class="-block -gutter-top-hf"><a href="<?php echo $this->createUrl('/catalog/profile/storeUpdate', array('id' => $store->id));?>"
														 class="-red">Рассказать
															      о
															      магазине</a></span>
							</p>
						</div>
					<?php } ?>

				<?php } else { ?>

					<div class="-col-6">
						<p class="collapsed-text"><?php echo nl2br($store->about);?></p>

						<?php if (mb_strlen($store->about, 'UTF-8') > 400) { ?>
							<span class="-acronym -gray -small" data-alt="Свернуть">Подробнее</span><i class="-small -pointer-down "></i>
							<script>
								minisite.toggleDesc()
							</script>
						<?php } ?>
					</div>

				<?php } ?>


				<?php
				/* -------------------------------------
				 *  Фотогалерея
				 * -------------------------------------
				 */
				$photos = StoreGallery::model()->findAllByAttributes(array(
					'store_id' => $store->id,
					'status' => StoreGallery::STATUS_PUBLIC
				), array(
					'limit' => 4,
					'order' => 'update_time DESC'
				));
				?>
				<?php if (!empty($photos)) { ?>
				<div class="-col-2">

					<div class="-inline-wrapper">
						<?php if (count($photos) == 4) { ?>

							<?php foreach ($photos as $photo) { ?>
								<div class="-inline"><a href="<?php echo Store::getLink($store->id, 'moneyFotos');?>" class="-block"><img src="<?php echo '/' . $photo->preview->getPreviewName(StoreGallery::$preview['crop_60']);?>" class="-quad-60"></a></div>
							<?php } ?>

						<?php } elseif (isset($photos[0])) { ?>

							<div class="-inline"><a href="<?php echo Store::getLink($store->id, 'moneyFotos');?>" class="-block"><img src="<?php echo '/' . $photos[0]->preview->getPreviewName(StoreGallery::$preview['crop_140']);?>" class="-quad-140"></a></div>

						<?php } ?>

					</div>
					<a href="<?php echo Store::getLink($store->id, 'moneyFotos');?>" class="-gutter-left-hf -pointer-right -small -gray">Фотогалерея</a>
				</div>
				<?php } ?>
			</div>


			<h2 class="-gutter-bottom-hf">Мы предлагаем

				<?php
				/* ---------------------------------------------
				 *  Ссылка обновления товаров. Для владельца.
				 * ---------------------------------------------
				 */
				if ($isOwner && !$store->checkEmptyShowcase()) { ?>
					<a href="<?php echo $this->createUrl('/catalog2/profile/storeShowcase', array('id' => $store->id)); ?>" class="-block -gutter-top -push-right -small -normal -gray -icon-pencil-xs">Обновить товары</a>
				<?php } ?>
			</h2>

			<?php if ($store->productQt > 0) { ?>
				<ul class="-menu-inline goods-shortcuts">

					<?php
					/* -------------------------------------
					 *  Список категорий
					 * -------------------------------------
					 */
					?>
					<?php echo $navListCategory; ?>

				</ul>

			<?php } ?>

			<div class="-grid goods-list">

				<?php if ($store->checkEmptyShowcase() && $isOwner) { ?>
					<div class="catalog_items_list_small store empty">
						Добавьте ваши лучшие товары на
						витрину<br>
						<a class="edit hover"
						   href="<?php echo $this->createUrl(
							   '/catalog2/profile/storeShowcase/',
							   array('id' => $store->id, 'from_card' => 1)
						   ); ?>"><i></i>Редактировать</a>
					</div>

					<div class="-grid -inset-top-hf">
						<div class="-col-8 -tinygray-box -inset-all block-placeholder">
							<p class="-text-align-center -inset-all -gutter-top-hf -large">
								Добавьте ваши лучшие товары на витрину.
								<br>
								<span class="-block -gutter-top-hf"><a href="<?php echo $this->createUrl('/catalog2/profile/storeShowcase/', array('id' => $store->id, 'from_card' => 1)); ?>" class="-red">Редактировать витрину товаров</a></span>
							</p>
						</div>
					</div>
				<?php } ?>

				<?php foreach ($store->getShowcase_data() as $pid) : ?>
					<?php
					/** @var $product Product */
					$product = Product::model()->find(
						'id=:id and status=:st',
						array(':id' => $pid, ':st' => Product::STATUS_ACTIVE)
					);
					if (!$product) {
						continue;
					}

					// Получаем цену
					$discountClass = '';
					$price = $product->getStorePrice($store->id);
					if ($price && $price['price'] > 0) {
						if ($price['discount'] > 0) {
							$discountClass = 'discount';
							$realPrice = $price['price'] * (1 -  $price['discount'] / 100);
						} else {
							$realPrice = $price['price'];
						}
						$priceHtml = '<span>'.number_format($realPrice, 0, '.', ' ') . ' руб.'.'</span>';
					} else {
						$priceHtml = '<span>Цена не указана</span>';
					}

					?>
					<div class="-col-2 <?php echo $discountClass;?>">
						<a href="<?php echo Product::getLink($product->id, null, $product->category_id) . '?store_id=' . $store->id; ?>" class="-block">
							<?php echo CHtml::image(
								'/' . $product->cover->getPreviewName(Product::$preview['crop_140']),
								'',
								array('width' => 140, 'height' => 140, 'class' => '-quad-140')
							); ?>
							<span><?php echo $product->name; ?></span>
						</a>

						<?php echo $priceHtml; ?>
					</div>


				<?php endforeach; ?>
			</div>


			<?php if ($isOwner && empty($storeNews)) { ?>

				<h2 class="-gutter-bottom">Новости и акции<a href="<?php echo StoreNews::getLink($store, 'list');?>" class="-gutter-left-hf -pointer-right -small -gray">Все</a></h2>
				<div class="-grid -inset-top-hf">
					<div class="-col-8 -tinygray-box -inset-all block-placeholder">
						<p class="-text-align-center -inset-all -gutter-top-hf -large">
							Добавляйте новости и акции вашего магазина.
							<br>Это поможет привлечь больше покупателей!
								<span class="-block -gutter-top-hf">
									<a href="<?php echo StoreNews::getLink($store, 'list');?>" class="-red">Добавить новость или акцию</a>
								</span>
						</p>
					</div>
				</div>

			<?php } elseif (!empty($storeNews)) { ?>

				<h2 class="-gutter-bottom">Новости и акции<a href="<?php echo StoreNews::getLink($store, 'list');?>" class="-gutter-left-hf -pointer-right -small -gray">Все</a></h2>
				<div class="-grid -inset-top-hf">
					<?php foreach ($storeNews as $data) { ?>
						<?php
						if ($data->preview) {
							echo '<div class="-col-2 -gutter-bottom-dbl">';
							echo CHtml::image(
								'/' . $data->preview->getPreviewName(StoreNews::$preview['crop_140']),
								'',
								array('width' => 140, 'height' => 140, 'class' => '-quad-140')
							);
							echo '</div>';
						}
						?>
						<div class="-col-6 -gutter-bottom-dbl">
							<?php
							echo CHtml::link(
								$data->title,
								StoreNews::getLink($store, 'element', $data->id),
								array('class' => '-large -strong')
							);
							?>
							<p class="-gutter-top-hf -gutter-bottom-hf"><?php echo nl2br(Amputate::getLimb($data->content, 400)); ?></p>
							<span class="-small -gray"><?php echo CFormatterEx::formatDateToday($data->create_time);?></span>
						</div>
					<?php } ?>
				</div>

			<?php } ?>

		</div>
		<div class="-col-3 -skip-1">
			<?php $this->renderPartial('_moneyRightSidebar', array(
				'store' => $store
			)); ?>
		</div>
	</div>
</div>

