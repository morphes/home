<?php
/** @var $data Store[] */
$data = $storeProvider->getData();

foreach ($data as $store) {
	// Выводим результаты поиска
	?>
	<div class="-col-3">
		<?php if ($store->isChain == 1) : ?>
			<span class="-large -semibold" href="<?php echo $store->getLink($store->id);?>"><?php echo $store->name;?></span>
		<?php else: ?>
			<a class="-nodecor -semibold -large" href="<?php echo $store->getLink($store->id);?>"><?php echo $store->name;?></a>
		<?php endif; ?>

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
	<?php
}

if (empty($data)) {
	// Сообщение о том, что ничего не найдено.
	?>
	<div class="-col-wrap -gutter-left-qr">
		<h2 class="-gutter-top-null">По вашему запросу ничего не найдено.</h2>
	</div>
	<?php
}
?>

<?php
if ($storeProvider->pagination->currentPage < $storeProvider->pagination->pageCount - 1) {
	echo CHtml::hiddenField(
		'next_page_url',
		$storeProvider->pagination->createPageUrl($this, $storeProvider->pagination->currentPage + 1)
	);
} else {
	echo CHtml::hiddenField('next_page_url', 0);
}