<?php $class = $data->price ? 'binded' : ''; ?>

<div class="item <?php echo $class; ?>">
    <div class="photo">
        <?php if ($data->cover) : ?>
                <a href="<?php echo Product::getLink($data->id, null, $data->category_id); ?>">
                        <?php echo CHtml::image('/'.$data->cover->getPreviewName(Product::$preview['crop_60']), '', array('width'=>60)); ?>
                </a>
        <?php endif; ?>
    </div>
    <div class="name">
        <?php echo CHtml::link($data->name, Product::getLink($data->id, null, $data->category_id), array('class'=>'item_head')); ?>

        <p><?php echo $data->vendor? $data->vendor->name : ''; ?></p>
    </div>
    <div class="category">
            <?php echo $data->category->name; ?>
    </div>
    <!--<div class="available">
        <select name="" class="textInput">
            <option value="1">В наличии</option>
            <option value="2">Под заказ</option>
        </select>
    </div>-->
    <?php echo CHtml::hiddenField('product_id', $data->id); ?>
	<div class="price for_mini_site">

		<div class="base">
			<div>
				<?php
				echo CHtml::checkBox(
					'price_type',
					$data->price_type == StorePrice::PRICE_TYPE_MORE,
					array(
						'value'             => StorePrice::PRICE_TYPE_MORE, // Значение по-умолчанию
						'data-value-second' => StorePrice::PRICE_TYPE_EQUALLY // Алтернативное значение
					)
				);
				?>
				<span>От</span>
			</div>
			<div>
				<?php $price = ($data->price) ? round($data->price) : ''; ?>
				<?php echo CHtml::textField('price', $price, array('class' => 'textInput price_value')); ?>
				<span>руб.</span>
			</div>
		</div>
		<div class="discount">
			<?php if ($data->discount) { ?>
				<div>
					<span class="percent on"><?php echo round($data->discount);?>%</span>
					<input type="text" class="textInput percent_value" value="<?php echo $data->discount;?>" name="discount" maxlength="7">
				</div>
				<div>
					<?php $newPrice = round($data->price * (1 - ($data->discount / 100)) );?>
					<span class="suggested on"><?php echo $newPrice;?> руб.</span>
					<input type="text" name="suggested" class="textInput suggested_value" value="<?php echo $newPrice;?>">
				</div>

			<?php } else { ?>

				<div>
					<span class="percent">% скидки</span>
					<input type="text" class="textInput percent_value" value="" name="discount" maxlength="7">
				</div>
				<div>
					<span class="suggested">Цена со скидкой</span><input type="text" name="suggested" class="textInput suggested_value" value="">
				</div>
			<?php } ?>
		</div>
	</div>
    <div class="bind">
        <?php echo CHtml::checkBox('binded', !empty($data->price) ? true : false); ?>
    </div>
    <div class="clear"></div>
</div>