<div class="-grid city-stores -hidden">
	<h1 class="-block -inset-left-hf -inset-top -inset-right-hf">Магазины в <span class="current-city-name"><?php echo $store->city->prepositionalCase ?></span>

		<?php if($countSorted > 1) : ?>
		<a class="-icon-location-s -gutter-left -red -medium -normal toggle-city-list"
										    href="javascript:void(0)">Другой
													      город</a>
		<?php endif; ?>
	</h1>

		<div class="-col-4 addresses">
			<?php $this->renderPartial('_storesInCityFull', ['model'=>$model, 'stores'=>$stores]); ?>
		</div>

		<div class="-col-8">
			<div class="map" id=mapPopup></div>
		</div>

</div>