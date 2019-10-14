
<?php /** @var $this FrontController */?>
<?php $this->pageTitle = $model->name . ' — ' . $model->category->name . ' — MyHome.ru'; ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CCatalog.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/catalog.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/bm.css'); ?>

<?php Yii::app()->clientScript->registerScriptFile('/js/fancybox.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/fancybox.css'); ?>

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
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$model->cover->getPreviewName(Product::$preview['resize_380']);
// Остальные товары
foreach ($model->getImages(true) as $image) {
	Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$image->getPreviewName(Product::$preview['resize_380']);
}

Yii::app()->openGraph->renderTags();
?>

<?php if(in_array(Yii::app()->user->role, array(User::ROLE_POWERADMIN, User::ROLE_ADMIN, User::ROLE_MODERATOR)))
              $page_name = $model->name . ' ' . CHtml::link('[редакт.]', Yii::app()->createUrl('/catalog/admin/product/update/', array('ids'=>$model->id, 'category_id'=>$model->category_id)), array('style'=>'text-decoration: underline;'));
      else
              $page_name = $model->name;
 ?>

<?php $this->widget('catalog.components.widgets.CatBreadcrumbs', array(
	'category' => $model->category,
	'pageName' => $page_name,
)); ?>

<div class="product_card">

	<?php $this->renderPartial('_menuBlock', array('model' => $model, 'store_id'=>$store_id)); ?>

	<div class="product_info">
		<div class="product_photo">
			<div class="product_photo_big">
				<div>
					<!-- Вывод большой обложки товара -->
					<?php
					$src = $model->cover->getPreviewName(Product::$preview['resize_380']);
					$srcSize = UploadedFile::getImageSize($src);
					echo CHtml::image(
						'/' . $src,
						'',
						array(
							'class'  => 'show_origin',
							'id'     => 'ph_0',
							'width'  => $srcSize['width'],
							'height' => $srcSize['height']
						)
					);
					?>
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
						<?php echo CHtml::openTag('a', array('data-src' => '/' . $model->cover->getPreviewName(Product::$preview['resize_960']), 'href' => '/' . $model->cover->getPreviewName(Product::$preview['resize_380'], 'default', true))); ?>
						<?php
							$src = $model->cover->getPreviewName(Product::$preview['resize_60']);
							$srcSize = UploadedFile::getImageSize($src);
							echo CHtml::image('/' . $src, '', array('width'=>$srcSize['width'], 'height'=>$srcSize['height']));
						?>
						<?php echo CHtml::closeTag('a'); ?>
						<?php echo CHtml::closeTag('li'); ?>

						<!-- Превью остальных фото -->
						<?php foreach ($model->getImages(true) as $image) : ?>
						<?php echo CHtml::openTag('li') ?>
						<?php
							echo CHtml::openTag('a',
								array(
									'data-src' => '/' . $image->getPreviewName(Product::$preview['resize_960']),
									'href' => '/' . $image->getPreviewName(Product::$preview['resize_380'],'default', true)
								)
							); ?>
						<?php
							$src = $image->getPreviewName(Product::$preview['resize_60']);
							$srcSize = UploadedFile::getImageSize($src);
							echo CHtml::image('/' . $src, '', array('width'=>$srcSize['width'], 'height'=>$srcSize['height']));
						?>
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

		<div class="buyers_opinion <?php if ($feedbacks->getTotalItemCount()) echo 'has_items'; ?>">

			<?php if (!$feedbacks->getTotalItemCount()) : ?>
				<h3 class="headline">Мнения покупателей</h3>

				<p>Пока нет ни одного отзыва. Вы можете стать первым!
					<span class="add_comment"><i></i>
						<?php echo CHtml::link('Оставьте свой отзыв', $this->createUrl('/product', array('id' => $model->id, 'action' => 'feedback')) . '#product_comment_form'); ?>
					</span>
				</p>
			<?php else : ?>
				<h3 class="headline">Мнения покупателей</h3>
				<?php echo CHtml::link($feedbacks->getTotalItemCount(), $this->createUrl('/product', array('id' => $model->id, 'action' => 'feedback')), array('class' => 'items_count')); ?>
				<span class="add_comment"><i></i><?php echo CHtml::link('Оставьте свой отзыв', $this->createUrl('/product', array('id' => $model->id, 'action' => 'feedback')) . '#product_comment_form'); ?></span>

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


			<?php if ( ! empty($stores)) : ?>
				<ul class="products_shops promo">
					<?php // ---- ГДЕ КУПИТЬ ---- ?>
					<?php
					// Выводим сначала магазины из ТЦ
					$li = '';
					foreach ($stores as $store)
					{
						if ( ! $store->mall_build_id)
							continue;

						if ($li == '')
							$li .= '<li class="promo-address-block">';

						$li .= $this->renderPartial('//catalog/product/_whereBy', array(
							'store'      => $store,
							'product_id' => $model->id,
							'mall'       => MallBuild::model()->findByPk($store->mall_build_id)
						), true);
					}
					$li .= '</li>';
					echo $li;

					// Выводим все магазины, которые не относятся ТЦ
					$li = '';
					foreach ($stores as $store)
					{
						if ($store->mall_build_id > 0)
							continue;

						if ($li != '')
							$li .= '</li>';
						$li .= '<li>';


						$li .= $this->renderPartial('//catalog/product/_whereBy', array('store' => $store, 'product_id' => $model->id), true);
					}
					$li .= '</li>';
					echo $li;
					?>
				</ul>
			<?php else: ?>
				<p>В нашей базе пока что нет магазинов, предлагающих этот товар. Скоро мы их обязательно добавим, обещаем.</p>
			<?php endif; ?>

                        <?php // Если в get параметрах не указан store_id, то отображаются ссылки для поиска товара в других магазинах ?>
                        <?php if ($store_id===null) : ?>
                                <?php if ($city) : ?>
                                <span class="all_elements_link" style="display: inline;">
                                        <?php echo CHtml::link('В ' . $city->prepositionalCase, $this->createUrl('/product', array('id' => $model->id, 'cid' => $city->id, 'action' => 'storesInCity'))); ?>
                                        <span>&rarr;</span>
                                </span>
                                <?php endif; ?>

                                <div class="clear"></div>

                                <?php if ( ! empty($stores)) : ?>
                                <span class="all_elements_link" style="display: inline;">
                                        <?php echo CHtml::link('Во всех городах', $this->createUrl('/product', array('id' => $model->id, 'action' => 'stores'))); ?>
                                        <span>&rarr;</span>
                                </span>
                                <?php endif; ?>
                        <?php endif; ?>
		</div>

		<?php // Подключаем виджет для добавления в избранное
		$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
			'modelId'   => $model->id,
			'modelName' => get_class($model),
			'viewHeart'  => 'favoriteMedia',
		));?>


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
				<h2 class="-col-wrap -gutter-bottom-dbl -huge">
					Другие варианты этой модели</h2>

				<div class="items">
					<?php
					$classGutterRight = '-gutter-right-hf';
					unset($end);
					$arrayKeys=array_keys($similars);
					$end = end($arrayKeys);
					foreach ($similars as $key => $similar) :
						if ($end===$key) {
							$classGutterRight = '';
						}
						?>
						<div class="-col-wrap item <?php echo $classGutterRight ?>">
							<a href="<?php echo Product::getLink($similar->id, null, $similar->category_id); ?>">
								<?php if ($similar->cover) : ?>
									<?php echo CHtml::image('/' . $similar->cover->getPreviewName(Product::$preview['resize_120']), '', array('width' => 120, 'height' => 120)); ?>
								<?php endif; ?>
								<span class="-block -large"><?php echo $similar->name ?></span>
							</a>
						</div>
					<?php endforeach; ?>

				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php //<Блок похожие товары для магазинов с платным тарифом ?>
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
								<?php echo CHtml::image('/' . $rld->cover->getPreviewName(Product::$preview['resize_120']), '',
								  array('class' => '-quad-120 -gutter-bottom')); ?>
								<span class="-block -large"><?php echo $rld->name ?></span>
							</a>

							<?php
							$price = $rld->getStorePrice($store_id);
							if ((int)$price['price']) {
								echo CHtml::tag('span', array('class' => '-strong'), number_format($price['price'], 0, '.', ' ') . ' руб.');
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
</div>


<div class="spacer-30"></div>
