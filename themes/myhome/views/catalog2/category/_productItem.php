<div class="item">
        <?php $cacheKey = isset($store_id) ?  "store{$store_id}ProductItemId{$data->id}" : "productItemId{$data->id}"; ?>

        <?php if($this->beginCache($cacheKey, array('duration'=>3600))) : ?>

                    <?php if (!isset($store_id)) $store_id = null;?>
                    <div class="item_inner">
                        <div class="item_photo">
                            <a href="<?php echo Product::getLink($data->id, $store_id, $data->category_id); ?>">
                                <?php echo CHtml::image('/'.$data->cover->getPreviewName(Product::$preview['resize_200'])); ?>
                            </a>
                        </div>
                        <div class="item_descript">
                            	<?php echo CHtml::link(Amputate::getLimb($data->name, 60, ' ...'), Product::getLink($data->id, $store_id, $data->category_id), array('class'=>'item_name', 'data-title' => $data->name)); ?>

				<?php
				// ---- Ц Е Н Ы  товара ----
				if ($store_id) {
					// Если смотрим карточки товара от конкретного магазина.


					/** @var $storePrice StorePrice */
					$storePrice = StorePrice::model()->findByAttributes(array(
						'store_id' => $store_id,
						'product_id' => $data->id
					));

					if ($storePrice && $storePrice->price > 0) {

						$ot = ($storePrice->price_type == StorePrice::PRICE_TYPE_MORE)
							? 'от '
							: '';
						echo CHtml::tag('span', array('class' => 'price'), $ot . number_format($storePrice->price, 0, '.', ' ').' руб.');

					} else {

						echo CHtml::tag('span', array('class' => 'price not_specified'), 'Цена не указана');

					}

				} else {

					$price = StorePrice::getPriceOffer($data->id);

					if ($price['min'] == 0.0 && $price['mid'] == 0.0)
					{
						echo CHtml::tag('span', array('class' => 'price not_specified'), 'Цена не указана');
					}
					elseif ($price['min'] == 0.0 && $price['mid'] > 0)
					{
						echo CHtml::tag('span', array('class' => 'price'), number_format($price['mid'], 0, '.', ' ').' руб.');
					}
					elseif ($price['min'] > 0 && $price['mid'] > 0) {
						$priceLowest = CHtml::tag('span', array('class' => 'lowest'), 'от '.number_format($price['min'], 0, '.', ' ')." руб.");
						echo CHtml::tag('span', array('class' => 'price'), number_format($price['mid'], 0, '.', ' ').' руб.'.$priceLowest);
					}

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
                        <?php echo CHtml::tag('p', array('class'=>'desc_text'), Amputate::getLimb($data->desc, 200)); ?>
                    </div>

        <?php $this->endCache(); endif; ?>

        <?php // Подключаем виджет для добавления в избранное
        $this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
                'modelId'   => $data->id,
                'modelName' => get_class($data),
                'cssClass'  => 'catalog',
        ));?>
</div>
