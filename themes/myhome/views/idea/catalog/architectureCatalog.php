<?php $this->pageTitle = 'Архитектура — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>

<?php
// SMO оптимизация
Yii::app()->openGraph->title = 'Архитектура';

/** @var $data Architecture */
foreach($architectureProvider->getData() as $index=>$data) {
	Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$data->getPreview('crop_210');

	if ($index > 10) {
		break;
	}
}

Yii::app()->openGraph->renderTags();
?>



<?php
// Подключаем виджед для SEO оптимизации вручную
$this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags');
?>

<div class="pathBar">
	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(
			'Идеи<span class="text_block"> для интерьера</span>' => array('/idea'),
		),
		'encodeLabel' => false
	));?>

	<h1><?php $this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags', array(
		'renderH1' => true,
		'defaultH1' => 'Архитектура'
	));?></h1>

	<div class="spacer"></div>
</div>
<div id="left_side">
	<?php
	/* ---------------------------------------------------------------------
	 *  Фильтр списка
	 * ---------------------------------------------------------------------
	 */
	$this->widget('idea.components.ArchitectureCatalogBar.ArchitectureCatalogBar', array(
		'ideaCount' => $architectureProvider->getTotalItemCount(),
		'selected' => $selected,
	));
	?>

	<div class="-gutter-top-dbl -gutter-bottom-dbl -relative">

	</div>

	<div class="-gutter-top-dbl -gutter-bottom-dbl -relative"><?php $this->widget('application.components.widgets.banner.BannerWidget', array(
		'controller' => $this,
		'type'       => 2
	)); ?></div>

	<div class="-gutter-top-dbl -gutter-bottom-dbl -relative">
		<?php
		// Google adsense
		$this->renderPartial('//widget/google/adsense_160x600_idea_list');
		?>
	</div>
</div>

<div id="right_side">
	<?php
	/* ---------------------------------------------------------------------
	 *  Список карточек идей
	 * ---------------------------------------------------------------------
	 */
	$this->widget('idea.components.IdeasList.IdeasList', array(
		'dataProvider'       => $architectureProvider,
		'availablePageSizes' => Config::$ideasPageSizes,
		'itemView'           => '_architectureItem',
		'sortType'           => $selected['sortType'],
		'pageSize'           => $selected['pageSize'],
		'search'             => $selected['search'],
		'emptyText'          => 'У нас пока что нет идей, отвечающих такому запросу. Но мы их обязательно<br> найдем, обещаем. Попробуйте изменить или <a href="/idea/architecture/catalog">сбросить параметры фильтра</a>',
		'bannerText'         => $this->renderPartial('//widget/banner/_ideaBanner', Config::getBannerData(), true),
	));
	?>

	<?php
	// Between
	$this->renderPartial('//widget/google/adsense_728x90_idea_list');
	?>
</div>
<div class="clear"></div>
<div class="spacer-30"></div>