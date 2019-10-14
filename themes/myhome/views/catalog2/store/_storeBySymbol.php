<?php
/**
 * @var $stores Store[]
 */
?>

<?php foreach ($stores as $store) : ?>

	<div class="-col-3 -hidden">
		<a class="-nodecor -semibold -large" href="<?php echo $store->getLink($store->id);?>"><?php echo $store->name;?></a>
		<span class="-small -gray -block">
			<?php
			if ($store->isChain == 1) {
				echo CHtml::tag(
					'span',
					array(
						'class' => '-acronym address-list',
						'data-id' => $store->chainId
					),
					CFormatterEx::formatNumeral(
						$store->chainQt,
						array('адрес', 'адреса', 'адресов')
					)
				);
			} else {
				echo $store->city->name.', '.$store->address;
			}
			?>
		</span>
	</div>

<?php endforeach; ?>