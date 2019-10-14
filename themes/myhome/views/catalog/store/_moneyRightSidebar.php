<?php
/* -----------------------------------------------------------------------------
 *  Рейтинг и количество отзывов
 * -----------------------------------------------------------------------------
 */
?>


<?php if (isset($showAddNews) && $showAddNews == true) { ?>
	<div class="-tinygray-box">
		<span class="-button -button-skyblue"
		      id="addEvent"
		      data-storeId="<?php echo $store->id;?>"
			>Добавить публикацию</span>
	</div>
<?php } ?>


<?php if (isset($showAddPhoto) && $showAddPhoto == true) { ?>
	<div class="-tinygray-box">
		<span class="-button -button-skyblue" id="addPhoto">Добавить фото</span>
	</div>
<?php } ?>

<?php
/* -----------------------------------------------------------------------------
 *  Средний рейтинг.
 * -----------------------------------------------------------------------------
 */
?>
<div class="-gutter-top -gutter-bottom-dbl">
	<?php $this->widget('application.components.widgets.WStarGrid', array(
		'selectedStar' => $store->average_rating,
	));?>
	<?php $feedback_qt = $store->feedbackQt;?>
	<?php echo CHtml::link(
		($feedback_qt == 0)
			? 'Нет отзывов'
			: CFormatterEx::formatNumeral($feedback_qt, array('отзыв', 'отзыва', 'отзывов')),
		Store::getLink($store->id, 'moneyFeedback'),
		array('class' => '-gutter-left-hf -small -gray')
	); ?>
</div>


<?php $this->widget('ext.sharebox.EShareBox', array(
	'view'             => 'idea',
	// url to share, required.
	'url'              => Yii::app()->request->hostInfo . Yii::app()->request->requestUri,

	// A title to describe your link, required.
	'title'            => $store->name,

	// A small message for post
	'message'          => Amputate::getLimb($store->about, 500, '...'),
	'classDefinitions' => array(
		'facebook'  => '-icon-facebook',
		'vkontakte' => '-icon-vkontakte',
		'twitter'   => '-icon-twitter',
		'google+'   => '-icon-google-plus',
		'odkl'      => '-icon-odnoklassniki',
	),
	'exclude'          => array('pinterest', 'livejournal'),
	'htmlOptions'      => array('class' => '-gutter-bottom-dbl -gray'),
));?>

<?php
/* -----------------------------------------------------------------------------
 *  Контактные данные
 * -----------------------------------------------------------------------------
 */
?>
<div class="right-nav">

	<script type="text/javascript" src="http://api-maps.yandex.ru/2.0/?coordorder=longlat&load=package.full&wizard=constructor&lang=ru-RU"></script>

	<div class="map collapsed">

		<div id="map" style="width: 100%; height: 100%;"></div>

		<?php
		$lat = $store->getCoordinates('lat');
		$lng = $store->getCoordinates('lng');
		$hint = $store->name;
		$mapData = array($lat, $lng, $hint);
		?>
		<?php Yii::app()->clientScript->registerScript('yandexmap', '
			var map;
			var group;
			var typeSelector;

			function init()
			{
			    	map = new ymaps.Map ("map", {
					center: [55.76, 37.64],
				    	zoom: 7
				});
			    	var data = ' . CJavaScript::encode($mapData) . ';
			    	if (data[0] == "" || data[1] == "") {
					return;
				}

				typeSelector = new ymaps.control.TypeSelector(["yandex#map", "yandex#satellite", "yandex#hybrid", "yandex#publicMap"]);

			    	var pos = [data[0], data[1]];
			    	group = new ymaps.GeoObjectCollection();
			    	group.add(new ymaps.Placemark(pos, {balloonContent: data[2]}));
			    	map.geoObjects.add(group);
			    	map.setCenter(pos, 14);
			}

			ymaps.ready(function(){
				init();
			});

			minisite.toggleMap({
				becomeBig: function(){
					map.container.fitToViewport();
					map.controls.add("zoomControl").add("mapTools").add(typeSelector);
				},
				becomeSmall: function(){
					map.container.fitToViewport();
					map.controls.remove("zoomControl").remove("mapTools").remove(typeSelector);
				}

			});

		', CClientScript::POS_LOAD);?>

		<span id="toggleMap" class="-pseudolink -small -gray -opaque-box"><i>Развернуть карту</i></span>
	</div>

	<ul class="-menu-block">
		<li class="-icon-location-s -icon-gray -gutter-bottom">
			<?php
			$city = $store->getCityOfflineStore();
			if ($city) {
				echo $city->country->name . ', г. ' . $city->name . '<br>' . $store->address;
			}
			?>

		</li>
		<li class="-icon-mobile-s -icon-gray -gutter-bottom-hf"><?php echo $store->phone;?></li>
		<li class="-icon-earth-s -icon-gray -gutter-bottom-hf">
			<?php
			if ($store->site) {
                if($store->anchor) {
                    $anchor = $store->anchor;
                } else {
                    $anchor = Amputate::absoluteUrl($store->site);
                }
				echo CHtml::link(
					$anchor,
					$this->createUrl(
						'viewSite',
						array(
							'store_id' => $store->id
						)
					),
					array('target' => '_blank', 'class' => '-red')
				);
			}
			?>

		</li>
		<li class="-icon-mail-s -icon-gray -gutter-bottom-hf">
			<?php
			if ($store->email) {
				echo CHtml::link($store->email, 'mailto:' . $store->email);
			}
			?>
		</li>
		<li class="-icon-clock-s -icon-gray -gutter-top -gray">
			<?php $time = $store->getTimeArray(); ?>
			Пн-Пт<span class="-gutter-left-hf"><?php echo $time['weekdays']['work_from'] . '–' . $time['weekdays']['work_to']; ?></span>
			<br>Сб<span class="-gutter-left -inset-left-hf"><?php echo $time['saturday']['work_from'] . '–' . $time['saturday']['work_to']; ?></span>
			<br>Вс<span class="-gutter-left -inset-left-hf"><?php echo $time['sunday']['work_from'] . '–' . $time['sunday']['work_to']; ?></span>
		</li>
	</ul>
</div>


<?php
/* -----------------------------------------------------------------------------
 *  Отзывы. Ссылка на раздел и несколько последних отзывов.
 * -----------------------------------------------------------------------------
 */
?>

<h2 class="-gutter-bottom-dbl -block">Отзывы
	<span class="-gutter-left-hf -normal -huge -gray"><?php echo $store->feedbackQt;?></span>
	<a href="<?php echo Store::getLink($store->id, 'moneyFeedback'); ?>"
	   class="-gutter-left-hf -pointer-right -normal -small -gray">Все</a>
</h2>

<?php
$lastFeedbacks = StoreFeedback::model()->findAllByAttributes(array(
	'store_id' => $store->id,
	'parent_id' => 0
), array(
	'order' => 'create_time DESC',
	'limit' => 2
));

if ($lastFeedbacks) {
	foreach ($lastFeedbacks as $data) {
		?>
		<div class="short-review">
			<div class="-col-wrap -inset-right-qr">
				<?php echo CHtml::image('/'.$data->author->getPreview( Config::$preview['crop_25'] ), '', array('class' => '-quad-25', 'width' => 25, 'height' => 25));?>
			</div>
			<div class="-col-wrap -small">
				<span class="-block"><?php echo $data->author->name; ?></span>
				<span class="-block -gray"><?php echo CFormatterEx::formatDateToday($data->create_time);?></span>
			</div>
			<div class="-gutter-top -gutter-bottom">
				<?php $this->widget('application.components.widgets.WStarGrid', array(
					'selectedStar'   => $data->mark,
					'itemClass'      => '-icon-star-xs',
					'itemClassEmpty' => '-icon-star-empty-xs',
					'labels'         => array(
						1 => 'Ужасный магазин',
						2 => 'Плохой магазин',
						3 => 'Обычный магазин',
						4 => 'Хороший магазин',
						5 => 'Отличный магазин'
					),
				));?>
			</div>
			<p><?php echo $data->message; ?></p>
		</div>
		<?php
	}
}
?>

<a href="<?php echo Store::getLink($store->id, 'moneyFeedback') . '#product_comment_form';?>" class="-icon-bubble -red">Оставить отзыв</a>