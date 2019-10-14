<?php
/**
 * @var $unitProduct MainUnit
 * @var $origin Product
 */
$image = $unitProduct->getImage();
$origin = $unitProduct->getOrigin();
if ( $image===null || $origin===null )
	return;

$price = $origin->getStorePrice($unitProduct->store_id);
?>
<div class="-col-wrap">
	<a href="<?php echo Product::getLink($origin->id, $unitProduct->store_id, $origin->category_id); ?>">
		<?php echo CHtml::image('/'.$image->getPreviewName(MainUnit::$preview['crop_234x180']), $unitProduct->name, array('width'=>234, 'height'=>180) ); ?>
	</a>
	<div>
		<a href="<?php echo Product::getLink($origin->id, $unitProduct->store_id, $origin->category_id); ?>" class="name"><?php echo $unitProduct->name; ?></a>
		<span class="price"><?php
			if ($price===null || empty($price['price']) )
				echo 'цена не указана';
			else
				echo number_format($price['price'], 0, '.', ' ').' руб.'
		?></span>
	</div>
</div>