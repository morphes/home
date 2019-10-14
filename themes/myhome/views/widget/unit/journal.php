<?php
Yii::import('application.modules.media.models.*');

$totalKnowledge = MediaKnowledge::model()->published()->count();
$totalNew = MediaNew::model()->countByAttributes(array(
	'status' => MediaNew::STATUS_PUBLIC
));
$total = $totalKnowledge + $totalNew;
?>

<h2 class="main_page_head"><a href="/journal">Журнал</a></h2>
<span class="headline_counter"><?php echo $total;?></span>
<div class="alias">
	Каждый день в Журнале вас ждут свежие новости, интервью с яркими
	личностями, обзоры новых технологий и товаров,
	репортажи с тематических событий и другие полезные статьи
</div>

<div class="media_categories">
	<a href="<?php echo MediaNew::getSectionLink(); ?>">Новости</a>
	<a href="<?php echo MediaKnowledge::getSectionLink(); ?>">Знания</a>
	<a href="<?php echo MediaEvent::getSectionLink(); ?>">События</a>
</div>

<h3 class="main_page_head">Сегодня читают</h3>

<div class="main_item">

	<?php
	/** @var $know MediaKnowledge Последнее опубликованное «Знание» */
	$know = MediaKnowledge::model()->published()->find();
	?>
	<a class="item_head"
	   href="<?php echo $know->getElementLink(); ?>">
		<img src="/<?php echo $know->preview->getPreviewName(MediaKnowledge::$preview[ 'crop_300x213' ]); ?>"
		     width="300"
		     height="213"/>
		<?php echo $know->title;?>
	</a>

	<p><?php echo $know->lead;?></p>
	<span class="date"><?php echo CFormatterEx::formatDateToday($know->public_time);?></span>
</div>

<div class="news_list">
	<?php // Получаем последние новости
	$news = MediaNew::model()->published()->findAll(array('limit' => 3));
	foreach ($news as $new) {
		?>
		<div class="item">
			<a href="<?php echo $new->getElementLink(); ?>">
				<img src="/<?php echo $new->preview->getPreviewName(MediaNew::$preview[ 'crop_60x45' ]); ?>"
				     width="60"
				     height="45"/>
			</a>

			<div class="item_desc">
				<a href="<?php echo $new->getElementLink(); ?>"><?php echo $new->title;?></a><br>
				<span class="date"><?php echo CFormatterEx::formatDateToday($new->public_time);?></span>
			</div>
			<div class="clear"></div>
		</div>
	<?php
	}
	?>
</div>