<?php $this->pageTitle = 'Календарь событий — MyHome'; ?>

<?php Yii::app()->getClientScript()->registerCssFile('/css/jquery-ui-1.8.18.custom.css'); ?>
<?php Yii::app()->getClientScript()->registerScriptFile('/js/jquery-ui-1.8.18.custom.min.js'); ?>

<?php
// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = 'Календарь событий MyHome';
Yii::app()->openGraph->description = 'Выставки, ярмарки, конкурсы, мастер-классы, и другие мероприятия для специалистов '
	.'и людей интересующихся дизайном, архитектурой и обустройством дома';

foreach($eventProvider->getData() as $data) {
	Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$data->getPreview(MediaEvent::$preview['crop_160x110']);
}

Yii::app()->openGraph->renderTags();

// Подключаем виджед для SEO оптимизации вручную
$this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags');
?>

<script type="text/javascript">
	$(document).ready(function(){
		media.InitFilter();
	});
</script>

<div class="pathBar">
	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(
			'Журнал' => array('/journal'),
		),
	));?>

	<h1><?php $this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags', array(
		'renderH1' => true,
		'defaultH1' => 'Календарь событий'
	));?></h1>

	<span class="rss">
		<i></i>
		<a href="/content/feed/rss2JournalEvents">Подписаться</a>
		<?php Yii::app()->clientScript->registerLinkTag('alternate', 'application/rss+xml', '/content/feed/rss2JournalEvents', '', array('title' => 'MyHome.ru — Журнал — Календарь событий')); ?>
	</span>
	<div class="spacer"></div>
</div>

<?php
	$this->widget('media.components.EventList.EventList', array(
		'dataProvider'=>$eventProvider,
		'viewType'=>$viewType,
		'pageSize' => $pageSize,
		'sortType' => $sortType,
		'sortDirect' => $sortDirect,
		'bannerText' => $this->renderPartial('//widget/between/between_event_list_728x90', array(), true),
	));
?>

<div id="left_side" class="new_template">
	<?php
	$this->widget('media.components.MediaEventBar.MediaEventBar', array(
		'dataProvider' => $eventProvider,
		'viewType' => $viewType,
		'pageSize' => $pageSize,
		'sortType' => $sortType,
		'sortDirect' => $sortDirect,
		'cityId' => $cityId,
		'startTime' => $startTime,
		'endTime' => $endTime,
	));
	?>

	<div class="-gutter-top-dbl -gutter-bottom-dbl -relative"><?php $this->widget('application.components.widgets.banner.BannerWidget', array(
			'section'=>Config::SECTION_EVENT,
			'type'=>2
	)); ?></div>

<?php /*
	<div class="articles_block">
		<h3 class="arch_name">Новости по теме</h3>
		<div class="block_item">
			<a href="#">На алтае прошел экокультурный фестиваль «ВОТЭТНО!» </a>
			<div class="block_item_info">
				<span>Вчера в 12:30 • </span>
				<a href="#">Дизайн интерьера</a>
			</div>
			<div class="clear"></div>
		</div>
		<div class="block_item">
			<a href="#">На алтае прошел экокультурный фестиваль «ВОТЭТНО!» </a>
			<div class="block_item_info">
				<span>Вчера в 12:30 • </span>
				<a href="#">Дизайн интерьера</a>
			</div>
			<div class="clear"></div>
		</div>
		<div class="block_item">
			<a href="#">На алтае прошел экокультурный фестиваль «ВОТЭТНО!» </a>
			<div class="block_item_info">
				<span>Вчера в 12:30 • </span>
				<a href="#">Дизайн интерьера</a>
			</div>
			<div class="clear"></div>
		</div>
	</div>
 */ ?>
	<div class="spacer-30"></div>
</div>
<div class="clear"></div>
<div class="spacer-18"></div>