<?php $this->pageTitle = 'ТВК «Большая Медведица» — MyHome.ru'; ?>

<?php
/** @var $cs CClientScript */
$cs = Yii::app()->getClientScript();

$cs->registerCssFile('/css/catalog.css');
$cs->registerCssFile('/css/bm.css');
?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>

<?php
Yii::app()->openGraph->title = $mall->name;
Yii::app()->openGraph->description = $mall->about;
if ($mall->logoFile) {
	Yii::app()->openGraph->image = $mall->logoFile->getPreviewName(MallBuild::$preview['resize_140']);
}
Yii::app()->openGraph->renderTags();


?>
<div class="-grid-wrapper page-content">

	<?php
	$this->widget('catalog.components.widgets.CatBreadcrumbs',
		array(
			'category' => Category::getRoot(),
			'homeLink' => '<a href="'.Yii::app()->params->bmHomeUrl.'">Каталог ТВК «Большая Медведица»</a>',
			'pageName' => $mall->name,
		)
	);?>

	<div class="-col-12 shop_card">

		<div class="shop_left tvk_banner">
			<div class="shop_info">
				<?php
				if ($mall->logoFile) {
					$src = $mall->logoFile->getPreviewName(MallBuild::$preview['resize_140']);
					echo CHtml::image('/'.$src, $mall->name, UploadedFile::getImageSize($src)) ;
				}
				?>
				<div class="shop_contacts">
					<p class="-gutter-null"><?php echo nl2br($mall->address);?></p>
					<p><?php echo $mall->phone;?></p>
					<ul class="timetable">
						<?php
						$workTimeArray = unserialize($mall->work_time);

						echo '<li><span>Пн&ndash;Вс</span><span class="time">'.$workTimeArray['weekdays']['work_from'].'–'.$workTimeArray['weekdays']['work_to'].'</span></li>';
						?>

					</ul>
					<p><a href="<?php echo $mall->site;?>" class="-red"><?php echo $mall->site;?></a></p>
				</div>
				<div class="shop_rubrics">
					<a href="<?php echo Yii::app()->params->bmHomeUrl;?>"><img src="/img-new/banner/bm-b-03.png"></a>
				</div>
				<div class="clear"></div>
			</div>
			<div class="shop_map">
				<!-- Этот блок кода нужно вставить в ту часть страницы, где вы хотите разместить карту (начало) -->
				<div id="ymaps-map-id_134700315320291014453" style="width: 698px; height: 278px;"></div>
				<script type="text/javascript">function fid_134700315320291014453(ymaps) {var map = new ymaps.Map("ymaps-map-id_134700315320291014453", {center: [82.932696, 55.081423], zoom: 15, type: "yandex#map"});map.controls.add("zoomControl").add("mapTools").add(new ymaps.control.TypeSelector(["yandex#map", "yandex#satellite", "yandex#hybrid", "yandex#publicMap"]));map.geoObjects.add(new ymaps.Placemark([82.932696, 55.081423], {balloonContent: ""}, {preset: "twirl#lightblueDotIcon"}));};</script>
				<script type="text/javascript" src="http://api-maps.yandex.ru/2.0/?coordorder=longlat&load=package.full&wizard=constructor&lang=ru-RU&onload=fid_134700315320291014453"></script>
				<!-- Этот блок кода нужно вставить в ту часть страницы, где вы хотите разместить карту (конец) -->
			</div>
			<div class="shop_description">
				<?php
				if ($services) {
					echo '<ul class="-menu -block -push-right">';
					foreach($services as $serv) {
						echo CHtml::tag('li', array(), $serv->name);
					}
					echo '</ul>';
				}
				?>

				<p><?php echo nl2br($mall->about);?></p>

				<!--<span class="all_elements_link" style="display: inline;">
					<a href="#">Показать полностью</a>
					<span>↓</span>
				</span>-->
			</div>
		</div>
		<div class="product_icons">
			<!--<div class="manufacturer_list">
				<h3 class="headline">Производители</h3>
				<ul class="">
					<li><a href="#" title="производитель"><img src="/img/tmp/catalog/cat17.jpg" alt="производитель"></a></li>
					<li><a href="#" title="производитель"><img src="/img/tmp/catalog/cat17.jpg" alt="производитель"></a></li>
					<li><a href="#" title="производитель"><img src="/img/tmp/catalog/cat17.jpg" alt="производитель"></a></li>
					<li>
									<span class="all_elements_link" style="display: inline;">
										<a href="#">Все</a>
										<span>&rarr;</span>
									</span>
					</li>
				</ul>
				<div class="clear"></div>
				<a href="#" class="-default -icon-toggle-blank -icon-float-right -icon-align-left">Весь список магазинов на medvediza.ru</a>
			</div>-->
			<div class="social_links">
				<?php $this->widget('ext.sharebox.EShareBox', array(
				'view' => 'news',
				// url to share, required.
				'url' => Yii::app()->request->hostInfo.Yii::app()->request->requestUri,

				// A title to describe your link, required.
				'title'=> $mall->name,

				// A small message for post
				'message' => Amputate::getLimb($mall->about, 500, '...'),
				'classDefinitions' => array(
					'livejournal' => 'ns-lj',
					'vkontakte'   => 'ns-vk',
					'twitter'     => 'ns-tw',
					'facebook'    => 'ns-fb',
					'google+'     => 'ns-gp',
				),
				'exclude' => array('odkl','pinterest'),
				'htmlOptions' => array('class' => 'social'),
			));?>
			</div>
		</div>
		<div class="spacer-30"></div>
		<div class="clear"></div>

	</div>
</div>