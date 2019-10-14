<?php

/* ----------------------------------
 *  SEO - большой, жирный кусок seo
 * ----------------------------------
 */

// Заголовок по-умолчанию
$this->pageTitle = 'Интерьеры — MyHome.ru';

// Выделяем типы строений в массив
$builds = ( ! empty($selected['build_type']))
	? array_map('trim', explode(',', trim($selected['build_type'], ' ,')))
	: array();

/**
 * Хранит ID типа помещения.
 * Это значение перебрасывается во вьюшку для отрисовки списка общественных
 * интерьеров, чтобы вывести красивые URL'ы
 * Нужен для ситуации, когда выбрано только одно помещение
 */
$oneBuild = null;

if (count($builds) == 1)
{
	$oneBuild = $builds[0];

	/*
	 * Массив соответсвий ID строений и их падежных форм
	 * array(
	 * 	0 - родительный падеж, множественное число
	 * 	1 - иментиельный падеж, единственное число
	 * )
	 */
	$seoCase = array(
		'232' => array('Офисов', 'Офис'),
		'224' => array('Административных зданий', 'Административное здание'),
		'225' => array('Торгово-выставочных комплексов', 'Торгво-выставочный центр'),
		'226' => array('Развлекательных центров', 'Развлекательный центр'),
		'227' => array('Киноконцертных комплексов, театров', 'Киноконцертный комплекс, театр'),
		'228' => array('Ресторанов, кафе, баров', 'Ресторан, кафе, бар'),
		'229' => array('Салонов красоты, саун, spa', 'Салон красоты, сауна, spa'),
		'230' => array('Спортивных сооружений', 'Спортивное сооружение'),
		'231' => array('Промышленных объектов', 'Промышленный объект'),
	);

	if (isset($seoCase[$builds[0]]))
	{
		// Получаем набор падежей для конкретного помещения
		$nameBuild = $seoCase[$builds[0]];

		$build_0_lower = mb_strtolower($nameBuild[0], 'UTF-8');
		$build_1_lower = mb_strtolower($nameBuild[1], 'UTF-8');

		$this->pageTitle = 'Интерьеры '.$build_0_lower.' с фото — Общественные интерьеры — MyHome.ru';
		$this->description = 'Лучшие идеи интерьеров '.$build_0_lower.' с фото и описанием в галерее интерьеров общественных зданий на МайХоум.ру';
		$this->keywords = 'интерьеры '.str_replace(',', '', $build_0_lower)
			.', интерьер '.str_replace(',', '', $build_0_lower)
			.', общественные интерьеры, интерьер '.str_replace(',', '', $build_1_lower)
			.', интерьеры общественных зданий, нежилые интерьеры, идеи интерьеров, майхоум, myhome, myhome.ru';

		$h1 = 'Интерьеры '.$build_0_lower;
	}
}

$breadCrumbLinks = array(
	'Идеи<span class="text_block"> для интерьера</span>' => array('/idea')
);
if ( ! empty($builds))
	$breadCrumbLinks['Общественные интерьеры'] = array('/idea/interiorpublic');


// Если у нас в фильтре ничего не выбрано, т.е. находимся на главной странице раздела
if ($selected['search'] == false && empty($builds))
{
	$this->pageTitle = 'Общественные интерьеры — Идеи Интерьеров — MyHome.ru';

	$this->description = 'Общественные интерьеры с фото и описанием от ведущих дизайнеров интерьеров на MyHome.ru.
			      Галерея интерьеров офисов, ресторанов, развлекательных центров, салонов красоты';

	$this->keywords = 'общественные интерьеры, нежилые интерьеры, дизайн общественных интерьеров,
			   интерьеры общественных зданий, интерьеры общественных помещений, интерьеры офисов,
			   интерьеры кафе, интерьеры ресторанов, интерьеры салонов красоты, дизайн интерьеров,
			   идеи интерьеров, майхоум, myhome, myhome.ru';
	$h1 = 'Интерьеры общественных зданий';
}


// SMO оптимизация
Yii::app()->openGraph->title = isset($h1) ? $h1 : '';

foreach($interiorpublicProvider->getData() as $index=>$data) {
	Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$data->getPreview(config::$preview['crop_210']);

	if ($index > 10) {
		break;
	}
}

Yii::app()->openGraph->renderTags();



// Подключаем виджед для SEO оптимизации вручную
$this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags');
?>

<div class="pathBar">
	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
	'links'=>$breadCrumbLinks,
	'encodeLabel' => false
));?>

	<h1><?php $this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags', array(
		'renderH1' => true,
		'defaultH1' => isset($h1) ? $h1 : 'Интерьеры'
	));?></h1>

	<div class="spacer"></div>
</div>
<div id="left_side">
	<?php
	/* ---------------------------------------------------------------------
	 *  Фильтр списка
	 * ---------------------------------------------------------------------
	 */
	$this->widget('idea.components.InteriorpublicCatalogBar.InteriorpublicCatalogBar', array(
		'ideaCount' => $interiorpublicProvider->getTotalItemCount(),
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
		'dataProvider'       => $interiorpublicProvider,
		'availablePageSizes' => Config::$ideasPageSizes,
		'itemView'           => '_interiorpublicItem',
		'sortType'           => $selected['sortType'],
		'pageSize'           => $selected['pageSize'],
		'search'             => $selected['search'],
		'oneBuild'           => $oneBuild,
		'emptyText'          => 'У нас пока что нет идей, отвечающих такому запросу. Но мы их обязательно<br> найдем, обещаем. Попробуйте изменить или <a href="/idea/interiorpublic/catalog">сбросить параметры фильтра</a>',
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