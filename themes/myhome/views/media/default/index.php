<?php $this->pageTitle = 'Журнал — MyHome' ?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>

<?php
// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = "Журнал MyHome";

Yii::app()->openGraph->description = "Новости дизайна, ремонта и благоустройства, интервью с яркими личностями, "
	. "репортажи с тематических событий и другие полезные статьи";

if (!empty($promoBlocks)) {
	foreach ($promoBlocks as $promo) {
		Yii::app()->openGraph->image = Yii::app()->homeUrl . '/' . $promo->preview->getPreviewName(MediaPromo::$preview[ 'crop_640x360' ]);
	}
}

Yii::app()->openGraph->renderTags();

// Подключаем виджед для SEO оптимизации вручную
$this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags');
?>

<script type="text/javascript">
	$(document).ready(function () {
		media.slider();
		media.initTabs();
	})
</script>

<div class="pathBar">
	<p class="path">
		<a href="/">Главная</a>
	</p>

	<h1><?php $this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags', array(
		'renderH1' => true,
		'defaultH1' => 'Журнал'
	));?></h1>

	<div class="spacer"></div>
</div>
<div class="knowledge_index_page">

	<div class="border_block slider">
		<?php
		// Будет содержать HTML списка всех фотографий
		$htmlImages = '';
		// Будет содержать HTML список всех описаний для фоток
		$htmlDesc = '';

		if (!empty($promoBlocks)) {
			$i = 1;
			foreach ($promoBlocks as $promo) {

				// -- Блок с фотографиями --

				$htmlOptionsImg = array('width' => 640, 'height' => 360);
				$htmlOptionsDiv = array('id' => 'img_' . $i);

				if ($i == 1)
					$htmlOptionsDiv[ 'class' ] = 'visible';

				$htmlImages .= CHtml::openTag('div', $htmlOptionsDiv);
				$htmlImages .= CHtml::openTag('a', array('href' => $promo->url));
				$htmlImages .= CHtml::image(
					'/' . $promo->preview->getPreviewName(MediaPromo::$preview[ 'crop_640x360' ]),
					'', $htmlOptionsImg
				);
				$htmlImages .= CHtml::closeTag('a');
				$htmlImages .= CHtml::closeTag('div');


				// -- Блок с описаниями --

				$htmlOptions = array();
				$htmlOptions[ 'class' ] = 'slide_item';

				if ($i == 1)
					$htmlOptions[ 'class' ] .= ' current';
				$htmlOptions[ 'data-image' ] = $i;

				$htmlDesc .= CHtml::openTag('div', $htmlOptions);
				$htmlDesc .= CHtml::tag('i', array(), '', true);
				$htmlDesc .= CHtml::openTag('div');
				$htmlDesc .= CHtml::link($promo->title, $promo->url);
				$htmlDesc .= CHtml::tag('p', array(), Amputate::getLimb($promo->lead, 120), true);
				$htmlDesc .= CHtml::closeTag('div');
				$htmlDesc .= CHtml::closeTag('div');

				$i++;
			}
		}
		?>
		<div class="slider_imgs">
			<?php echo $htmlImages; ?>
		</div>
		<div class="slider_control">
			<?php echo $htmlDesc; ?>
		</div>
		<div class="clear"></div>
	</div>

	<ul class="knowledge_tabs">
		<?php
		if ($themes) {
			foreach ($themes as $i => $theme) {
				if ($i >= 6) {
					continue;
				}

				$options = array();
				if ($i == 0) {
					$options[ 'class' ] = 'current';
				}

				echo CHtml::tag(
					'li',
					$options,
					CHtml::link(
						$theme->name, '#tab/' . $theme->id,
						array('data-id' => $theme->id)
					),
					true
				);
			}
		}
		?>
	</ul>
	<div class="clear"></div>

	<div class="knowledge_tabs_content">

		<?php // Сюда AJAX'ом гурзим конент ?>

	</div>

	<div class="knowledge_bottom_block">
		<div class="popular_today">
			<?php if (!empty($bestReading)) : ?>
				<h2>Сегодня читают</h2>

				<div class="articles_block">
					<?php $index = 1; ?>
					<?php foreach ($bestReading as $item) : ?>
						<div class="block_item">
							<div class="block_item_image">
								<?php echo CHtml::image(
									'/' . $item->preview->getPreviewName(MediaNew::$preview[ 'crop_60x45' ]),
									'',
									array('width' => 65, 'height' => 45)
								);?>
								<span class="views_quant"><i></i><?php echo $item->count_view;?></span>
							</div>
							<div class="block_item_image_desc">
								<a href="<?php echo $item->getElementLink(); ?>">
									<?php echo $item->title;?> </a>

								<div class="block_item_info">
									<span><?php echo CFormatterEx::formatDateToday($item->public_time);?></span>
									<?php // • <a href="#">Дизайн интерьера</a> ?>
									<span class="-icon-eye-s -small -gray -gutter-left-hf"><?php echo number_format($item->count_view, 0, '', ' '); ?></span>
									<span class="-icon-thumb-up-xs -small -gray -gutter-left-hf"><?php echo LikeItem::model()->countLikes(get_class($item),$item->id); ?></span>
								</div>
							</div>
							<div class="clear"></div>
						</div>
						<?php
						if ($index++ == 4) {
							break;
						}
						?>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="articles_block">&nbsp;</div>
			<?php endif; ?>
		</div>
		<div class="interview_block">
			<?php if (!empty($peoples)) { ?>
				<h2>Люди говорят</h2>
				<span class="all_elements_link"><a href="<?php echo $this->createUrl(
						MediaKnowledge::getSectionLink(),
						array('f_genre' => 1)); ?>">Все
									    интервью</a><span>&rarr;</span></span>

				<div class="interview_list">
					<?php foreach ($peoples as $people) : ?>
						<div class="interview_list_item">
							<?php echo CHtml::image(
								'/' . $people->photo->getPreviewName(MediaPeople::$preview[ 'crop_120' ]),
								'',
								array('width' => 120, 'height' => 120)
							);?>

							<div class="interview_list_item_desc">
								<p class="item_head"><?php echo $people->fio;?></p>
								<span><?php echo $people->job;?></span>

								<div class="quote">
									<i></i>
									<a href="<?php echo $people->url; ?>"><?php echo $people->message;?></a>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php } ?>
		</div>
	</div>
	<div class="clear"></div>
</div>
<div class="spacer-18"></div>
