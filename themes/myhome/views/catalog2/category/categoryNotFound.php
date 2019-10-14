<?php
/** @var $cs CClientScript */
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile('/js/CCatalog.js');
$cs->registerCssFile('/css/catalog.css');

$cs->registerScript('initial-run', '
        $(function(){
                cat.initBreadCrumbs();
        });
', CClientScript::POS_READY);

$this->pageTitle = 'Товары — MyHome.ru';

// Подключаем виджед для SEO оптимизации вручную
$seo = $this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags', array(
	'defaultH1' => 'Товары'. ( ($city instanceof City) ? ' в '. ( (empty($city->prepositionalCase)) ? 'городе '.$city->name : $city->prepositionalCase) : '')
));

$cityPopup = $this->widget('catalog.components.widgets.CityPopup.CityPopupWidget', array(
	'city'=>$city,
));
?>

<?php $this->widget('catalog2.components.widgets.CatBreadcrumbs', array(
	'category' => Category::getRoot(),
	'pageName' => $seo->getH1(),

	'afterH1'  => $cityPopup->getHtml(),
)); ?>

<!-- Списки категорий //-->
<div class="catalog_index goods_cat_list -gutter-bottom-dbl">
	<!-- Категория со списком подкатегорий //-->
	<div class="-grid-wrapper">
		<div class="-grid">
			<div class="-col-12">
				<div class="-col-wrap -gutter-left-qr">
					<h2 class="-gutter-top-null">К сожалению, в нашем каталоге еще нет товаров вашего города</h2>
					<p class="-large -gutter-top">Рекомендуем вам посмотреть <a onclick="CCommon.setUrl('catalog'); ">каталог всех товаров</a>, представленных на MyHome</p>
				</div>
			</div>
		</div>
	</div>

	<script>cat.initCatalogMainPage();</script>
</div>
