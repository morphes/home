<?php
/**
 * @var Category[] $categories Список категорий относительно активной $category
 */

/** @var $cs CustomClientScript */
$cs = Yii::app()->getClientScript();
$cs->registerCssFile('/css-new/generated/goods.css');
$cs->registerScriptFile('/js-new/catalog.js');

// Вычисляем количество товаров в категории
$categoryQuantity = $products->getTotalItemCount();

/* =============================================================================
 *  SEO оптимизация
 * =============================================================================
 */

$cityPath = (($city instanceof City)
	? 'в ' . ((empty($city->prepositionalCase))
		? 'городе ' . $city->name: $city->prepositionalCase)
	: '');

if ($category->isRoot()) {
	$categoryName = 'Товары для дома';
	$catNameGen = 'Товаров для дома';
} else {
	$categoryName = $category->name;
	$catNameGen = $category->genitiveCase;
}

$this->pageTitle = $categoryName . ' по низким ценам, каталог интернет магазинов ' . $catNameGen . ' ' . $cityPath;

$this->description = 'Каталог из '
	. CFormatterEx::formatNumeral($categoryQuantity, array('наименования', 'наименований', 'наименований'))
	. ' ' . $catNameGen . ' от '
	. CFormatterEx::formatNumeral($storeCount, array('интернет магазина', 'интернет магазинов', 'интернет магазинов'))
	. ' на MyHome, купить ' . $categoryName . ' в '
	. CFormatterEx::formatNumeral($storeCount, array('магазине', 'магазинах', 'магазинах'))
	. '  по низким ценам' . ' ' .$cityPath;

$this->keywords = 'каталог ' . $catNameGen . ' с низкими ценами, купить ' . $categoryName;




/* -----------------------------------------------------------------------------
 *  Далее идут модификация SEO фигней для спец условий
 * -----------------------------------------------------------------------------
 */

if (isset($selected['vendor_country']) && $selected['vendor_country'] > 0) {
	
	/*
	 * Модифицируем в случае выбора страны производителя
	 */

	$country = Country::model()->findByPk($selected['vendor_country']);
	if ($country) {

		$this->pageTitle = $categoryName
			. ' ('.$country->name.')'
			.' — '
			. $categoryName . ' — MyHome.ru';


		$this->description = $categoryName
			. ' ('.$country->name.')'
			. ' в каталоге товаров для дома на MyHome.ru.'
			. ' Подбор '
			. $category->genitiveCase
			. ' по цене, производителю, типу, стилю, цвету, материалу, адреса магазинов.';

		$this->keywords = $categoryName
			. ' ' . $country->name
			. ', ' . $categoryName
			. ', каталог товаров, товары для дома, майхоум, myhome, май хоум, myhome.ru';

		$h1 = $categoryName . ' (' . $country->name . ')';
	}

}

if (isset($selected['vendors']) && count($selected['vendors']) == 1) {

	/*
	 * Модифицируем в случае, когда выбран ровно один производитель.
	 */

	$vendor = Vendor::model()->findByPk($selected['vendors'][0]);
	if ($vendor) {
		$this->pageTitle = $categoryName . ' ' . $vendor->name
			. ' — ' . $categoryName . ' — MyHome.ru';

		$h1 = $categoryName . ' ' . $vendor->name;

		$this->description = $categoryName . ' ' . $vendor->name
			. ' в каталоге товаров для дома на MyHome.ru.'
			. ' Подбор ' . $category->genitiveCase . ' по цене,'
			. ' производителю, типу, стилю, цвету, материалу, адреса магазинов';

		$this->keywords = $categoryName . ' ' . $vendor->name . ','
			. ' ' . $categoryName . ','
			. ' ' . $vendor->name . ', каталог товаров,'
			. ' товары для дома, майхоум, myhome, май хоум, myhome.ru';
	}

}

// ==<<<<==== SEO ==============================================================

?>

<!-- Page content wrap //-->
<div class="-grid-wrapper page-content -gutter-top">
	<div class="-grid">
		<!-- Right sidebar //-->
		<div class="-col-3">
			<div class="page-sidebar">

				<?php if ($category->name == 'root') { ?>
					<span class="-strong -gutter-bottom -block">Категория</span>
				<?php } ?>

				<ul class="level-1 -menu-block -light-gray -gutter-bottom-dbl">
					<?php if ($category->name != 'root') { ?>
						<li class="-icon-arrow-down level-1"><a href="/catalog">Все категории</a></li>
					<?php } ?>

                    <?php if ($category->name != 'root') : ?>
                        <span style="display: none;">
                            <?php foreach(Category::getRootChildren($category) as $cat) : ?>
                                <?php echo CHtml::openTag('li', ['class'=>'level-2']); ?>
                                    <?php echo CHtml::link($cat->name, $cat->getLink($cat->id)); ?>
                                <?php echo CHtml::closeTag('li'); ?>
                            <?php endforeach; ?>
                        </span>
                    <?php endif; ?>

					<?php
					/* -----------------------------
					 *  Категории товаров
					 * -----------------------------
					 */
					foreach ($categories as $cat) {

						$activeCls = $iconCls = '';

						if ($category->id == $cat->id) {
							if ($cat->level < 4) {
								$activeCls = ' current strong ';
							} else {
								$activeCls = ' current ';
							}
						}

						if ($cat->level < 4 && !$cat->isLeaf()) {
							$iconCls = '-icon-arrow-down';
						}

						if ($category->name == 'root') {
							$level = 0;
							$iconCls = '';
						} else {
							$level = $cat->level;
						}

						echo '<li class="'.$iconCls.' '.$activeCls.' level-'.$level.' ">'
							.'<a href="'.$cat->getLink($cat->id).'">'.$cat->name.'</a>'
							.'</li>'.' ';
					}
					?>
				</ul>

				<?php
				/* -------------------------------------
				 *  Фильтр
				 * -------------------------------------
				 */
				$this->widget('catalog.components.widgets.CatFilter.CatFilter', array(
					'category' => $category,
					'selected' => $selected,
					'city'     => $city,
					'viewName' => 'filterGrid',
					'viewType'=> $viewType,
				)); ?>

			</div>
			<div class="-gutter-top-dbl -gutter-bottom-dbl -relative">

			</div>
			<div class="-gutter-top-dbl -gutter-bottom-dbl -relative">
				<?php $this->widget('application.components.widgets.banner.BannerWidget', array(
					'section'=>Config::SECTION_CATALOG,
					'type'=>2,
				)); ?>
			</div>

			<div class="-gutter-top-dbl -gutter-bottom-dbl">
				<?php
				// Profit
                Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_vertical');				?>

                <?php
                // Google adsense
                $this->renderPartial('//widget/google/adsense_160x600_product_list');
                ?>

            </div>

		</div>
		<!-- EOF Right sidebar //-->

		<!-- Main block //-->
		<div class="-col-9">

			<?php
			$cityName = '';
			if ($city instanceof City) {
				if (!empty($city->prepositionalCase)) {
					$cityName = 'в ' . $city->prepositionalCase;
				} else {
					$cityName = 'в городе ' . $city->name;
				}
			}
			$categoryName = ($category->name == 'root') ? 'Товары для дома' : $category->name;

			$seo = $this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags', array(
				'defaultH1' => $categoryName . ' ' . $cityName
			));

			$cityPopup = $this->widget('catalog.components.widgets.CityPopup.CityPopupWidget', array(
				'city' => $city,
			));


			/* ---------------------------------------------
			 *  Хлебные крошки
			 * ---------------------------------------------
			 */
			$this->widget('catalog.components.widgets.CatBreadcrumbs',
				array(
					'category' => $category,
					'pageName' => (isset($h1)) ? $h1 : $seo->getH1(),
					'insideH1' => '<sup class="-large">'.$categoryQuantity.'</sup>',
					'afterH1'  => $cityPopup->getHtml(),
					'homeLink' => null,
					'showDesc' => true,
				)
			);
			if (Yii::app()->getUser()->getState('showCreteStoreBlock', true)) : ?>
			<noindex>
			<div class="-grid">
				<div class="-col-9 -relative -large -tinygray-bg -inset-all -border-radius-all -strong -gutter-bottom-dbl promo-button">
					<span></span>
					Хотите добавить свои товары в каталог? <a class="-skyblue" href="/advertising/rates?request=true">Отправьте заявку</a>
					<i class="-icon-cross - -icon-only -push-right"></i>
				</div>
				<script>
					CCommon.hidePromoButton();
				</script>
			</div>
			</noindex>
			<?php endif;

			/* ---------------------------------------------
			 *  Лента логотипов
			 * ---------------------------------------------
			 */
			$this->widget('catalog.components.widgets.TapeStore.TapeStoreWidget', array(
				'categoryId' => $category->id,
				'cityId'     => ($city) ? $city->id : 0
			));
            Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_above');
            ?>

			<?php
			echo CHtml::openTag('div', array(
				'class' => ($viewType == 2) ? 'goods-list-s -grid' : 'goods-list-xl -grid'
			));

			/* -----------------------------------------------------
			 *  Карточки товаров
			 * -----------------------------------------------------
			 */
			$data = $products->getData();

			if (!empty($data)) {

				$qt = count($data);

				if ($viewType == 2) {
					// Трех-колоночный вариант
					$center =  intval($qt/2);
					while($center % 3 != 0) {$center++;}

					foreach ($data as $index => $item) {
						$this->renderPartial('_product3colGrid', array(
							'data'  => $item,
							'index' => $index,
							'center'=> $center,
							'qt'    => $qt,
							'imageFormat'=>$category->image_format,
						));
					}

				} else {

					$center =  intval($qt/2);
					while($center % 2 != 0) {$center++;}

					$i = 0;
					// Двух колоночный вариант
					foreach ($data as $index=>$item) {
						$i = $i + 1;

						if($i&1){
							$class = '-col-wrap -gutter-left-hf -gutter-right -gutter-bottom-dbl';
						}
						else{
							$class = '-col-wrap -gutter-bottom-dbl';
						}

						$this->renderPartial('_product2colGrid', array(
							'data' => $item,
							'index' => $index,
							'center'=> $center,
							'qt'    => $qt,
							'class' => $class,
							'imageFormat'=>$category->image_format,
						));

					}

				}

			} else {

				// Выводим текст для пустых категорий

				echo $htmlEmpty;

			}

			echo CHtml::closeTag('div');
            echo '<script>catalog.setItemHeight();</script>';
            Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_under');
            ?>

			<?php if (!empty($data)) { ?>

				<hr>

				<div class="-grid">
					<div class="-col-9">
						<!--<div class="-col-wrap -gutter-right layout-icons">
							<span data-layout="1" class="-gray <?php /*if ($viewType==1) echo 'current'; */?>"><i class="-icon-layout-s"></i></span>
							<span data-layout="2" class="-gray <?php /*if ($viewType==2) echo 'current'; */?>"><i class="-icon-layout-small-s"></i></span>
						</div>-->

						<!-- sorting -->
						<?php // Блок сортировки
						$this->widget('catalog.components.widgets.CatFilterSortGrid', array(
							'items'=>array(
								array('name'=>'price', 'text'=>'цене'),
								array('name'=>'date', 'text'=>'новизне', 'order' => 'desc'),
							),
							'defaultOrder' => 'desc',
							'defaultSort' => 'default',
							'renderDefault' => true,
						));
						?>
						<!-- eof sorting -->

						<div class="-col-wrap -small items-qnt">
							На странице
							<?php
							echo CHtml::dropDownList('pageSize', $pageSize, Config::$productFilterPageSizes);
							?>
						</div>
						<!-- pager -->

						<div class="-col-wrap -inline pagination" style="display: inline; float:right;">
							<?php $this->widget('application.components.widgets.CustomListPager', array(
								'pages'          => $products->pagination,
								'htmlOptions'    => array('class' => '-menu-inline -pager'),
								'maxButtonCount' => 5,
							)); ?>
						</div>
						<!-- eof pager -->
					</div>
				</div>
			<?php } ?>


			<script>
				catalog.initFilter();
                $("li.level-1").click(function(){
                    var rootChildren = $("ul.-menu-block > span");
                    rootChildren.toggle(rootChildren.css('display') == 'none');
                    return false;
                });
			</script>

			<div class="-gutter-top-dbl">
				<!-- Яндекс.Маркет -->
				<?php $this->renderPartial('//widget/yandex/market_horizontal_3'); ?>

			</div>

			<div class="-gutter-top-dbl">
				<!-- R-123646-1 Яндекс.RTB-блок  -->
				<?php $this->renderPartial('//widget/yandex/rtb'); ?>
			</div>

			<?php if($category->seo_bottom_desc) : ?>
				<div class="meta-tags -gutter-top-dbl -inset-top-dbl"> <p><?php echo nl2br($category->seo_bottom_desc) ?> </p></div>
			<?php endif; ?>

		</div>
		<!-- EOF Main block //-->
	</div>
</div>
<!-- EOF Page content wrap //-->