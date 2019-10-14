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
	<?php if($store->type == Store::TYPE_ONLINE) { ?>
	    <div class="url">
		  <?php echo CHtml::textField('url', $data->url, array('class'=>'textInput'));?>
	    </div>
	<?php } ?>
    <!--<div class="available">
        <select name="" class="textInput">
            <option value="1">В наличии</option>
            <option value="2">Под заказ</option>
        </select>
    </div>-->
    <?php echo CHtml::hiddenField('product_id', $data->id); ?>
    <div class="price">

        <?php echo CHtml::dropDownList('price_type', $data->price_type ? $data->price_type : StorePrice::PRICE_TYPE_MORE, StorePrice::$price_types, array('class'=>'textInput')); ?>

        <div class="adding_product_price">
            <?php if($data->price) $price = round($data->price); else $price = ''; ?>
            <?php echo CHtml::textField('price', $price, array('class'=>'textInput')); ?>
            <span class="currency">руб.</span>
        </div>
    </div>
    <div class="bind">
        <?php echo CHtml::checkBox('binded', !empty($data->price) ? true : false); ?>
    </div>
    <div class="clear"></div>
</div>