<?php Yii::app()->clientScript->registerScriptFile('/js/newFilter.js'); ?>

<?php
/* ----------------------------------
 *  SEO - большой, жирный кусок seo
 * ----------------------------------
 */

// Заголовок по-умолчанию
$this->pageTitle = 'Интерьеры — MyHome.ru';

// Выделяем названия выбранных помещений в массив
$rooms = ( ! empty($selected['room']))
	 ? array_map('trim', explode(',', trim($selected['room'], ' ,')))
	 : array();

if (count($rooms) == 1)
{
	/**
	 * Массив падежей для типов помещений
	 * array(
	 * 	0 - родительный падеж, множественное число
	 * 	1 - родительный падеж, единственное число
	 * 	2 - именитльеный падеж, множественное число
	 * )*/
	$seoCase = array(
		'Ванная'              => array('Ванных', 	'Ванной', 	'Ванные'),
		'Гостиная'            => array('Гостиных', 	'Гостинной', 	'Гостинные'),
		'Кухня'               => array('Кухонь', 	'Кухни', 	'Кухни'),
		'Спальня'             => array('Спален', 	'Спальни', 	'Спальни'),
		'Детская'             => array('Детских', 	'Детской', 	'Детские'),
		'Столовая'            => array('Столовых', 	'Столовой', 	'Столовые'),
		'Мансарда'            => array('Мансард', 	'Мансарды', 	'Мансарды'),
		'Квартира-студия'     => array('Квартир-студий', 'Квартиры-студии', 'Квартиры-студии'),
		'Кабинет; Библиотека' => array(
			array('Кабинетов', 'Библиотек'),
			array('Кабинета', 'Библиотеки'),
			array('Кабинеты', 'Библиотеки')
		),
		'Коридор; Холл'       => array(
			array('Коридоров', 'Холлов'),
			array('Коридора', 'Холла'),
			array('Коридоры', 'Холлы')
		),
	);

	if (isset($seoCase[$rooms[0]]))
	{
		// Получаем набор падежей для конкретного помещения
		$nameRoom = $seoCase[$rooms[0]];

		if ( ! is_array($nameRoom[0]))
		{       // Если помещение состоит из одного слова

			$room_0_lower = mb_strtolower($nameRoom[0], 'UTF-8');
			$room_1_lower = mb_strtolower($nameRoom[1], 'UTF-8');
			$room_2_lower = mb_strtolower($nameRoom[2], 'UTF-8');

			$this->pageTitle = 'Интерьеры '.$nameRoom[0].' с Фото — Галерея Интерьеров — MyHome.ru';
			$this->description = 'Дизайн интерьера '.$room_0_lower.' с фото и описанием — лучшие идеи дизайна '.$room_0_lower.' в галерее интерьеров жилых помещений на МайХоум.ру';
			$this->keywords = 'интерьеры '.$room_0_lower
				.', интерьеры '.$room_2_lower
				.', интерьеры '.$room_0_lower.' фото'
				.', дизайн интерьера '.$room_1_lower
				.', дизайн '.$room_1_lower
				.', интерьер '.$room_1_lower.' фото'
				.', интерьеры жилых помещений, галерея интерьеров, майхоум, myhome, myhome.ru';

			$h1 = 'Интерьеры '.$room_0_lower;
		}
		else
		{	// Если помещение состоит из двух слов

			$room_00_lower = mb_strtolower($nameRoom[0][0], 'UTF-8');
			$room_01_lower = mb_strtolower($nameRoom[0][1], 'UTF-8');
			$room_10_lower = mb_strtolower($nameRoom[1][0], 'UTF-8');
			$room_11_lower = mb_strtolower($nameRoom[1][1], 'UTF-8');
			$room_20_lower = mb_strtolower($nameRoom[2][0], 'UTF-8');
			$room_21_lower = mb_strtolower($nameRoom[2][1], 'UTF-8');

			$this->pageTitle = 'Интерьеры '.$nameRoom[0][0].', '.$nameRoom[0][1].' с Фото — Галерея Интерьеров — MyHome.ru';
			$this->description = 'Дизайн интерьера '.$room_00_lower.', '.$room_01_lower.' с фото и описанием — лучшие идеи дизайна '.$room_00_lower.', '.$room_01_lower.' в галерее интерьеров жилых помещений на МайХоум.ру';
			$this->keywords =
				 'интерьеры '.$room_00_lower
				.', интерьеры '.$room_01_lower
				.', интерьеры '.$room_20_lower
				.', интерьеры '.$room_21_lower
				.', интерьеры '.$room_10_lower
				.', интерьеры '.$room_11_lower
				.', дизайн интерьера '.$room_10_lower
				.', дизайн интерьера '.$room_11_lower
				.', дизайн '.$room_10_lower
				.', дизайн '.$room_11_lower
				.', интерьер '.$room_10_lower.' фото'
				.', интерьер '.$room_11_lower.' фото'
				.', интерьеры жилых помещений, галерея интерьеров, майхоум, myhome, myhome.ru';

			$h1 = 'Интерьеры '.$room_00_lower.', '.$room_01_lower;
		}
	}
}

$breadCrumbLinks = array('Идеи<span class="text_block"> для интерьера</span>' => array('/idea'));
if ( ! empty($rooms))
	$breadCrumbLinks['Галерея интерьеров'] = array('/idea/interior');

// Если у нас в фильтре ничего не выбрано, т.е. находимся на главной странице раздела
if (empty($selected['room']) && empty($selected['color-data']) && empty($selected['style']) && empty($selected['tags-list']))
{
	$this->pageTitle = 'Галерея Интерьеров Квартир, Домов — Идеи Интерьеров — MyHome.ru';

	$this->description = 'Галерея интерьеров жилых помещений с фото и описанием: интерьеры кухни, спальни, детской,
			      гостиной, ванной на MyHome.ru. Уникальные идеи интерьеров в разных стилевых и цветовых решениях';

	$this->keywords = 'галерея интерьеров, интерьеры квартир, интерьеры домов, интерьеры ванной, интерьеры кухни,
			   интерьеры детской, интерьеры спальни, дизайн интерьеров, идеи интерьеров, майхоум, myhome, myhome.ru';

	$h1 = 'Галерея жилых интерьеров';
}


// SMO оптимизация
Yii::app()->openGraph->title = isset($h1) ? $h1 : 'Интерьеры';

foreach ($interiorProvider->getData() as $index=>$data) {
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
		'links'=> $breadCrumbLinks,
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
	$this->widget('idea.components.IdeasCatalogBar.IdeasCatalogBar', array(
		'ideaCount'  => $interiorProvider->getTotalItemCount(),
		'sortType'   => $sortType,
		'pageSize'   => $pagesize,
		'selected'   => $selected,
		'objectType' => $objectType,
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

	<ul class="ideas-seeAlso" style="padding-left: 0; text-align: left;">
        <span>Смотрите также:</span>
        <li><a href="/products/hygiene/?utm_content=cat-idea-list-sidebar">Мебель для ванной</a></li>
        <li><a href="/products/bathroom_furniture/?utm_content=cat-idea-list-sidebar">Сантехника</a></li>
        <li><a href="/products/kitchen_furniture/?utm_content=cat-idea-list-sidebar">Мебель для кухни</a></li>
        <li><a href="/products/living_headset/?utm_content=cat-idea-list-sidebar">Гостиные</a></li>
        <li><a href="/products/sofas/?utm_content=cat-idea-list-sidebar">Диваны</a></li>
        <li><a href="/products/juvenile/?utm_content=cat-idea-list-sidebar">Детская мебель</a></li>
        <li><a href="/products/hall_furniture_sets/?utm_content=cat-idea-list-sidebar">Прихожие</a></li>
    </ul>
    <script async type='text/javascript' src='//s.luxupcdna.com/t/common_400.js'></script> <script class='__lxGc__' type='text/javascript'> ((__lxGc__=window.__lxGc__||{'s':{},'b':0})['s']['_207099']=__lxGc__['s']['_207099']||{'b':{}})['b']['_599687']={'i':__lxGc__.b++}; </script> <br id="lxBlockPlaceholder"/> <script type="text/javascript" src='//is.luxup.ru/t/fst/myhome/sticky.js'></script> <script type="text/javascript" src='//is.luxup.ru/t/fst/myhome/sticky_2.js'></script>

</div>

<div id="right_side">
	<?php
	/* ---------------------------------------------------------------------
	 *  Список карточек идей
	 * ---------------------------------------------------------------------
	 */
	$this->widget('idea.components.IdeasList.IdeasList', array(
		'dataProvider'       => $interiorProvider,
		'availablePageSizes' => Config::$ideasPageSizes,
		'itemView'           => '_interiorContentItem',
		'sortType'           => $sortType,
		'pageSize'           => $pagesize,
		'search'             => $search,
		'emptyText'          => 'У нас пока что нет идей, отвечающих такому запросу. Но мы их обязательно<br> найдем, обещаем. Попробуйте изменить или <a href="/idea/interior/catalog">сбросить параметры фильтра</a>',
        'viewNumber'         => rand( 12, 28 ),
		'bannerText'         => $this->renderPartial('//widget/banner/_ideaListBanner', false, true),
//        'bannerText'         => $this->renderPartial('//widget/banner/_ideaBanner', Config::getBannerData(), true),
	));
	?>

	
    <?php
    // Between
    $this->renderPartial('//widget/google/adsense_728x90_idea_list');
    ?>

</div>
<div class="clear"></div>
<div class="spacer-30"></div>