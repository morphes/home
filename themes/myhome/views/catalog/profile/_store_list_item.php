<?php
/**
 * @var $data Store
 * @var $this FrontController
 */
?>
<div class="item">
	<div class="name">
		<?php echo CHtml::link($data->address, $this->createUrl('/catalog/store/index/', array('id' => $data->id)), array('class' => 'item_head')); ?>
		<p><?php echo $data->name; ?></p>
	</div>
	<div class="products_quant">
		<?php echo CHtml::link(CFormatterEx::formatNumeral($data->prod_qt, array('товар', 'товара', 'товаров')), $this->createUrl('storeProductList', array('store_id' => $data->id, 'onlyStoreProducts' => 1))); ?>
	</div>
	<div class="bind_product product_action">
        <span>
                <?php
		echo CHtml::link(
			'Прикрепить товары',
			$this->createUrl('storeProductList', array('store_id' => $data->id, 'onlyStoreProducts' => 0)),
			array('class' => '-icon-plus -icon-skyblue')
		);
		?>
        </span>
	</div>
	<div class="copy_product product_action"
	     title="Копировать товары">
        <span>
                <i></i>
		<?php echo CHtml::link('Копировать товары', $this->createUrl('productCopy', array('from_store' => $data->id))); ?>
        </span>
	</div>
	<div class="actions">
		<?php if($data->type !=  Store::TYPE_ONLINE) { ?>
			<?php echo CHtml::openTag('a', array('href' => $this->createUrl('storeUpdate', array('id' => $data->id)), 'title' => 'редактировать магазин')); ?>
			<i class="edit"></i><?php echo CHtml::closeTag('a'); ?>
			<!--<a href="#"><i class="deactivate"></i></a>-->
			<?php echo CHtml::openTag('a', array('href' => '#', 'title' => 'удалить магазин')); ?>
			<i class="del"
		   		data-store-id="<?php echo $data->id; ?>"></i><?php echo CHtml::closeTag('a'); ?>
		<?php } ?>

		<?php if ($data->tariff_id != Store::TARIF_FREE) { ?>
		<a class="-icon-stat -icon-skyblue -icon-only" href="<?php echo $this->createUrl('storeStat', array('id' => $data->id));?>"
		   title="Статистика"></a>
		<?php } ?>
	</div>
	<div class="clear"></div>
</div>