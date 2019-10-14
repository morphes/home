<?php
/**
 * @var $model Store Магазин
 */
?>

<?php $this->pageTitle = $model->name . ' — Магазины — MyHome.ru'; ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CCatalog.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/fancybox.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/fancybox.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/catalog.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerScript('initial-run', '
        $(function(){
                cat.initBreadCrumbs();
        });
', CClientScript::POS_READY);?>

<div class="-grid-wrapper page-content">

<div class="pathBar">
	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'encodeLabel' => false,
		'homeLink' => '<a href="'.Yii::app()->params->bmHomeUrl.'">Каталог ТВК «Большая Медведица»</a>',
		'links'       => array(
			'Магазины' => $this->createUrl('/catalog/stores')
		),
	));?>

	<h1><?php echo CHtml::value($model, 'name'); ?></h1>
	<?php if ($isOwner) : ?>
		<a class="edit"
		   href="<?php echo $this->createUrl('/catalog/profile/storeUpdate/', array('id' => $model->id)); ?>"><i></i>Редактировать</a>
	<?php endif; ?>

	<div class="spacer"></div>
</div>


<div class="shop_card">

<?php $this->renderPartial('_menuBlock', array('model' => $model)); ?>

<div class="shop_info">
	<?php if ($model->uploadedFile) {
		$src = $model->uploadedFile->getPreviewName(Config::$preview['resize_140']);
		echo CHtml::image('/' . $src, '', UploadedFile::getImageSize($src));
	}
	else{
		echo CHTML::image('/img-new/nologo-store-140.png');
	}
	?>
	<div class="shop_contacts">
		<p class="-gutter-null"><?php echo $model->country->name . ', ' . $model->city->name; ?></p>

		<p class="-gutter-null"><?php echo $model->address; ?></p>

		<?php
		// ---->>-- Блок «БОЛЬШАЯ МЕДВЕДИЦА» ---->>--
		$popupData = $model->getMallData();
		?>
		<?php if ($popupData) { ?>
			<p class="-gutter-null"><?php echo CHtml::link($popupData['mall_name'], Yii::app()->params->bmHomeUrl, array('class' => '-red')); ?></p>
			<script>
				$(document).ready(function () {
					$('#toggleScheme').fancybox();
				});
			</script>
			<p><a href="#scheme"
			      id="toggleScheme"
			      class="-acronym -small -gray">Показать на схеме
							    ТВК</a></p>

			<div style="display:none">
				<div id="scheme">
					<div class="scheme-left">
						<h2><?php echo $model->name; ?></h2>

						<div class="scheme-bage">
							<span class="-large -darkgray -gutter-null">Этаж</span><span class="-giant -gutter-null"><?php echo $popupData['floor_name']; ?></span>
						</div>
						<div class="scheme-bage">
							<span class="-large -darkgray -gutter-null">Секция</span><span class="-giant -gutter-null"><?php echo $popupData['sect_name']; ?></span>
						</div>
					</div>
					<div class="scheme-right"
					     style="background: url('<?php echo '/' . $popupData['floor_img']->getPreviewName(MallFloor::$preview['resize_0x520']); ?>') 0 20px no-repeat;"></div>
				</div>
			</div>
		<?php } ?>
		<?php // --<<---- Блок «БОЛЬШАЯ МЕДВЕДИЦА» --<<---- ?>

		<p><?php echo $model->phone; ?></p>
		<ul class="timetable">

			<?php if ( $model->type === Store::TYPE_OFFLINE ) : ?>
				<?php $time = $model->getTimeArray(); ?>
				<li><span>Пн-Пт</span><span
						class="time"><?php echo $time['weekdays']['work_from'] . '–' . $time['weekdays']['work_to']; ?></span>
				</li>
				<li><span>Сб</span><span
						class="time"><?php echo $time['saturday']['work_from'] . '–' . $time['saturday']['work_to']; ?></span>
				</li>
				<li><span>Вс</span><span
						class="time"><?php echo $time['sunday']['work_from'] . '–' . $time['sunday']['work_to']; ?></span>
				</li>
			<?php endif; ?>
		</ul>
                <span class="mails_list">
                        <?php if ($model->email)
				$links[] = CHtml::link($model->email, 'mailto:' . $model->email); ?>

			<?php
			if ($model->site && $model->tariff_id != Store::TARIF_FREE && $model->tariff_id != null) {
				$links[] = CHtml::link(
					Amputate::absoluteUrl($model->site),
					$this->createUrl(
						'viewSite',
						array(
							'store_id' => $model->id
						)
					),
					array('target' => '_blank')
				);
			} ?>
			<?php echo !empty($links) ? implode('<br> ', $links)
				: ''; ?>
                </span>
	</div>
	<div class="shop_rubrics">
		<?php $chain = $model->chain; ?>
		<?php if ($chain) : ?>
			<div class="chain_stores">
				<span>Входит в сеть:</span> <?php echo CHtml::link($chain->name, $this->createUrl('/catalog/chain/index', array('id' => $chain->id))); ?>
			</div>
		<?php endif; ?>

		<?php if ($model->tariff_id != Store::TARIF_FREE) : ?>
			<div class="catalog_sections">
				<?php
				$links = array();
				$connection = Yii::app()->dbcatalog2;
				foreach ($model->getUsedCategory() as $cat_id) {
					$catName = $connection->createCommand()->select('name')->from('cat_category')->where('id=:id', array(':id' => $cat_id))->queryScalar();
					$links[] = CHtml::link($catName, $this->createUrl('/catalog/store/products/', array('id' => $model->id, 'category_id' => $cat_id)));
				}
				echo implode(', ', $links);
				?>
			</div>
		<?php endif; ?>
	</div>
	<div class="item_rating">
		<?php $this->widget('application.components.widgets.WStar', array(
			'selectedStar' => $model->average_rating,
			'addSpanClass' => 'rating-b',
		));?> <br>
		<?php $feedback_qt = $model->feedbackQt; ?>
		<?php echo CHtml::link(($feedback_qt == 0) ? 'Нет отзывов'
			: CFormatterEx::formatNumeral($feedback_qt, array('отзыв', 'отзыва', 'отзывов')), $this->createUrl('/catalog/store/feedback', array('id' => $model->id))); ?>
		<?php echo CHtml::link('Оставить отзыв', $this->createUrl('/catalog/store/feedback', array('id' => $model->id)) . '#product_comment_form'); ?>
	</div>
	<div class="clear"></div>
</div>
<div class="shop_left">

	<?php if ($model->tariff_id != Store::TARIF_FREE): ?>
		<div class="other_models">
			<h3 class="headline">Товары</h3>
			<span class="comments_quant"><?php echo $model->productQt; ?></span>
                        <span class="all_elements_link"
			      style="display: inline;">
                                <a href="<?php echo $this->createUrl('/catalog/store/products/', array('id' => $model->id)); ?>">Все</a>
                                <span>&rarr;</span>
                        </span>
			<?php if ($model->checkEmptyShowcase() && $isOwner) : ?>
				<div class="catalog_items_list_small store empty">
					Добавьте ваши лучшие товары на
					витрину<br>
					<a class="edit hover"
					   href="<?php echo $this->createUrl('/catalog/profile/storeShowcase/', array('id' => $model->id, 'from_card' => 1)); ?>"><i></i>Редактировать
																					 витрину
																					 товаров</a>
				</div>
			<?php else : ?>

				<?php if ($isOwner) : ?>
					<a class="edit"
					   href="<?php echo $this->createUrl('/catalog/profile/storeShowcase/', array('id' => $model->id)); ?>"><i></i>Редактировать
																		       витрину
																		       товаров</a>
				<?php endif; ?>

				<div class="catalog_items_list_small store">
					<?php foreach ($model->getShowcase_data() as $pid) : ?>
						<?php
						$product = Product::model()->find('id=:id and status=:st', array(':id' => $pid, ':st' => Product::STATUS_ACTIVE));
						if (!$product)
							continue;
						?>
						<div class="item">
							<a href="<?php echo Product::getLink($product->id, null, $product->category_id) . '?store_id=' . $model->id; ?>">
								<?php echo CHtml::image('/' . $product->cover->getPreviewName(Product::$preview['crop_120']), '', array('width' => 120, 'height' => 120)); ?>
							</a>

							<h2>
								<a href="<?php echo Product::getLink($product->id, $model->id, $product->category_id); ?>"><?php echo $product->name; ?></a>
							</h2>
							<?php $price = $product->getStorePrice($model->id); ?>
							<?php if ($price && $price['price'] > 0) : ?>
								<span class="price"><?php echo number_format($price['price'], 0, '.', ' ') . ' руб.'; ?></span>
							<?php else : ?>
								<span class="price not_specified">Цена не указана</span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					<div class="clear"></div>
				</div>
			<?php endif; ?>
		</div>
		<?php if (!empty($model->about) || $isOwner) : ?>
			<div class="shop_description">
				<h3 class="headline">О магазине</h3>
				<?php if ($isOwner) : ?>
					<a class="edit"
					   href="<?php echo $this->createUrl('/catalog/profile/storeUpdate/', array('id' => $model->id)); ?>"><i></i>Редактировать
																		     описание</a>
				<?php endif; ?>
				<p><?php echo CHtml::encode($model->about); ?></p>
				<?php if (empty($model->about) && $isOwner) : ?>
					<div class="empty">
						<a class="edit hover"
						   href="<?php echo $this->createUrl('/catalog/profile/storeUpdate/', array('id' => $model->id)); ?>"><i></i>Добавить
																			     описание
																			     магазина</a>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<div class="shop_map">
		<div id="map"
		     style="width: 698px; height: 278px;"></div>
		<script type="text/javascript"
			src="http://api-maps.yandex.ru/2.0/?coordorder=longlat&load=package.full&wizard=constructor&lang=ru-RU"></script>
	</div>
	<?php
	$lat = $model->getCoordinates('lat');
	$lng = $model->getCoordinates('lng');
	$hint = $model->name;
	$mapData = array($lat, $lng, $hint);
	?>
	<?php Yii::app()->clientScript->registerScript('yandexmap', '
                var map;
                var group;

                function init()
                {
                    map = new ymaps.Map ("map", {
                            center: [55.76, 37.64],
                            zoom: 7
                        });
                    map.controls.add("zoomControl").add("mapTools").add(new ymaps.control.TypeSelector(["yandex#map", "yandex#satellite", "yandex#hybrid", "yandex#publicMap"]));
                    var data = ' . CJavaScript::encode($mapData) . ';
                    if(data[0] == "" || data[1] == "")
                        return;

                    var pos = [data[0], data[1]];
                    group = new ymaps.GeoObjectCollection();
                    group.add(new ymaps.Placemark(pos, {balloonContent: data[2]}));
                    map.geoObjects.add(group);
                    map.setCenter(pos, 14);
                }

                ymaps.ready(function(){
                        init();
                });

        ', CClientScript::POS_LOAD);?>

	<div class="buyers_opinion">

		<?php if (!$feedbacks->getTotalItemCount()) : ?>
			<h3 class="headline">Отзывы о магазине</h3>

			<p>Пока нет ни одного отзыва. Вы можете стать первым!
                        <span class="add_comment"><i></i>
				<?php echo CHtml::link('Оставьте свой отзыв', $this->createUrl('/catalog/store/feedback', array('id' => $model->id)) . '#product_comment_form'); ?>
                        </span>
			</p>
		<?php else : ?>
			<h3 class="headline">Отзывы о магазине</h3>
			<span class="comments_quant"><?php echo $feedbacks->getTotalItemCount(); ?></span>
			<span class="all_elements_link"
			      style="display: inline;">
                        <?php echo CHtml::link('Все', $this->createUrl('/catalog/store/feedback', array('id' => $model->id))); ?>
				<span>&rarr;</span>
                    </span>

			<div class="product_page_comments">
				<?php $this->widget('zii.widgets.CListView', array(
					'dataProvider' => $feedbacks,
					'itemView'     => '_feedbackItemIndexPage',
					'template'     => '{items}',
					'viewData'     => array('model' => $model),
				));?>
			</div>
		<?php endif; ?>
	</div>
	<div class="clear"></div>


</div>

<div class="product_icons">
	<?php if ($model->tariff_id != Store::TARIF_FREE) : ?>
		<div class="manufacturer_list">
			<h3 class="headline">Производители</h3>
			<ul class="">
				<?php $i = 0; ?>
				<?php foreach ($model->getVendors() as $vendor) : ?>
					<?php if ($i > 3)
						break; else $i++; ?>
					<li>
						<a title="<?php echo $vendor->name; ?>"
						   href="<?php echo $this->createUrl('/catalog/store/products/', array('id' => $model->id, 'vendor_id' => $vendor->id)); ?>">
							<?php
							if ($vendor->uploadedFile)
								$src = '/' . $vendor->uploadedFile->getPreviewName(Vendor::$preview['resize_50']);
							else
								$src = '/' . UploadedFile::model()->getPreviewName(Vendor::$preview['resize_50']);

							echo CHtml::image($src, $vendor->name, array('style' => 'max-width:50px; max-height:50px;'));
							?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="clear"></div>
		</div>
	<?php endif; ?>

	<!--<div class="favorite_button add_this_to_favorite">
	    <i></i>
	    <a href="#">В избранное</a>
	</div>-->

	<div class="social_links">
		<?php $this->widget('ext.sharebox.EShareBox', array(
			'view'             => 'product',
			// url to share, required.
			'url'              => Yii::app()->request->hostInfo . Yii::app()->request->requestUri,

			// A title to describe your link, required.
			'title'            => $model->name,

			// A small message for post
			'message'          => Amputate::getLimb($model->about, 500, '...'),
			'classDefinitions' => array(
				'livejournal' => 'ns-lj',
				'vkontakte'   => 'ns-vk',
				'twitter'     => 'ns-tw',
				'facebook'    => 'ns-fb',
				'google+'     => 'ns-gp',
			),
			'exclude'          => array('odkl','pinterest'),
			'htmlOptions'      => array('class' => 'social'),
		));?>
	</div>
</div>

<div class="spacer-30"></div>
</div>

</div>

<div class="clear"></div>

<?php //<Блок похожие магазины ?>
<?php if ($relatedShops) : ?>
	<div class="-grid">
		<h2 class="-col-12 -gutter-bottom-dbl -huge">Похожие
							     магазины</h2>

		<div class="-col-12">
			<div class="-grid similar-stores-list">
				<?php foreach ($relatedShops as $rs): ?>
					<div class="-col-2">
						<a href="<?php echo Store::model()->getLink($rs->id) ?>">
							<?php if ($rs->uploadedFile) {
								$src = $rs->uploadedFile->getPreviewName(Config::$preview['resize_120_border']);
								echo CHtml::image('/' . $src, '', array('class' => '-quad-120 -gutter-bottom'));
							}
							else{
								echo CHTML::image('/img-new/nologo-store-120.png','', array('class' => '-quad-120 -gutter-bottom'));
							}
							?>
							<span class="-block"><?php echo $rs->name ?></span>
						</a>
						<span class="-gray -small"><?php echo $rs->city->name . ',';
							echo $rs->address; ?></span>
					</div>
				<?php endforeach; ?>
			</div>

		</div>
	</div>

<?php endif; ?>
</div>