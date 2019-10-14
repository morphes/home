<div class="-col-3">
	<a class="-block"
	   href="<?php echo Product::model()->getLink($product->id) ?>" onclick = "_gaq.push(['_trackEvent','interest','click']);return true;">
		<?php echo CHtml::image('/' . $product->cover->getPreviewName(Product::$preview['crop_220x175']), '',
			array('class' => '-rect-220-175')); ?>

		<span><?php echo $product->name ?></span>
	</a>
	<?php

	$price = $product->average_price;

	if ($price) {
		echo CHtml::tag('span', array('class' => '-gray -small'), number_format($price, 0, '.', ' ') . ' руб.');
	} else {
		echo CHtml::tag('span', array('class' => '-gray'), 'Цена не указана');
	}
	?>
</div>