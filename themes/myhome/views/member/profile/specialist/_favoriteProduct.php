<?php Yii::app()->clientScript->registerCssFile('/css/catalog.css'); ?>

<?php $countModels = count($models); ?>

<?php if ($countModels > 0): ?>
	<div class="favorite_item">
		<h3>Товары<span class="items_count quant_f"><?php echo $countModels;?></span></h3>
		<span class="hide_items"><i></i><a href="#">Свернуть</a></span>

		<div class="favorite_list_conteiner">
			<div class="catalog_items_list">

				<?php foreach($models as $data) : ?>
				<div class="item">
					<?php if ($this->beginCache("productItemId{$data->id}", array('duration' => 3600))) : ?>

					<div class="item_inner">
						<div class="item_photo">
							<a href="<?php echo Product::getLink($data->id, null, $data->category_id); ?>">
								<?php echo CHtml::image('/' . $data->cover->getPreviewName(Product::$preview['resize_200'])); ?>
							</a>
						</div>
						<div class="item_descript">
							<?php echo CHtml::link(Amputate::getLimb($data->name, 60, ' ...'), Product::getLink($data->id, null, $data->category_id), array('class' => 'item_name', 'data-title' => $data->name)); ?>

							<?php
							// ---- Ц Е Н Ы  товара ----
							$price = StorePrice::getPriceOffer($data->id);

							if ($price['min'] == 0.0 && $price['mid'] == 0.0) {
								echo CHtml::tag('span', array('class' => 'price not_specified'), 'Цена не указана');
							} elseif ($price['min'] == 0.0 && $price['mid'] > 0) {
								echo CHtml::tag('span', array('class' => 'price'), number_format($price['mid'], 0, '.', ' ') . ' руб.');
							} elseif ($price['min'] > 0 && $price['mid'] > 0) {
								$priceLowest = CHtml::tag('span', array('class' => 'lowest'), 'от ' . number_format($price['min'], 0, '.', ' ') . " руб.");
								echo CHtml::tag('span', array('class' => 'price'), number_format($price['mid'], 0, '.', ' ') . ' руб.' . $priceLowest);
							}
							?>

							<?php
							// Производитель (полное название)
							$manufacturer = '';
							$manufacturer .= isset($data->vendor) ? $data->vendor->name : '';
							$manufacturer .= isset($data->countryObj) ? ', ' . $data->countryObj->name : '';

							// Производитель (обрезанное для сокращенной карточки)
							$manAmp = mb_strlen($manufacturer, 'UTF-8') > 30
								? Amputate::getLimb($manufacturer, 30)
								: $manufacturer;
							?>
							<span class="manufacturer" data-title="<?php echo $manufacturer;?>"><?php echo $manAmp;?></span>
						</div>
						<?php //$this->widget('application.components.widgets.WStar', array('selectedStar' => $data->average_rating,));?>
						<?php echo CHtml::tag('p', array('class' => 'desc_text'), Amputate::getLimb($data->desc, 200)); ?>
					</div>

					<?php $this->endCache(); endif; ?>


					<?php // Подключаем виджет для добавления в избранное
					$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
						'modelId'   => $data->id,
						'modelName' => get_class($data),
						'cssClass'  => 'catalog',
						'deleteItem' => true
					));?>
				</div>
				<?php endforeach; ?>

				<div class="clear"></div>
			</div>
		</div>
	</div>
<?php endif; ?>