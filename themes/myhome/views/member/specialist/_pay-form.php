<h2>Поднять свой профиль</h2>
<div class="-tinygray-bg user-block">
	<?php echo CHtml::image( '/'.$user->getPreview(User::$preview['crop_30']),'', array('class'=>'-inline -col-wrap -gutter-right')); ?>
	<span class=" -col-wrap "><?php echo $user->name; ?></span>

	<?php echo CHtml::link('',
		$user->getLinkProfile(),
		array(
			'class'=>'-icon-toggle-blank -push-right',
			'target'=>'_blank'

		)
	); ?>
</div>


<form class="-form-inline -large">
	<div class="service">
		<strong>Выберите город и услугу</strong>
		<?php $this->widget('application.components.widgets.EAutoComplete', array(
		'sourceUrl'	=> '/utility/autocompleteusercity/userId/'.$user->id,
		'valueName' =>  isset($city->name)
			? $city->name
			: '',
		'value' => isset($city->id)
			? $city->id
			: '',
		'options'	=> array(
			'showAnim'	=>'fold',
			'open' => 'js:function(){}',
			'minLength' => 3,
			'select'=>'js:function(event, ui) {
				$("#city_id").val(ui.item.id).keyup().trigger("changeCity", $("#val_city_id").val());
			}',
		),
		'htmlOptions'	=> array('id'=>'city_id', 'name'=>'city', 'class' => 'span6 selectedCity', 'value'=>$city->name),
		'cssFile' => null,
		));?>
		<input type="hidden" name='userId' value="<?php echo $user->id; ?>">

		<div class="dropdown services -col-wrap -icon-arrow-down">
			<span class=""><?php echo $service->name; ?></span>
			<input type='hidden' id='servId' name="service" value="<?php echo $service->id ?>"/>
			<div class="-hidden">
				<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
				<div class="viewport">
					<div class="overview topics_list">
						<ul class="-menu-block">
							<?php foreach($serviceList as $sl) { ?>
								<li data-id="<?php echo $sl['service_id'] ?>"><a><?php echo $sl['service_name'] ?></a></li>
							<?php } ?>
						</ul>
					</div>
				</div>

			</div>
		</div>

	</div>
	<div class="loaded-content">
		<div class="period">
			<strong>Срок размещения</strong>
			<div class="">
				<input type="hidden" name="date" value="3">
				<div class="-col-wrap -text-align-center current" data-select="period" data-value="3">

					<span class="-huge -semibold -acronym">3 дня</span>
					<?php if($rate->Rate->discount_3 > 0) {
						echo CHtml::tag('span', array('class' => '-block summ'), $rate->Rate->discount_3.' руб.');
						$finalPrice = $rate->Rate->discount_3;
						?>
						<span class="-gray -small"><s><?php echo $rate->Rate->packet_3?></s> руб.</span>
					<?php
					} else {
						echo CHtml:: tag('span', array('class' => '-block summ'), $rate->Rate->packet_3);
						$finalPrice = $rate->Rate->packet_3;
					}
					?>
					<i></i>
				</div>
				<div class="-col-wrap -text-align-center" data-select="period" data-value="7">
					<span class="-huge -semibold -acronym">7 дней</span>
					<?php if($rate->Rate->discount_7 > 0) {
						echo CHtml::tag('span', array('class' => '-block summ'), $rate->Rate->discount_7.' руб.');
						?>
						<span class="-gray -small"><s><?php echo $rate->Rate->packet_7?></s> руб.</span>
					<?php
					} else {
						echo CHtml:: tag('span', array('class' => '-block summ'), $rate->Rate->packet_7);
					}
					?>
					<i></i>
				</div>
				<div class="-col-wrap -text-align-center" data-select="period" data-value="14">
					<span class="-huge -semibold -acronym">14 дней</span>
					<?php if($rate->Rate->discount_14 > 0) {
						echo CHtml::tag('span', array('class' => '-block summ'), $rate->Rate->discount_14.' руб.');
						?>
						<span class="-gray -small"><s><?php echo $rate->Rate->packet_14?></s> руб.</span>
					<?php
					} else {
						echo CHtml:: tag('span', array('class' => '-block summ'), $rate->Rate->packet_14);
					}
					?>
					<i></i>
				</div>
			</div>
		</div>

		<strong>Дополнительно</strong>

		<label class="-checkbox"><input name='in_main' value='278' type="checkbox"> <span>Разместить на главной раздела «<a href="#">Специалисты</a>»  <span id='inMain'>+<?php echo round($finalPrice * 0.75)  ?> руб.</span></span> </label>


		<div class="-tinygray-bg -inset-all summary">
			<ul class="-menu-block -gutter-bottom-dbl">
				<li>
					<span class="-gray">Город</span>
					<span class="city"><?php echo $city->name; ?></span>
				</li>
				<li>
					<span class="-gray">Услуга</span>
					<span class="service"><?php echo $service->name ?></span>
				</li>
				<li>
					<span class="-gray">К оплате</span>
					<?php
					 if($rate->Rate->discount_3 > 0) {
						echo CHtml::tag('span', array('class' => 'summ'), $rate->Rate->discount_3.' руб.');
						?>
					<?php
					} else {
						echo CHtml:: tag('span', array('class' => 'summ'), $rate->Rate->packet_3);
					}
					?>
				</li>
			</ul>
			<!--<div class="-inset-all -gutter-top-dbl -gutter-bottom success">
				По данной услуге и городу вы находитесь на первой
				странице. Возможность приоретизации отключена
				за ненадобностью.
			</div>-->
			<input type="hidden" id="totalPrice" name="totalPrice" value="<?php

			if($rate->Rate->discount_3 > 0) {
				echo $rate->Rate->discount_3;
			} else {
				echo $rate->Rate->packet_3;
			}

			?>">
			<?php
			$options = array('service'=>$service->url);
			if ($city instanceof City) {
				$options['city'] = $city->eng_name;
			}
			$url = $this->createUrl('/member/specialist/list', $options);
			?>
			<p ><span class="-gray -gutter-top -small">Профиль поднимется на странице</span>
				<br>
				<a class="-gray -gutter-top -small" href="<?php echo $url; ?>" target="_blank"><?php
					echo 'www.myhome.ru'.$url;
					?></a>
				<br>
				<a id='linkInMain' class="-hidden -gray -gutter-top -small" href="/specialist/<?php echo $city->eng_name; ?>" target="_blank"><?php echo 'www.myhome.ru/specialist/'.$city->eng_name ?></a>
			</p>
			<input type="hidden" name="rateId" value='<?php echo $rate->Rate->id; ?>'>
			<button class="-button -button-skyblue" formaction="/pay/process" formmethod="post"
				formenctype="multipart/form-data">Оплатить</button>
			<p></p>
			<p ><a class="-gray -gutter-top -small" href="http://www.myhome.ru/pros/up" target="_blank">Подробнее об услуге</a></p>
		</div>
	</div>

</form>