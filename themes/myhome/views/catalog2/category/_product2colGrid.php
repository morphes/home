<?php
/**
 * @var $data Product
 */
?>

	<?php
	/* -----------------------------------------------------------------------------
	 *  Вычисляем и формируем цену товара
	 * -----------------------------------------------------------------------------
	 */
	// Html блок с ценой товара. формируется на несколько разных случаев
	$priceHtml = '';
	// Класс для блока товара, в случае если он со скидкой.
	$discountClass = '';

	if (isset($store_id)) {

		$price = StorePrice::model()->findByAttributes(array(
			'store_id'   => $store_id,
			'product_id' => $data->id
		));
		if ($price) {

			if ($price->discount > 0) {

				$priceHtml = CHtml::tag(
					'span',
					array('class' => '-strong'),
					number_format(($price->price * (100 - $price->discount) / 100), 0, '.', ' ') . ' руб.'
				);
				$priceHtml .= CHtml::tag(
					'span',
					array('class' => '-small -gray -inset-left-hf'),
					number_format($price->price, 0, '.', ' ') . ' руб.'
				);

				$discountClass = 'discount';
			} else {

				if ($price->price > 0) {

					$priceHtml = CHtml::tag('span', array('class' => '-strong'), number_format($price->price, 0, '.', ' ') . ' руб.');
				} else {

					$priceHtml = CHtml::tag('span', array('class' => '-gray'), 'Цена не указана');
				}
			}
		}
	} else {

		// ---- Ц Е Н Ы  товара ----
		$price = StorePrice::getPriceOffer($data->id);

		if ($price['min'] == 0.0 && $price['mid'] == 0.0) {

			$priceHtml = CHtml::tag('span', array('class' => '-gray'), 'Цена не указана');
		} elseif ($price['min'] == 0.0 && $price['mid'] > 0) {

			$priceHtml = CHtml::tag('span', array('class' => '-strong'), number_format($price['mid'], 0, '.', ' ') . ' руб.');
		} elseif ($price['min'] > 0 && $price['mid'] > 0) {

			$priceHtml = CHtml::tag(
				'span',
				array('class' => '-strong'),
				number_format($price['mid'], 0, '.', ' ') . ' руб.'
			);
			$priceHtml .= CHtml::tag(
				'span',
				array('class' => '-small -gray -inset-left-hf'),
				'от ' . number_format($price['min'], 0, '.', ' ') . ' руб.'
			);
		}
	}
	?>

	<?php
	// начало тизера (который вставляется в середину списка)
	if ( $index == $center) {
		echo "<div class='-col-9'>";
		//$this->renderPartial('//widget/yandex/direct_product_3_ad');
		echo '</div><div></div>';
	}
	// конец тизера
	?>

	<div class="<?php echo $class; ?> <?php echo $discountClass; ?>">
		<noindex>
		<a href="<?php echo Product::getLink($data->id, isset($store_id)
			? $store_id : null, $data->category_id); ?>">

			<?php if ($data->category->image_format == Category::IMAGE_CROP) :  ?>
				<?php echo CHtml::image('/' . $data->cover->getPreviewName(Product::$preview['crop_338']),$data->name,array('width' => 338, 'height' => 338)); ?>
			<?php else : ?>
				<?php echo CHtml::image('/' . $data->cover->getPreviewName(Product::$preview['resize_338']),$data->name,array('width' => 338, 'height' => 338)); ?>
			<?php endif; ?>
		</a>
		</noindex>

		<div class="-inset-all-hf">
			<?php echo CHtml::link(
				Amputate::getLimb($data->name, 60, ' ...'),
				Product::getLink($data->id, null, $data->category_id),
				array(
					'class' => '-block -gutter-bottom-hf',
					'title' => $data->name
				)
			); ?>

			<?php
			// --- Выводим цену ---
			echo $priceHtml;
			?>

			<?php //Подключаем виджет для добавления товара в папку.
			// Если пользователь этого достоин
			if (isset($addToFolder) and $addToFolder) {
				$this->widget('catalog.components.widgets.AddToFolder.AddToFolder', array(
					'modelId' => $data->id,
				));
			}

			$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
				'modelId'   => $data->id,
				'modelName' => get_class($data),
				'cssClass'  => 'catalog',
			));?>
		</div>
	</div>
