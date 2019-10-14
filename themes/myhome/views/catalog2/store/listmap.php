<?php
$this->pageTitle = 'Магазины — MyHome.ru';

$cs = Yii::app()->clientScript;
$cs->registerCssFile('/css-new/generated/goods.css');
$cs->registerScriptFile('/js-new/CCatalog2.js');
$cs->registerScriptFile('/js-new/jquery.simplemodal.1.4.4.min.js');
$cs->registerScriptFile('/js-new/scroll.js');


/* -----------------------------------------------------------------------------
 *  SEO — оптимизация
 * -----------------------------------------------------------------------------
 */
if (!empty($seoOptimize['title'])) {
	$this->pageTitle = $seoOptimize['title'];
}
if (!empty($seoOptimize['description'])) {
	$this->description = $seoOptimize['description'];
}
if (!empty($seoOptimize['keywords'])) {
	$this->keywords = $seoOptimize['keywords'];
}


?>

<?php
$cityPopup = $this->widget('catalog.components.widgets.CityPopup.CityPopupWidget', array(
	'city'       => $city,
	'cityUrlPos' => 3
));
?>

<div class="-grid-wrapper page-title">
	<div class="-grid">
		<div class="-col-12">
			<ul class="-menu-inline -breadcrumbs">
				<li><a>Главная</a></li>
			</ul>
		</div>
		<div class="-col-8"><h1><?php echo !empty($seoOptimize['h1']) ? $seoOptimize['h1'] : 'Магазины';?></h1></div>
		<div class="-col-4 -text-align-right">
			<?php echo $cityPopup->getHtml();?>
		</div>
		<hr class="-col-12">
	</div>
</div>
<!-- EOF Page title widget //-->

<!-- Page content wrap //-->
<div class="-grid-wrapper page-content">
<div class="-grid">
<div class="-col-3 -tinygray-box page-sidebar">
	<ul class="-menu-block -small">
		<?php
		$htmlOut = '';
		foreach ($categories as $cat) {

			$clsSelect = ($cat->id == $cid) ? 'selected' : '';

			$url = Store::getLinkList(array('map' => 'show', 'city' => $city, 'cid' => $cat->id));


			switch ($cat->level) {
				case 2:
					$cls = $clsSelect ? '-nodecor' : '';
					$htmlOut .= '<li class="-large -strong -gutter-top level-1 ' . $clsSelect . '">'
						.'<a href="'.$url.'" class="'.$cls.'">'.$cat->name.'</a></li>';
					break;
				case 3:
					$cls = $clsSelect ? '-icon-arrow-down -icon-pull-right -nodecor' : '-gray';
					$htmlOut .= '<li class="level-2 ' . $clsSelect . '">'
						. '<a href="'.$url.'" class="'.$cls.'">'.$cat->name.'</a></li>';
					break;
				default:
					$cls = $clsSelect ? '-nodecor' : '';
					$htmlOut .= '<li class="-gutter-left-hf -hidden level-3 ' . $clsSelect . ' ">'
						.'<a href="'.$url.'" class="'.$cls.'">'.$cat->name.'</a></li>';
					break;
			}
		}

		echo $htmlOut;
		?>
		<script>
			// Проставляем корректные классы для дерева каталога.
			cat.showListSubCategory();
		</script>
		
	</ul>
</div>


<div class="-col-9 -gutter-bottom stores-list">
<div class="-grid">
<div class="-relative">
	<input type="text"
	       class="-col-9"
	       data-city-id="<?php echo ($city instanceof City) ? $city->id : 0;?>"
	       placeholder="Название или адрес магазина">
	<button type="submit"
		class="search-submit">
		<i class="-icon-search-s -icon-only -icon-gray"></i></button>

	<input type="hidden" id='f_city_id' value="<?php echo ($city instanceof City) ? $city->id : 0;?>">
	<input type="hidden" id='f_category_id' value="<?php echo $cid;?>">
</div>

<div class="lists-block -relative">

	<div class="-col-9 -gutter-bottom-dbl">
		<hr>

		<div class="-grid">
			<div class="-col-4">
				<div class="-gutter-bottom-dbl">
					<a href="<?php echo Store::getLinkList(array('city' => $city, 'cid' => $cid));?>" class="-icon-list-s -icon-gray">Списком</a>
					<span class="-disabled -icon-map-s -gutter-left">На карте</span>
				</div>
			</div>
			<div class="-col-5 -text-align-right ">
				<?php if ($cid > 0) { ?>
					<span class="-gray">Выбрана категория &laquo;<?php if (($cat = Category::model()->findByPk($cid))) echo $cat->name;?>&raquo;<a href="<?php echo Store::getLinkList(array('city' => $city, 'map' => 'show'));?>" data-tooltip="-tooltip-bottom-center" data-title="Отменить выбор" class="-icon-cross -icon-pull-right"></a></span>
				<?php } ?>

			</div>
		</div>

		<div class="-grid">
			<div class="-col-9">
				<?php // Яндекс карта ?>

				<script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>

				<div id="mapShops" style="width:100%; height:500px"></div>
			</div>
		</div>

	</div>

</div>

</div>
<!--Попап со списком магазинов-->
<div class="-hidden">
	<div class="popup popup-stores-list" id="popup-stores-list">

		<?php // Сюда вставлются данные по магазинам сети из AJAX ?>
	</div>
</div>
</div>

<?php
// Получение геокоординат города
$geoCity = '';
if ($city instanceof City) {
	$geo = unserialize(YandexMap::getGeocode('Россия '.$city->name));
	if (isset($geo[0]) && isset($geo[1])) {
		$geoCity = ', [' . $geo[1] . ',' . $geo[0] . ']';
	}
}
?>

<script>
	cat.initShopMap();

	ymaps.ready(function(){
		cat.renderMap(
			<?php echo CJavaScript::encode($shops);?>
			<?php echo $geoCity;?>
		);
	});

</script>


</div>
</div>