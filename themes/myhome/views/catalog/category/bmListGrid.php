<?php
/**
 * @var Category[] $categories Список категорий относительно активной $category
 */

/* =============================================================================
 *  SEO оптимизация
 * =============================================================================
 */

$cityPath = ( ($city instanceof City) ? ' в '. ( (empty($city->prepositionalCase)) ? 'городе '.$city->name : $city->prepositionalCase) : '');
if($category->isRoot())
{
	$parentName = '';
	$categoryName = 'Каталог товаров ТВК Большая Медведица';
}
else
{
	$parentName = ($category->parent()->find()->isRoot()) ? '' : ' — '.$category->parent()->find()->name;
	$categoryName = $category->name;
}

$this->pageTitle = $categoryName.$parentName.$cityPath.'  — MyHome.ru';

$this->keywords = 'товары для дома, товары для ремонта '
	. ', товары для дома, товары для ремонта, каталог товаров для дома,'
	. ' каталог товаров, мебель, сантехника, двери, электрика, освещение,'
	. ' отделочные материалы, товары, барнаул, майхоум, myhome, myhome.ru';


//$this->description = $categoryName.$cityPath.

if ($city instanceof City) {
	$this->keywords = 'товары для дома ' . $city->name . ', товары для ремонта ' . $city->name
		. ', товары для дома, товары для ремонта, каталог товаров для дома,'
		. ' каталог товаров, мебель, сантехника, двери, электрика, освещение,'
		. ' отделочные материалы, товары, барнаул, майхоум, myhome, myhome.ru';
}

$categoryName = ($category->isLeaf())
	? $category->name
	: 'Товары для дома';

if (isset($selected['vendor_country']) && $selected['vendor_country'] > 0) {

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

		$categoryQuantity = $products->getTotalItemCount();
	}

} else {
	$categoryQuantity = $products->getTotalItemCount();
}

// ==<<<<==== SEO ==============================================================

?>
<div class="-grid-wrapper page-content -gutter-top">
	<div class="-grid">
		<?php
		$mall = Cache::getInstance()->mallBuild;
		$promo = MallPromo::model()->findAllByAttributes(array('mall_id'=>$mall->id, 'status'=>MallPromo::STATUS_ACTIVE), array('order'=>'position ASC'));

		if (!empty($promo)) :

			?>
		<div class="-col-12 -slider bm-promo-slider">
			<ul class="-slider-content">
				<?php
				/** @var $item MallPromo */
				foreach($promo as $item) {
					echo CHtml::tag('li', array(),
						CHtml::link(
							CHtml::image('/'.$item->getPreview(MallPromo::$preview['crop_940x400']), $item->name),
							$item->url,
							array('target'=>'_blank')
						)
					);
				}
				?>
			</ul>
			<!-- slider preview -->
			<div class="-slider-preview">
				<?php
				for ( $i=0; $i<count($promo); $i++ ) {
					$htmlOpt = array();
					if ($i==0)
						$htmlOpt['class'] = 'current';
					echo CHtml::tag('span', $htmlOpt, '');
				} ?>
			</div>
			<!-- eof slider preview -->
			<!-- slider controls -->
			<div class="-slider-controls">
				<span class="-slider-prev -disabled"></span>
				<span class="-slider-next"></span>
			</div>
			<!-- eof slider controls -->
		</div>
		<script>
			CCommon.slider($('.-slider'));
		</script>
		<?php endif; ?>

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
					'enableStyleColorFilter' => !$category->isLeaf() ? true : false,
					'viewType'=> $viewType,
				)); ?>

			</div>

			<?php $folders = CatFolders::getRandomFolder(); ?>
			<?php if($folders) : ?>

			<p class="-gutter-top-dbl -inset-top-dbl"><a href="<?php echo Yii::app()->params->bmHomeUrl.'/catalog/folders/list' ?>"> <span class="-large -strong">Спецпредложения</span></a>
				<span class="-small -gray -gutter-left-qr"><?php echo CatFolders::getCount() ?></span></p>
			<?php foreach($folders as $folder) : ?>
				<div class="-grid folders-list">
					<div class="-col-3 folder">
						<a class="folder-picture" href="<?php echo $folder->getLink(); ?>">
							<?php
							$product = CatFolderItem::getFirstModel($folder->id);
							echo CHtml::image(
								'/' . $product->cover->getPreviewName(Product::$preview['crop_220']), $product->name, array('class' => '-quad-200'));
							?>
							<span><?php echo  $folder->name; ?> </span>
						</a>
						<p class="-gray -gutter-top-hf">
							<span class="-small"><?php echo CFormatterEx::formatNumeral($folder->count, array('товар', 'товара', 'товаров')); ?></span>
						</p>
					</div>
				</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<!-- EOF Right sidebar //-->

		<!-- Main block //-->
		<div class="-col-9">

			<?php

			$categoryName = ($category->name == 'root') ? 'Каталог товаров Большой Медведицы' : $category->name;

			$seo = $this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags', array(
				'defaultH1' => $categoryName
			));

			/*$cityPopup = $this->widget('catalog.components.widgets.CityPopup.CityPopupWidget', array(
				'city' => $city,
			));*/


			/* ---------------------------------------------
			 *  Хлебные крошки
			 * ---------------------------------------------
			 */
			$this->widget('catalog.components.widgets.CatBreadcrumbs',
				array(
					'category' => $category,
					'pageName' => ( (isset($h1)) ? $h1 : $seo->getH1() ).'<sup class="-large">'.$categoryQuantity.'</sup>',
					'afterH1'  => '',//$this->renderPartial('//widget/bmLogo', array('bmCatalog'=>true), true),
					'homeLink' => '<a href="/">Каталог ТВК «Большая Медведица»</a>',
					'mallCatalogClass' => true,
				)
			);



			/* ---------------------------------------------
			 *  Лента логотипов
			 * ---------------------------------------------
			 */
			/*$this->widget('catalog.components.widgets.TapeStore.TapeStoreWidget', array(
				'categoryId' => $category->id,
				'cityId'     => ($city) ? $city->id : 0
			));*/

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

				if ($viewType == 2) {
					// Трех-колоночный вариант
					$qt = count($data);
					foreach ($data as $index => $item) {
						$this->renderPartial('_product3colGrid', array(
							'data'  => $item,
							'index' => $index,
							'qt'    => $qt,
							'addToFolder'=>$addToFolder,

						));
					}

				} else {
					// Двух колоночный вариант
					foreach ($data as $item) {
						$this->renderPartial('_product2colGrid', array('data' => $item,'addToFolder'=>$addToFolder));
					}
				}

			} else {

				// Выводим текст для пустых категорий

				echo $htmlEmpty;

			}

			echo CHtml::closeTag('div');

			echo '<script>catalog.setItemHeight();</script>';
			?>

			<?php if (!empty($data)) { ?>

			<hr>


			<div class="-grid">
				<div class="-col-9">
					<div class="-col-wrap -gutter-right layout-icons">
						<span data-layout="1" class="-gray <?php if ($viewType==1) echo 'current'; ?>"><i class="-icon-layout-s"></i></span>
						<span data-layout="2" class="-gray <?php if ($viewType==2) echo 'current'; ?>"><i class="-icon-layout-small-s"></i></span>
					</div>

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

					<div class="-col-wrap -push-right">
						<?php $this->widget('application.components.widgets.CustomListPager', array(
						'pages'          => $products->pagination,
						'htmlOptions'    => array('class' => '-menu-inline -inline -pager'),
						'maxButtonCount' => 5,
					)); ?>
					</div>
					<!-- eof pager -->
				</div>
			</div>

			<?php } ?>
			<script>
				catalog.initFilter();
			</script>

		</div>
		<!-- EOF Main block //-->
	</div>


</div>