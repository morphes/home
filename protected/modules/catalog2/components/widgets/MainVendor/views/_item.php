<?php
/**
 * @var $unitVendor MainUnit
 * @var $origin Product
 */
$image = $unitVendor->getImage();
$origin = $unitVendor->getOrigin();
if ( $image===null || $origin===null )
	return;
?>
<li><a href="<?php echo Vendor::getLink($unitVendor->origin_id); ?>"><img width="70" height="70" src="/<?php echo $image->getPreviewName(Vendor::$preview['resize_70']); ?>" alt="<?php $origin->name; ?>"></a></li>