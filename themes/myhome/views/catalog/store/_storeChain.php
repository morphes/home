<?php
/**
 * @var $chain Chain Сеть магазинов
 */
?>

<div class="popup-header">
	<div class="popup-header-wrapper">
		<span class="-giant"><?php echo $chain->name;?> </span>
		<span class="-gray -medium"><?php echo CFormatterEx::formatNumeral(
				count($storeIds),
				array('адрес', 'адреса', 'адресов')
			);?></span>
	</div>
</div>
<div class="popup-body">
	<div class="list-inner -scroll-content">
		<div class="scrollbar">
			<div class="track">
				<div class="thumb"></div>
			</div>
		</div>
		<div class="viewport">
			<div class="overview">
				<div class="-grid">

					<?php
					$lastCityId = 0;
					foreach ($storeIds as $item) {
						/** @var $store Store */
						$store = Store::model()->findByPk($item['id']);
						if (!$store) {
							continue;
						}

						$city = $store->getCity();

						if ($city!==null && $city->id != $lastCityId) {

							/* Если выводим не первый город,
							   то нужно закрыть предыдущий список */
							if ($lastCityId > 0) {
								echo '	</ul>';
								echo '</div>';
							}

							// Название города
							echo CHtml::tag(
								'div',
								array('class' => '-col-3 -huge -semibold'),
								$city->name
							);

							// Начинаем список магазинов для города
							echo '<div class="-col-4">';
							echo '	<ul class="-menu-block -large">';
						}

						// Закрываем список магазинов для города
						echo '<li>';
						echo CHtml::link($store->address, $store->getLink($store->id));
						echo '</li>';

						$lastCityId = $city->id;
					}

					echo '	</ul>';
					echo '</div>';

					?>
				</div>
			</div>
		</div>
	</div>
</div>