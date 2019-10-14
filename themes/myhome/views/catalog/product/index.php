<?php /** @var $this FrontController */ ?>

<?php Yii::app()->clientScript->registerScriptFile('/js-new/CCatalog.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js-new/catalog.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js-new/jquery.popup.carousel.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js-new/jquery.simplemodal.1.4.4.min.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js-new/scroll.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile('http://api-maps.yandex.ru/2.0/?coordorder=longlat&load=package.full&wizard=constructor&lang=ru-RU', CClientScript::POS_BEGIN); ?>

<?php
// SEO оптимизация
$this->pageTitle = $model->name . ' по низкой цене, продажа ' . $model->category->genitiveCase . ', каталог интернет магазинов';

$this->description = 'Купить ' . $model->name . ' по низкой цене. Полный каталог '
	. $model->category->genitiveCase . ' от '
	. CFormatterEx::formatNumeral($storeCount, array('интернет-магазина', 'интернет-магазинов', 'интернет-магазинов'));

$this->keywords = 'купить ' . $model->name . ', цены на ' . $model->category->name;
?>

<?php
/* =============================================================================
 *  SEO оптимизация
 * =============================================================================
 */

Yii::app()->openGraph->title = $model->name;
Yii::app()->openGraph->description = $model->desc;
// Обложка
Yii::app()->openGraph->image = Yii::app()->homeUrl . '/' . $model->cover->getPreviewName(Product::$preview['resize_380']);
// Остальные товары
foreach ($model->getImages(true) as $image) {
	Yii::app()->openGraph->image = Yii::app()->homeUrl . '/' . $image->getPreviewName(Product::$preview['resize_380']);
}

Yii::app()->openGraph->renderTags();
?>

<?php if (in_array(Yii::app()->user->role, array(User::ROLE_POWERADMIN, User::ROLE_ADMIN, User::ROLE_MODERATOR)))
	$page_name = $model->name . ' ' . CHtml::link('[редакт.]', Yii::app()->createUrl('/catalog/admin/product/update/', array('ids' => $model->id, 'category_id' => $model->category_id)), array('style' => 'text-decoration: underline;'));
else
	$page_name = $model->name;
?>

<div class="-grid-wrapper page-title">
	<?php $this->widget('catalog.components.widgets.CatBreadcrumbs', array(
		'category'    => $model->category,
		'pageName'    => $page_name,
		'productCard' => true
	)); ?>
</div>

<div class="-grid-wrapper page-content">
<div class="-grid">

	<!-- Фото и превьюшки //-->
	<div class="-col-7 photo">
		<!-- Превью обложки -->
		<div class="preview">
			<img src="<?php echo '/' . $model->cover->getPreviewName(Product::$preview['resize_510'], 'default', true) ?>" id="originPhoto_0" alt="<?php echo CHtml::encode($model->name);?>">
		</div>
		<div class="zoom"></div>
		<!-- Превью остальных фото -->
		<?php $images = $model->getImages(true);

		if ($images) : ?>
			<div class="thumbs">
				<a href="<?php echo '/' . $model->cover->getPreviewName(Product::$preview['resize_510']) ?>"
				   class="-inline current"><img src="<?php echo '/' . $model->cover->getPreviewName(Product::$preview['resize_60']) ?>"
								class="-quad-60"></a>
				<?php foreach ($images as $image) : ?>
					<a href="<?php echo '/' . $image->getPreviewName(Product::$preview['resize_510']) ?>"
					   class="-inline"><img src="<?php echo '/' . $image->getPreviewName(Product::$preview['resize_60']) ?>"
								class="-quad-60"></a>
				<?php endforeach ?>
			</div>
		<?php endif; ?>
	</div>
	<!-- Цена //-->

	<div class="-col-5 summary">
		<?php if ($store) : ?>
		<div class="-header-1">
			<?php
			// Цена товара в магазине
			/** @var $storePrice StorePrice */
			$storePrice = StorePrice::model()->findByAttributes(array(
				'store_id'   => $store->id,
				'product_id' => $model->id
			));

			if ($storePrice->discount > 0) {
			?>
			<span class="discount"><?php echo number_format($storePrice->discount, 0, '.', ' ') ?>
				%</span><?php echo number_format($storePrice->getNumberDiscount(), 0, '.', ' ') ?>
			<span class="-giant">руб.</span>
				<span class="-gray"><?php echo number_format($storePrice->price, 0, '.', ' '); ?>
					<span class="-giant">руб.</span>
					<?php
					}
					elseif ($storePrice && $storePrice->price > 0) {
						$ot = ($storePrice->price_type == $storePrice::PRICE_TYPE_MORE)
							? 'от '
							: '';
						echo $ot . number_format($storePrice->price, 0, '.', ' ') . CHtml::tag('span', array('class' => '-giant'), ' руб.');
					}?>
			</span>
		</div>

		<div class="-gutter-top-hf -gutter-bottom-dbl -huge -semibold">
			<?php $selectedCity = Yii::app()->user->getSelectedCity() ?>
			<?php if (!$storesInUserCity && $selectedCity && $store->type != Store::TYPE_ONLINE) { ?>
				<span class="-block -gray">Нет в продаже в <?php echo $selectedCity->prepositionalCase ?></span>
			<?php } ?>
			<?php if ($store->type != Store::TYPE_ONLINE) { ?>
			<span class="-block">Магазин в <?php echo $store->city->prepositionalCase . ':' ?>
				<br>
				<a class="-skyblue" href="<?php echo $store->getLink($store->id) ?>"><?php echo $store->name ?></a></span>
		</div>
		<noindex><a href="<?php echo $store->getLink($store->id) ?>"
		   onclick="_gaq.push(['_trackEvent','inShop','click']);return true;"
		   class="-button -button-skyblue">Перейти в магазин</a></noindex>
		<?php
		}
		else {
		?>
		<span class="-block">Магазин
         <noindex>
				<a class="-skyblue"
                   rel="nofollow"
				   href="<?php echo $store->site ?>"><?php echo $store->name ?></a></span>
        </noindex>
	</div>
	<noindex>
	<a href="<?php echo $storePrice->url ?>"
	   target="_blank"
	   rel="nofollow"
	   onclick="_gaq.push(['_trackEvent','inShop','click']);return true;"
	   class="-button -button-skyblue">Перейти в магазин</a>
	</noindex>

	<?php } ?>


	<!--Диапазон цен-->
	<div class="-block -gutter-top-dbl -gutter-bottom-dbl -inset-bottom-dbl">
		<?php
		$price = StorePrice::getPriceOffer($model->id);

		if ($price['min'] !== $price['max']) {
			?>
			<span class="-block -huge -semibold"><?php echo number_format($price['min'], 0, '.', ' ') ?>
				...<?php echo number_format($price['max'], 0, '.', ' ') ?>
				руб.</span>
		<?php } ?>
		<?php if (count($stores) > 1) : ?>
			<span class="-block -gray"> <?php echo CFormatterEx::formatNumeral(count($stores), array('в другом', 'в других', 'в других'), true) ?>
				<a href="javascript:void(0)"
				   class="-skyblue -acronym"
				   onclick="CCommon.scrollTo('#sub1')"> <?php echo CFormatterEx::formatNumeral(count($stores), array('магазине', 'магазинах', 'магазинах')) ?></a></span>
		<?php endif; ?>
	</div>

	<!--Рейтинг товара-->
	<noindex>
	<div class="-rating">
		<?php $this->widget('application.components.widgets.WStar', array(
			'selectedStar'      => $model->average_rating,
			'useNewRealisation' => true,
			'largeIcon'         => true

		));?>
		<?php $feedback_qt = Feedback::model()->count('product_id=:pid', array(':pid' => $model->id)); ?>
		<?php echo CHtml::link(($feedback_qt == 0)
				? 'Нет отзывов'
				: CFormatterEx::formatNumeral($feedback_qt, array('отзыв', 'отзыва', 'отзывов')) . ' о товаре', 'javascript:void(0)',
			array('class' => '-acronym -gray -gutter-left-hf', 'onclick' => 'CCommon.scrollTo("#sub2")')); ?>
	</div>
	</noindex>
	<div class="shortcut">
		<?php if ($model->countryObj) : ?>
			<span class="-block">
					Страна —
				<?php echo $model->countryObj->name; ?>
				</span>
		<?php endif; ?>

		<span class="-block">
					Производитель —
			<?php echo CHtml::link($model->vendor->name, Vendor::getLink($model->vendor_id), array('class' => '-black')); ?>
			</span>

		<?php if ($model->guaranty) : ?>
			<span class="-block">
					Гарантия —
				<?php echo $model->guaranty; ?>
				</span>
		<?php endif; ?>
	</div>


	<div class="social">
		<noindex>
		<?php $this->widget('ext.sharebox.EShareBox', array(
			'view'             => 'productGrid',
			// url to share, required.
			'url'              => Yii::app()->request->hostInfo . Yii::app()->request->requestUri,
			'imgUrl'           => Yii::app()->request->hostInfo . '/' . $model->cover->getPreviewName(Product::$preview['resize_960']),

			// A title to describe your link, required.
			'title'            => !empty($model->name)
				? $model->name : 'товар',

			// A small message for post
			'message'          => Amputate::getLimb($model->desc, 500, '...'),
			'classDefinitions' => array(
				'facebook'  => '-icon-facebook -icon-softgray',
				'vkontakte' => '-icon-vkontakte -icon-softgray',
				'twitter'   => '-icon-twitter -icon-softgray',
				'google+'   => '-icon-google-plus -icon-softgray',
				'odkl'      => '-icon-odnoklassniki -icon-softgray',
				'pinterest' => '-icon-pinme -icon-softgray',
			),
			'exclude'          => array('livejournal'),
			'htmlOptions'      => array(),
		));?>

		<?php // Подключаем виджет для добавления в избранное
		$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
			'modelId'   => $model->id,
			'modelName' => get_class($model),
			'viewHeart' => 'favoriteProductCard',
		));?>
	</noindex>
	</div>
	<?php endif; ?>
</div>

<hr class="-absolute page-vbreak">
<?php
/* -----------------------------------------------------------------------------
 *  Описание товара
 * -----------------------------------------------------------------------------
 */
$this->renderPartial('//catalog/product/_description',
	array('model' => $model)
); ?>

<!-- Где купить //-->
<?php $countSorted = count($sorted); ?>
<?php if ((count($stores) > 1 || $countSorted > 1 || $storesOnline) && !$inStore) : ?>
	<div class="-col-3">
		<h2 class="-giant">Где<br>купить</h2>
	</div>
	<div class="-col-9 online-stores-list">
	<?php $classOfflineH3 = "--gutter-top-dbl --inset-top-dbl -gutter-top-null" ?>
	<?php if ($storesOnline) : ?>
		<?php $classOfflineH3 = "-gutter-top-dbl -inset-top-dbl" ?>
		<h3 class="-gutter-top-null">В интернет-магазине</h3>
		<table>
			<tbody>
			<?php foreach ($storesOnline as $so) : ?>
				<tr class="odd">
					<td><noidex><a href="<?php echo $so->site; ?>"
                           rel="nofollow"
					       target="_blank"
					       class="-skyblue -large"><?php echo $so->name ?></a>
                        </noidex>
                    </td>
					<td><span class="-huge -semibold">
						<?php
						$storePrice = StorePrice::model()->findByAttributes(array(
							'store_id'   => $so->id,
							'product_id' => $model->id
						));


						if ($storePrice && $storePrice->price > 0) {
							$ot = ($storePrice->price_type == $storePrice::PRICE_TYPE_MORE)
								? 'от '
								: '';
							echo $ot . number_format($storePrice->price, 0, '.', ' ') . ' руб.';
						} else {
							echo 'Цена не указана';
						}
						?>
				    </span>
					</td>

					<td>
                        <noindex>
                            <?php
                            echo CHtml::link('Перейти в магазин', $storePrice->url, array('class' => '-button -button-skyblue', 'target' => '_blank', 'rel' => 'nofollow'))
                            ?>
                        </noindex>
					</td>
					<?php unset($storePrice); ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<?php if ((count($stores) > 1 || $countSorted > 1) && $store->type != Store::TYPE_ONLINE) : ?>
		<!--Если нет интернет магазинов добавляем класс -gutter-top-null и убираем top-dbl -->
		<h3 class="<?php echo $classOfflineH3 ?>" id="sub1">
			В <span class="current-city-name"><?php echo $store->city->prepositionalCase ?></span>
			<?php if ($countSorted > 1) : ?>
				<a class="-icon-location-s -gutter-left -red -medium -normal popup-city-list" href="javascript:void(0)">Другой город</a>
			<?php endif; ?>
		</h3>

        <div class="offline-stores-list">
            <div class="-col-5">
                <?php $this->renderPartial('_storesInCityMini', ['stores'=>$stores, 'city'=>$store->city, 'model'=>$model]); ?>
            </div>
            <div class="-col-4">
                <div class="map" id="map"></div>
            </div>
        </div>

        <a href="javascript:void(0);" class="-icon-toggle-blank -icon-pull-right -gray popup-city-stores">
            <?php echo CFormatterEx::formatNumeral(count($stores), array('магазин', 'магазина', 'магазинов')) . ' ' . $store->city->genitiveCase ?>
        </a>

        <?php
        $mapData = $this->_getMapPointsForStores($stores);
        $sliceData = (count($mapData > 5) ? array_slice($mapData, 0, 5) : $mapData);
        ?>
        <?php Yii::app()->clientScript->registerScript('whereBuy', '
            function WhereBuyManager() {

                var self = this;
                self.mapMini = {};
                self.mapFull = {};

                self.createMap = function(id, data) {
                    var map = new ymaps.Map (id, {center: [55.76, 37.64], zoom: 7});
                    var group = new ymaps.GeoObjectCollection();
                    var point = null;
                    for (var i = 0; i < data.length; i++) {
                        point = data[i].coord;
                        var placemark = new ymaps.Placemark(data[i].coord, {balloonContent: data[i].baloonContent, iconContent: (i+1).toString()});
                        group.add(placemark);
                    }
                    map.geoObjects.add(group);
                    return {
                        map : map,
                        group : group,
                        data : data,
                        lastPoint : point
                    };
                };

                self.renderMiniMap = function(data) {
                    self.mapMini = self.createMap("map", data);
                    self.mapMini.map.controls.add("zoomControl").add(new ymaps.control.TypeSelector(["yandex#map", "yandex#satellite", "yandex#hybrid", "yandex#publicMap"]));
                    self.drawMiniMap(data, self.mapMini.lastPoint);
                };

                self.drawMiniMap = function(data, point) {
                    if (data.length > 1) {
                        self.mapMini.map.setBounds(self.mapMini.group.getBounds());
                        var cur_zoom = self.mapMini.map.getZoom();
                        if (cur_zoom > 1) {
                            self.mapMini.map.setZoom(cur_zoom - 1);
                        }
                    } else {
                        self.mapMini.map.setCenter(point, 14);
                    }
                };

                self.renderFullMap = function(data) {
                    self.mapFull = self.createMap("mapPopup", data);
                    self.mapFull.map.controls.add("zoomControl").add("mapTools").add(new ymaps.control.TypeSelector(["yandex#map", "yandex#satellite", "yandex#hybrid", "yandex#publicMap"]));
                    self.drawFullMap(data, self.mapFull.lastPoint);
                };

                self.drawFullMap = function(data, point) {
                    if (data.length>1) {
                        self.mapFull.map.setBounds(self.mapFull.group.getBounds());
                        self.mapFull.map.setZoom(10);
                        self.mapFull.map.container.fitToViewport();
                    } else {
                        self.mapFull.map.setCenter(point, 14);
                    }
                }

                self.showPoint = function (lat, lng, hint) {
                    if(lat == "" || lng == "")
                        return;

                    self.mapMini.group.removeAll();
                    var pos = [lat, lng];
                    var placemark = new ymaps.Placemark(pos, {balloonContent: hint});
                    self.mapMini.group.add(placemark);
                    self.mapMini.map.geoObjects.add(self.mapMini.group);
                    self.mapMini.map.setCenter(pos, 14);
                    placemark.balloon.open();
                }

                self.redrawPoints = function(map, data, drawer) {
                    map.data = data;
                    map.group.removeAll();
                    map.group = new ymaps.GeoObjectCollection();
                    var point = null;
                    for (var i = 0; i < data.length; i++) {
                        point = data[i].coord;
                        var placemark = new ymaps.Placemark(data[i].coord, {balloonContent: data[i].baloonContent, iconContent: (i+1).toString()});
                        map.group.add(placemark);
                    }
                    map.map.geoObjects.add(map.group);
                    drawer(data, point);
                }
            }
            var whereBuyManager = new WhereBuyManager();
            whereBuyManager.renderMiniMap('.CJavaScript::encode($sliceData).');
            whereBuyManager.renderFullMap('.CJavaScript::encode($sliceData).');

            $(".popup-city-stores").click(function(){
                setTimeout(function(){
                    whereBuyManager.redrawPoints(whereBuyManager.mapFull, whereBuyManager.mapFull.data, whereBuyManager.drawFullMap);
                }, 1000)
            });

            $(".offline-stores-list span ").click(function(){
                var li = $(this).parents("li");
                var parent = $(".offline-stores-list");
                whereBuyManager.showPoint($(this).attr("lat"), $(this).attr("lng"), $(this).attr("hint"));
                parent.find("li").removeClass("current");
                li.addClass("current");
                return false;
            });


            $(".city-list").on("click", ".another-city-link", function() {
                var cid = $(this).data("cid");
                var pid = $(this).data("pid");

                $.ajax({
                    url: "/catalog/product/mapData",
                    data: { productId : pid, cityId : cid },
                    async: true,
                    dataType: "json",
                    success:  function(response) {
                        whereBuyManager.redrawPoints(whereBuyManager.mapMini, response.points, whereBuyManager.drawMiniMap);
                        $(".offline-stores-list .-col-5").html(response.storesInCityMini);
                        $.ajax({
                            url: "/catalog/product/mapData",
                            data: { productId : pid, cityId : cid, limit : 100 },
                            dataType: "json",
                            async: true,
                            success:  function(response) {
                                whereBuyManager.redrawPoints(whereBuyManager.mapFull, response.points, whereBuyManager.drawFullMap);
                                $(".popup-city-stores").text(response.popupCityStores);
                                $(".current-city-name").text(response.currentCityName);
                                $(".city-stores .addresses").html(response.storesInCityFull);
                                $(".simplemodal-close").click();
                                return false;
                            }
                        });
                        return false;
                    }
                });
                return false;
            });
        ', CClientScript::POS_END); ?>
	<?php endif; ?>
	</div>
<?php endif ?>
<!-- Варианты модели //-->
<?php $similars = $model->getSimilar(true); ?>
<?php if ($similars) {
	$this->renderPartial('//catalog/product/_similarProduct',
		array('similars' => $similars)
	);
}
?>


<!-- Мнение покупателей //-->
<div class="-col-3">
	<span class="-giant" id="sub2">Мнение покупателей</span>
</div>
<div class="-col-9 reviews">


	<?php $this->widget('zii.widgets.CListView', array(
		'dataProvider' => $feedbacks,
		'itemView'     => '_feedbackItemIndexPageGrid',
		'template'     => '{items}',
		'emptyText'    => '',

	));
	?>
	<div class="error-list">

	</div>
	<?php if (!Yii::app()->user->isGuest && !$model->checkFeedback) : ?>
		<div class="review">
			<div class="-col-wrap">
				<?php $user = Yii::app()->user->getModel() ?>
				<?php echo CHtml::image('/' . $user->getPreview(Config::$preview['crop_25']), '', array('class' => '-quad-25')); ?>
			</div>
			<form>
				<div class="-col-wrap review-form">
					<div>
						<span class="-icon-bubble -gray">В общем:</span>
						<input name="Feedback[message]">
					</div>
					<div>
						<span>Достоинства:</span>
						<input name="Feedback[merits]">
					</div>
					<div>
						<span>Недостатки:</span>
						<input name="Feedback[limitations]">
					</div>
				</div>
				<div class="-col-wrap -block rating-stars"
				     id="rating-2">
					<i class="-icon-star-empty -icon-only"
					   data-rating="Не рекомендую"></i>
					<i class="-icon-star-empty -icon-only"
					   data-rating="Плохо"></i>
					<i class="-icon-star-empty -icon-only"
					   data-rating="Нормально"></i>
					<i class="-icon-star-empty -icon-only"
					   data-rating="Хорошо"></i>
					<i class="-icon-star-empty -icon-only"
					   data-rating="Рекомендую"></i>
					<span class="-gutter-left-hf -small -gray">ваша оценка</span>
					<input value=""
					       name="Feedback[mark]"
					       id="Review_rating"
					       type="hidden">
					<input value="create"
					       name="Review[action]"
					       id="Review_action"
					       type="hidden">
				</div>
				<div class="-error-list -hidden -gutter-bottom-dbl">
					<i class="-icon-alert"></i>

					<ol>

					</ol>
				</div>
				<button type="submit"
					class="-button -button-skyblue">
					Опубликовать отзыв
				</button>
			</form>
			<script>
				CCommon.rating($('#rating-2'));
			</script>
		</div>
	<?php endif; ?>

	<?php if (Yii::app()->user->isGuest) : ?>
		<div class="-gray guest-review">Чтобы оставить отзыв о товаре,
			<a href="#"
			   class="-skyblue -login">войдите</a> или
			<a href="/site/registration"
			   class="-skyblue">зарегистрируйтесь</a></div>
	<?php endif; ?>
    <?php echo Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_item_under'); ?>
</div>

<div class="-col-3">
    <?php
    $city = $store->city;
    if (!$city) {
        $city = Yii::app()->user->getSelectedCity();
    }
    if (!$city) {
        $city = Yii::app()->user->getDetectedCity();
    }
    $cityPath = (($city instanceof City)
        ? 'в ' . ((empty($city->prepositionalCase))
            ? 'городе ' . $city->name: $city->prepositionalCase)
        : ''); ?>
    <span class="-giant" id="sub2">Похожие товары <?php echo $cityPath; ?></span>
</div>
<div class="-col-9 similar-goods">
    <div class="-grid">
        <?php foreach ($model->getSimilarNew($city ? $city->id : null) as $key => $similar) : ?>
            <div class="-col-3">
                <a class="-block"
                   title="<?php echo $similar->name ?>"
                   href="<?php echo Product::getLink($similar->id, null, $similar->category_id); ?>">
                    <?php if ($similar->cover) : ?>
                        <noindex><?php echo CHtml::image(
                                '/' . $similar->cover->getPreviewName(Product::$preview['crop_220']),
                                $similar->name,
                                array(
                                    'width'  => 220,
                                    'height' => 220,
                                    'class'  => '-quad-220 -gutter-bottom-hf'
                                )
                            ); ?></noindex>
                    <?php endif; ?>

                    <span class="-block -inset-left-hf -inset-right-hf"><?php echo $similar->name ?></span>
                </a>

                <div class="-inset-all-hf -strong">
                    <?php
                    if($similar->average_price) {
                        echo number_format($similar->average_price, 0, '.', ' ') . ' руб.';

                    } else {
                        echo 'Цена не указана.';
                    }
                    ?>
                </div>
                <?php // Подключаем виджет для добавления в избранное
                $this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
                    'modelId'   => $similar->id,
                    'modelName' => get_class($similar),
                    'viewHeart'  => 'favorite',
                ));?>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<?php
/*Попап с магазинами города и картой*/
if ($store->type != Store::TYPE_ONLINE) {
	$this->renderPartial('//catalog/product/_cityStoriesPopap',
		array(
			'stores'      => $stores,
			'store'       => $store,
			'model'       => $model,
			'countSorted' => $countSorted
		));
}
/*Попап с выбором города*/
if (count($sorted) > 1) {
	$this->renderPartial('//catalog/product/_citiesWithProduct', array('sorted' => $sorted, 'model' => $model));
}
?>
</div>

<!--Попап с фотками товара-->
<div class="photogallery-view -hidden">

</div>


<script type="text/javascript">
	catalog.setOptions({'modelId':<?php echo $model->id; ?>});
	catalog.initProductPage();
	catalog.photoPopup();
	$(function () {
		//cat.initBreadCrumbs();
		cat.thumbs();
		// cat.showHint();
	});
</script>