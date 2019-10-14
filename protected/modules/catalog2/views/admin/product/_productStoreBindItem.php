<?php
/**
 * @var $data Store
 * @var $price StorePrice
 */
$price = $data->getProductPrice($pid); ?>
<div class="row product-dialog-store-item" style="margin-bottom: 15px;">

	<div class="span1">
		<?php echo CHtml::link('#' . $data->id, $this->createUrl('/catalog2/admin/store/update', array('id'=>$data->id)), array('target'=>'_blank')); ?>
	</div>

	<div class="span7">
		<?php echo CHtml::link($data->getFullName(), Store::getLink($data->id), array('target'=>'_blank')); ?>
	</div>

	<div class="span5">
		<?php echo CHtml::textField('url', isset($price->url) ? Amputate::absoluteUrl($price->url) : '', array('placeholder'=>'URL товара', 'class'=>'span5')); ?>
	</div>

	<div class="span2" style="width: 120px;">
		<div class="input-append">
			<?php echo CHtml::textField('price', isset($price->price) ? round($price->price) : '', array('placeholder'=>'цена', 'class'=>'input-price', 'style'=>'width:75px')); ?>
			<span class="add-on">rur</span>
		</div>
	</div>

	<div class="span1">
		<?php $url = $this->createUrl('/catalog2/admin/product/ajaxProductStoreBindPrice', array('store_id'=>$data->id, 'product_id'=>$pid)); ?>
		<?php echo CHtml::button('сохранить', array('class'=>'btn small price-dialog-save-price', 'data-url'=>$url)); ?>
		<?php echo CHtml::image('/img/load.gif', 'loader', array('style'=>'display:none;'))?>
	</div>
</div>