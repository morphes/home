<?php
/*
 * HTML код для строки в списке товаров в окне связки товаров и фото идей.
 */
?>

<li class="prod_item" data-product_id="<?php echo $product->id;?>">
	<a target="_blank" href="<?php echo $product->getLink($product->id);?>" ><?php echo $product->name;?></a>
	<a href="#" class="del_cross">[x]</a>
</li>