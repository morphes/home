<?php
echo CHtml::link($store->name, $this->createUrl('/catalog/store/index', array('id' => $store->id)));

if (isset($mall) && $mall instanceof MallBuild) {
	$popupData = $store->getMallData();

	echo CHtml::link($mall->name, Yii::app()->params->bmHomeUrl.'/about', array('class' => '-tvk'));
	echo '<a href="#scheme'.$store->id.'" id="toggleScheme'.$store->id.'" class="-acronym">Показать на схеме ТВК</a>';
	?>
	<div style="display:none">
		<div id="scheme<?php echo $store->id;?>">
			<div class="scheme-left">
				<h2><?php echo $store->name;?></h2>
				<div class="scheme-bage"><span class="-large -darkgray -gutter-null">Этаж</span><span class="-giant -gutter-null"><?php echo $popupData['floor_name'];?></span></div>
				<div class="scheme-bage"><span class="-large -darkgray -gutter-null">Секция</span><span class="-giant -gutter-null"><?php echo $popupData['sect_name'];?></span></div>
			</div>
			<div class="scheme-right" style="background: url('<?php echo '/'.$popupData['floor_img']->getPreviewName(MallFloor::$preview['resize_0x520']);?>') 0 20px no-repeat;"></div>
		</div>
	</div>
	<script>
		$(document).ready(function() {
			$('#toggleScheme<?php echo $store->id;?>').fancybox();
		});
	</script>
	<?php
}

echo '<span>г.'.$store->city->name.'<br/>'.$store->address.'</span>';

// Цена товара в магазине
/** @var $storePrice StorePrice */
$storePrice = StorePrice::model()->findByAttributes(array(
	'store_id' => $store->id,
	'product_id' => $product_id
));

if ($storePrice && $storePrice->price > 0) {
	$ot = ($storePrice->price_type == $storePrice::PRICE_TYPE_MORE)
		? 'от '
		: '';
	echo '<p><strong>'.$ot.number_format($storePrice->price, 0, '.', ' ').' руб.</strong></p>';
} else {
	echo '<p>цена не указана</p>';
}