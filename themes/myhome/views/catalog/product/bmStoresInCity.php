<?php $this->pageTitle = 'Где купить — ' . $model->name . ' — ТВК «Большая Медведица» — MyHome.ru'; ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CCatalog.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/scroll.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/catalog.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerScript('initial-run', '
        $(function(){
                cat.initBreadCrumbs();
        });
', CClientScript::POS_READY);?>

<div class="-grid-wrapper page-content">
<?php $this->widget('catalog.components.widgets.CatBreadcrumbs', array(
	'category' => $model->category,
	'homeLink' => '<a href="'.Yii::app()->params->bmHomeUrl.'">Каталог ТВК «Большая Медведица»</a>',
	'pageName' => $model->name,
	'mallCatalogClass' => true,
	'afterH1'  => '',//$this->renderPartial('//widget/bmLogo', array(), true),
)); ?>

<div class="shop_card">

	<?php $this->renderPartial('_bmMenuBlock', array('model' => $model)); ?>

	<form method="get" id="changeCity"
	      action="<?php echo $this->createUrl('/product', array('id' => $model->id, 'action' => 'storesInCity'))?>">
		<div class="region_selector">
			<label>Город
				<?php echo CHtml::dropDownList('cid', $city->id, $cities, array('class' => 'textInput', 'id' => 'city')); ?>
			</label>
		</div>
	</form>


	<div class="shops_list" id="scrollbar">
		<div class="scrollbar">
			<div class="track">
				<div class="thumb"></div>
			</div>
		</div>
		<div class="viewport">
			<div class="fader"></div>
			<div class="overview">
				<ul class="">
					<?php $i = 0; ?>
					<?php foreach ($stores as $store) : ?>
					<?php $i++; ?>
					<li>
						<div>
							<span><?php echo $i; ?>.</span>
							<?php echo CHtml::tag('h2', array('lat' => $store->getCoordinates('lat'), 'lng' => $store->getCoordinates('lng'), 'hint' => $store->getBaloonContent(), 'class' => 'item_name'), '<span>'.$store->name.'</span>'); ?>
						</div>
						<?php // ---- АДРЕС ----
						echo CHtml::openTag('address');
						echo $store->address;
						echo CHtml::closeTag('address');
						?>

						<?php // ---- ЦЕНА ----
						$storePrice = StorePrice::model()->findByAttributes(array(
							'store_id'   => $store->id,
							'product_id' => $model->id
						));
						if ($storePrice  && $storePrice->price > 0)
							echo CHtml::tag('b', array(), 'от '.number_format($storePrice->price, 0, '.', ' ').' руб.');
						else
							echo CHtml::tag('b', array('class' => 'undefined'), 'Цена не указана');
						?>

					</li>
					<?php endforeach; ?>

				</ul>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		cat.scroll();
	</script>

	<div class="shop_map">
		<!-- Этот блок кода нужно вставить в ту часть страницы, где вы хотите разместить карту (начало) -->
		<div id="map" style="width: 100%; height: 650px;"></div>
		<script type="text/javascript"
			src="http://api-maps.yandex.ru/2.0/?coordorder=longlat&load=package.full&wizard=constructor&lang=ru-RU"></script>

		<?php Yii::app()->clientScript->registerScript('yandexmap', '
                var map;
                var group;

                function init()
                {
                    map = new ymaps.Map ("map", {
                            center: [55.76, 37.64],
                            zoom: 7
                        });
                    map.controls.add("zoomControl").add("mapTools").add(new ymaps.control.TypeSelector(["yandex#map", "yandex#satellite", "yandex#hybrid", "yandex#publicMap"]));
                    var data = ' . CJavaScript::encode($mapData) . ';
                    var point;
                    group = new ymaps.GeoObjectCollection();
                    for (var i = 0; i < data.length; i++) {
                        point = data[i].coord;
                        placemark = new ymaps.Placemark(data[i].coord, {balloonContent: data[i].baloonContent, iconContent: (i+1).toString()});
                        group.add(placemark);
                    }
                    map.geoObjects.add(group);

                    if(data.length>1) {
                        map.setBounds(group.getBounds());

                        // Уменьшаем zoom на 1, чтобы все балуны были хорошо видны.
                        var cur_zoom = map.getZoom();
                        if (cur_zoom > 1) {
                        	map.setZoom(cur_zoom - 1);
                        }

                    }
                    else
                        map.setCenter(point, 14);
                }

                function showPoint(lat, lng, hint)
                {
                        if(lat == "" || lng == "")
                                return;

                        group.removeAll();
                        var pos = [lat, lng];
                        var placemark = new ymaps.Placemark(pos, {balloonContent: hint});
                        group.add(placemark);
                        map.geoObjects.add(group);
                        map.setCenter(pos, 14);
                        placemark.balloon.open();
                }

                ymaps.ready(function(){
                        init();
                });

                $(".shops_list li h2").click(function(){
                        var li = $(this).parents("li");
                        var parent = $(".shops_list");

                        showPoint($(this).attr("lat"), $(this).attr("lng"), $(this).attr("hint"));

                        parent.find("li").removeClass("current");
                        li.addClass("current");
                });

        ', CClientScript::POS_LOAD);?>
	</div>

	<div class="clear"></div>
</div>

<?php Yii::app()->clientScript->registerScript('storesInCity', '
        $("#city").change(function(){
                $("#changeCity").submit();
        });
', CClientScript::POS_READY);?>
</div>