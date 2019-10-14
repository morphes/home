<h2><?php echo $model->name ?></h2>
<div class="discount-scroll">
	<div class="list-inner">
		<div class="scrollbar">
			<div class="track">
				<div class="thumb">
					<div class="end"></div>
				</div>
			</div>
		</div>
		<div class="viewport">
			<div class="overview">
				<form  class="form">

					<?php foreach($stores as $store) : ?>
						<?php
						// Цена товара в магазине
						/** @var $storePrice StorePrice */
						$storePrice = StorePrice::model()->findByAttributes(array(
							'store_id'   => $store->id,
							'product_id' => $model->id
						));

						$folderDiscount = CatFolderDiscount::model()->findByAttributes(array(
							'store_id' => $store->id,
							'model_id' => $model->id,
						));
						$dateStartValue = '';
						$dateEndValue = '';
						if($folderDiscount) {

							if($folderDiscount->date_start) {
								$dateStartValue = date('d.m.Y',$folderDiscount->date_start);
									;
							}

							if($folderDiscount->date_end) {
								$dateEndValue = date('d.m.Y',$folderDiscount->date_end);
							}
						}

						?>

						<div class="-tinygray-bg -col-wrap -border-all -border-radius-all -inset-all -gutter-bottom">
							<span class="-huge -strong"><?php echo $store->name ?></span>
							<div class="-col-wrap -push-right">
								<span class="discount"><span><?php echo number_format($storePrice->price, 0, '.', ' ') ?></span> руб.</span>
								<span class="-strong -large -gutter-left"> <?php

									if(!empty($dateStartValue)) {
										echo $storePrice->getNumberDiscount($folderDiscount->discount);
									} else {
										echo $storePrice->getNumberDiscount();
									} ?> руб.</span>
							</div>

							<div class="discount-period">
								<strong class="-gutter-bottom">Период действия</strong>
								<input type="hidden" name="">
								<input value="<?php echo $dateStartValue; ?>" class="first-day" type="text" name="<?php echo $store->id ?>[firstDay]"><i class="-icon-calendar -icon-gray -icon-only toggle-calendar"></i>
								<span> — </span>
								<input type="hidden" name="">
								<input value="<?php echo $dateEndValue; ?>" class="last-day" type="text" name="<?php echo $store->id ?>[lastDay]"><i class="-icon-calendar -icon-gray -icon-only toggle-calendar"></i>
							</div>
							<div class="discount-type">
								<div>
									<strong class="-gutter-bottom">Процент скидки или новая цена</strong>
										<span class="-block -gutter-bottom">
<!--											<input type="radio" name="--><?php //echo $store->id ?><!--[discount-type]" />-->
											<input class="discount" value="<?php
											if(!empty($dateStartValue)) {
												echo round($folderDiscount->discount);
											} else {
												echo round($storePrice->discount);
											}?>"
											       type="text" name="<?php echo $store->id ?>[discPercent]"/>
											<span>%</span>
										</span>
								</div>
								<div>
										<span class="-block">
<!--											<input type="radio" name="--><?php //echo $store->id ?><!--[discount-type]">-->
											<input class="suggested_value" value="<?php
											if(!empty($dateStartValue)) {
												echo $storePrice->getNumberDiscount($folderDiscount->discount);
											} else {
												echo $storePrice->getNumberDiscount();
											}


											?>" type="text" name="<?php echo $store->id ?>[discNumber]">
											<span class="-hidden"><?php echo $storePrice->price; ?></span>
											<span>руб.</span>

										</span>

									<input type="hidden" value="<?php echo $model->id?>" name="<?php echo $store->id ?>[modelId]">
								</div>
							</div>
						</div>
					<?php endforeach; ?>

				</form>
				<button class="-button -button-skyblue">Применить</button>
			</div>
		</div>
	</div>
</div>