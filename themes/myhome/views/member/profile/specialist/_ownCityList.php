<?php
/**
 * Список городов и регионов уже привязанных пользователю.
 */
?>

<ul>
	<?php foreach ($locations as $location) : ?>

		<li>
			<a class="city_in_list"><?php echo $location->getLocationLabel(); ?></a><span data-location-id="<?php echo $location->getLocationId(); ?>"
												      data-location-type="<?php echo $location->getLocationType(); ?>"></span>
		</li>

	<?php endforeach; ?>

	<li class="last_city"><a class="city_add_list"
				 href="#">Добавить город</a>

		<div class="c-hinter"
		     data-index="244500">
			<i></i>

			<p class="c-hinter-text">
				Укажите города, в которых вы готовы оказывать
				свои услуги. Выберите страну, затем регион и
				город, после чего нажмите на кнопку «Готово». Вы
				можете указать любое количество городов.
			</p>
		</div>
	</li>
</ul>
<input type="hidden"
       name=""
       data-location-id=""
       class=""
       value=""
       id="reg_1"/>
<div class="clear"></div>