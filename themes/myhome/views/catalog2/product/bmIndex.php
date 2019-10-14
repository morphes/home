<?php $this->pageTitle = $model->name . ' — ТВК «Большая Медведица» — MyHome.ru'; ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CCatalog.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/catalog.css'); ?>

<?php Yii::app()->clientScript->registerScriptFile('/js/fancybox.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/fancybox.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>

<?php Yii::app()->clientScript->registerScript('initial-run', '
        $(function(){
                cat.initBreadCrumbs();
                cat.photo();
                cat.showHint();
        });
', CClientScript::POS_READY);?>

<?php
// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = $model->name;
Yii::app()->openGraph->description = $model->desc;
// Обложка
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$model->cover->getPreviewName(Product::$preview['resize_380'], 'default', true);
// Остальные товары
foreach ($model->getImages(true) as $image) {
	Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$image->getPreviewName(Product::$preview['resize_380'], 'default', true);
}

Yii::app()->openGraph->renderTags();
?>

<?php if(in_array(Yii::app()->user->role, array(User::ROLE_POWERADMIN, User::ROLE_ADMIN, User::ROLE_MODERATOR)))
              $page_name = $model->name . ' ' . CHtml::link('[редакт.]', Yii::app()->createUrl('/catalog2/admin/product/update/', array('ids'=>$model->id, 'category_id'=>$model->category_id)), array('style'=>'text-decoration: underline;'));
      else
              $page_name = $model->name;
 ?>

<div class="-grid-wrapper page-content">

<?php $this->widget('catalog2.components.widgets.CatBreadcrumbs', array(
	'category' => $model->category,
	'pageName' => $page_name,
	'mallCatalogClass' => true,
	'afterH1'  => '',//$this->renderPartial('//widget/bmLogo', array(), true),
	'homeLink' => '<a href="/">Каталог ТВК «Большая Медведица»</a>',
)); ?>

<div class="product_card">

	<?php $this->renderPartial('_bmMenuBlock', array('model' => $model)); ?>

	<div class="product_info">
		<div class="product_photo">
			<div class="product_photo_big">
				<div>
					<!-- Вывод большой обложки товара -->
					<?php echo CHtml::image('/' . $model->cover->getPreviewName(Product::$preview['resize_380'], 'default', true), '', array('class' => 'show_origin', 'id' => 'ph_0')); ?>
				</div>
			</div>
			<div class="zoom_link">
				<i></i>
				<span>Увеличить</span>
			</div>
			<div class="product_photo_previews">
				<div class="product_photo_previews_container">
					<ul class="">
						<!-- Превью обложки -->
						<?php echo CHtml::openTag('li', array('class' => 'current'))?>
						<?php echo CHtml::openTag('a', array('data-src' => '/' . $model->cover->getPreviewName(Product::$preview['resize_960'], 'default', true), 'href' => '/' . $model->cover->getPreviewName(Product::$preview['resize_380'], 'default', true))); ?>
						<?php echo CHtml::image('/' . $model->cover->getPreviewName(Product::$preview['resize_60'], 'default', true)); ?>
						<?php echo CHtml::closeTag('a'); ?>
						<?php echo CHtml::closeTag('li'); ?>

						<!-- Превью остальных фото -->
						<?php foreach ($model->getImages(true) as $image) : ?>
						<?php echo CHtml::openTag('li') ?>
						<?php echo CHtml::openTag('a', array('data-src' => '/' . $image->getPreviewName(Product::$preview['resize_960'], 'default', true), 'href' => '/' . $image->getPreviewName(Product::$preview['resize_380'], 'default', true))); ?>
						<?php echo CHtml::image('/' . $image->getPreviewName(Product::$preview['resize_60'], 'default', true)); ?>
						<?php echo CHtml::closeTag('a'); ?>
						<?php echo CHtml::closeTag('li'); ?>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="buttons prev_photo disabled"><i></i></div>
				<div class="buttons next_photo"><i></i></div>
			</div>
		</div>
		<div class="product_descript">

			<?php
			// ---- Ц Е Н Ы  товара ----
			$price = StorePrice::getPriceOffer($model->id);

			if ($price['min'] == 0.0 && $price['mid'] == 0.0)
			{
				echo CHtml::tag('div', array('class' => 'item_price not_specified'), 'Цена не указана');
			}
			elseif ($price['min'] == 0.0 && $price['mid'] > 0)
			{
				echo CHtml::tag('div', array('class' => 'item_price'), '<span>' . number_format($price['mid'], 0, '.', ' ') . '</span> руб.');
			}
			elseif ($price['min'] > 0 && $price['mid'] > 0)
			{
				$averageText = CHtml::tag('p', array(), 'Средняя цена');
				echo CHtml::tag('div', array('class' => 'item_price'), $averageText.'<span>' . number_format($price['mid'], 0, '.', ' ') . '</span> руб.');
				echo CHtml::tag('div', array('class' => 'price_from'), '<span>от ' . number_format($price['min'], 0, '.', ' ') . ' руб.</span> ');
			}
			?>

			<div class="item_rating">
				<?php $this->widget('application.components.widgets.WStar', array(
				'selectedStar' => $model->average_rating,
				'addSpanClass' => 'rating-b',
			));?>
				<?php $feedback_qt = Feedback::model()->count('product_id=:pid', array(':pid' => $model->id));?>
				<?php echo CHtml::link(($feedback_qt == 0) ? 'Нет отзывов' : CFormatterEx::formatNumeral($feedback_qt, array('отзыв', 'отзыва', 'отзывов')), $this->createUrl('/product', array('id' => $model->id, 'action' => 'feedback'))); ?>
				<?php echo CHtml::link('Оставить отзыв', $this->createUrl('/product', array('id' => $model->id, 'action' => 'feedback')) . '#product_comment_form'); ?>
			</div>

			<p><?php echo Amputate::getLimb(nl2br($model->desc), 300); ?></p>

			<?php if (mb_strlen($model->desc, 'utf-8') > 300) : ?>
			<span class="all_elements_link" style="display: inline;">
						<?php echo CHtml::link('Подробнее', $this->createUrl('/product', array('id' => $model->id, 'action' => 'description'))); ?>
				<span>&rarr;</span>
					</span>
			<?php endif; ?>

			<ul class="item_params">
				<li>
					<span>Производитель:</span>
					<?php echo isset($model->vendor) ? CHtml::link($model->vendor->name, Vendor::getLink($model->vendor_id)) : ''; ?>
					(<?php echo isset($model->countryObj) ? $model->countryObj->name : ''; ?>)
				</li>

				<?php if (!empty($model->collectionName)) : ?>
				<li>
					<span>Коллекция:</span>
					<?php echo $model->collectionName; ?>
				</li>
				<?php endif; ?>

				<?php if (!empty($model->guaranty)) : ?>
				<li>
					<span>Гарантия:</span>
					<?php echo $model->guaranty; ?>
				</li>
				<?php endif; ?>

				<?php $this->widget('catalog.components.widgets.CatMinicardOptions', array('model' => $model)); ?>


			</ul>
				<span class="all_elements_link" style="display: inline;">
					<?php echo CHtml::link('Все характеристики', $this->createUrl('/product', array('id' => $model->id, 'action' => 'description'))); ?>
					<span>&rarr;</span>
				</span>
		</div>

		<div class="clear"></div>

		<div class="buyers_opinion">

			<?php if (!$feedbacks->getTotalItemCount()) : ?>
				<h3 class="headline">Мнения покупателей</h3>

				<p>Пока нет ни одного отзыва. Вы можете стать
				   первым!
						<span class="add_comment"><i></i>
							<?php echo CHtml::link('Оставьте свой отзыв', $this->createUrl('/product', array('id' => $model->id, 'action' => 'feedback')) . '#product_comment_form'); ?>
						</span>
				</p>
			<?php else : ?>
				<h3 class="headline">Мнения покупателей</h3>
				<?php echo CHtml::link($feedbacks->getTotalItemCount(), $this->createUrl('/product', array('id' => $model->id, 'action' => 'feedback')), array('class' => 'items_count')); ?>
				<span class="add_comment"><i></i>
					<?php echo CHtml::link('Оставьте свой отзыв', $this->createUrl('/product', array('id' => $model->id, 'action' => 'feedback')) . '#product_comment_form'); ?>
					    </span>

				<div class="product_page_comments">
					<?php $this->widget('zii.widgets.CListView', array(
						'dataProvider' => $feedbacks,
						'itemView'     => '_feedbackItemIndexPage',
						'template'     => '{items}'
					));?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="product_icons">
		<div class="stores_list">
			<h3 class="headline">Где купить</h3>


			<?php if (!empty($stores)) : ?>
				<ul class="products_shops promo">
					<?php // ---- ГДЕ КУПИТЬ ---- ?>
					<?php foreach ($stores as $store) : ?>
						<?php // Цена товара в магазине
						$storePrice = StorePrice::model()->findByAttributes(array(
							'store_id'   => $store->id,
							'product_id' => $model->id
						));
						$class = '';

						if ($storePrice && $storePrice->discount > 0) {
							$class = 'promo-address-block discount';
						}
						?>


						<li class="<?php echo $class; ?>">
							<?php echo CHtml::link($store->name, $this->createUrl('/catalog2/store/index', array('id' => $store->id))) ?>
							<!--<a href="<?php /*echo Yii::app()->params->bmHomeUrl . '/about'; */?>"
							   class="-orange"><?php /*echo MallBuild::model()->findByPk($store->mall_build_id)->name; */?></a>-->
							<a href="<?php echo Yii::app()->params->bmHomeUrl . '/about'; ?>" class="-tvk"><?php echo MallBuild::model()->findByPk($store->mall_build_id)->name; ?></a>

							<?php $popupData = $store->getMallData(); ?>
							<span href="<?php echo "#scheme".$store->id  ?>" id="<?php echo "toggleScheme".$store->id   ?>"  class="-acronym -gray">Показать на схеме ТВК</span>
							<?php /*echo '<a href="#scheme' . $store->id . '" id="toggleScheme' . $store->id . '" class="-acronym -gray">Показать на схеме ТВК</a>'; */?>
							<div style="display:none">
								<div id="scheme<?php echo $store->id; ?>">
									<div class="scheme-left">
										<h2><?php echo $store->name; ?></h2>

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
							<script>
								$(document).ready(function () {
									$('#toggleScheme<?php echo $store->id;?>').fancybox();
								});
							</script>


							<span>г.<?php echo $store->city->name; ?>
								<br/><?php echo $store->address; ?></span>


							<?php
							if ($storePrice && $storePrice->discount > 0 && $storePrice->price > 0) {
								?>
								<p>
									<span class="-inline -large"><strong><?php echo number_format($storePrice->getNumberDiscount(), 0, '.', ' ') ?>
											руб.</strong></span>
									<span class="-large -gray -gutter-left-hf"><?php echo number_format($storePrice->price, 0, '.', ' '); ?>
										руб.</span>
								</p>
							<?php
							} else {
								if ($storePrice && $storePrice->price > 0) : ?>
									<p>
										<strong><?php echo number_format($storePrice->price, 0, '.', ' '); ?>
											руб.</strong>
									</p>
								<?php else: ?>
									<p>цена
									   не
									   указана</p>
								<?php endif;
							}?>


						</li>
					<?php endforeach; ?>
				</ul>
			<?php else: ?>
				<p>В нашей базе пока что нет магазинов,
				   предлагающих этот товар. Скоро мы их
				   обязательно добавим, обещаем.</p>
			<?php endif; ?>

			<div class="clear"></div>
		</div>

		<?php // Подключаем виджет для добавления в избранное
		$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
			'modelId'   => $model->id,
			'modelName' => get_class($model),
			'viewHeart'  => 'favoriteMedia',
		));

		if(isset($addToFolder) and $addToFolder)
		{
		$this->widget('catalog.components.widgets.AddToFolder.AddToFolder', array(
				'modelId'   => $model->id,
				'view'	    => 'addProductCard'
			  ));

		}
		?>

		<div class="social_links">

			<?php $this->widget('ext.sharebox.EShareBox', array(
			'view'             => 'product',
			// url to share, required.
			'url'              => Yii::app()->request->hostInfo . Yii::app()->request->requestUri,

			'imgUrl' => Yii::app()->request->hostInfo.'/' .$model->cover->getPreviewName(Product::$preview['resize_960']),

			// A title to describe your link, required.
			'title'            => !empty($model->name) ? $model->name : 'товар',

			// A small message for post
			'message'          => Amputate::getLimb($model->desc, 500, '...'),
			'classDefinitions' => array(
				'livejournal' => 'ns-lj',
				'vkontakte'   => 'ns-vk',
				'twitter'     => 'ns-tw',
				'facebook'    => 'ns-fb',
				'google+'     => 'ns-gp',
				'pinterest'   => 'ns-pi',
			),
			'exclude' => array('odkl'),
			'htmlOptions'      => array('class' => 'social'),
		));?>

		</div>
	</div>

	<div class="clear"></div>

	<?php $similars = $model->getSimilar(true); ?>

	<?php if ($similars) : ?>
		<div class="-col-9 -gutter-bottom-dbl -inset-bottom promo-items-list">
			<div class="-grid">
				<h2 class="-col-wrap -gutter-bottom-dbl -huge">Другие варианты этой модели</h2>
				<div class="items">
					<?php
					$classGutterRight = '-gutter-right-hf';
					unset($end);
					$arrayKeys=array_keys($similars);
					$end = end($arrayKeys);
					foreach ($similars as $key => $similar) :
						if ($end===$key)
						{
							$classGutterRight='';
						}
						?>
						<div class="-col-wrap item <?php echo $classGutterRight ?>">
							<a href="<?php echo Product::getLink($similar->id, null, $similar->category_id); ?>">
								<?php if ($similar->cover) : ?>
									<?php echo CHtml::image('/' . $similar->cover->getPreviewName(Product::$preview['resize_120']), '', array('width'=>120, 'height'=>120)); ?>
								<?php endif; ?>
								<span class="-block -large"><?php echo $similar->name ?></span>
							</a>
						</div>
					<?php endforeach; ?>

				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($relatedItemsData) : ?>
		<div class="-col-9 -gutter-bottom-dbl -inset-bottom promo-items-list">
			<div class="-grid">
				<h2 class="-col-wrap -gutter-bottom-dbl -huge">
					Похожие товары в магазине
					<?php
					echo '«' . CHTML::link(Store::model()->findByPk($store_id)->name . '»', Store::model()->getLink($store_id));
					?>
				</h2>

				<div class="items">
					<?php
					unset($end);
					$classGutterRight = '-gutter-right-hf';
					$arrayKeys=array_keys($relatedItemsData);
					$end = end($arrayKeys);
					foreach ($relatedItemsData as $key => $rld) :
						if ($end===$key) {
							$classGutterRight = '';
						}
						?>
						<div class="-col-wrap item <?php echo $classGutterRight ?>">
							<a href="<?php echo Product::model()->getLink($rld->id, $store_id) ?>">
								<?php echo CHtml::image('/' . $rld->cover->getPreviewName(Product::$preview['resize_120']), '', array('class' => '-quad-120 -gutter-bottom')); ?>
								<span class="-block -large"><?php echo $rld->name ?></span>
							</a>

							<?php
							$pr = $rld->getStorePrice($store_id);
							if ((int)$pr['price']) {
								echo CHtml::tag('span', array('class' => '-strong'), number_format((int)$pr['price'], 0, '.', ' ') . ' руб.');
							} else {
								echo CHtml::tag('span', array('class' => '-gray'), 'Цена не указана');
							}
							?>

						</div>
					<?php endforeach; ?>

				</div>
			</div>
		</div>
	<?php endif; ?>


	<div class="spacer-30"></div>
</div>
</div>



