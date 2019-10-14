<?php
/**
 * @var $data Tapestore
 */
$image = $data->getImage();
$store = $data->getStore();
if ( $image===null || $store===null )
	return;
?>

<div class="-col-wrap">
	<a href="<?php echo Store::getLink($data->store_id); ?>"
	   title="<?php echo $store->name; ?>">
		<?php echo CHtml::image(
			'/' . $image->getPreviewName(Tapestore::$preview['resize_148x68']),
			$store->name,
			array('max-width' => 148, 'max-height' => 68)
		);?>
	</a>
</div>