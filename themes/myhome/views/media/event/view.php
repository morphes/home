<?php
/**
 * @var $event MediaEvent
 */
$this->pageTitle = $event->name.' — Журнал — MyHome';
$this->description = $event->meta_desc;

Yii::app()->getClientScript()->registerScriptFile('/js/fancybox.js');
Yii::app()->getClientScript()->registerCssFile('/css/fancybox.css');
Yii::app()->getClientScript()->registerScriptFile('http://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=ru-RU');
?>

<div class="pathBar">
	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(
			'Журнал' => array('/journal'),
			'Календарь событий' => array('/journal/events'),
		),
	));?>
	<h1><?php echo $event->name; ?></h1>
	<div class="spacer"></div>
	<div class="event_date">
		<?php echo CFormatterEx::formatDateRange($event->start_time, $event->end_time); ?>
	</div>
</div>
<div id="left_side" class="wide">
	<div class="article_text">
		<div class="event_info_block">
			<div class="photo">
				<?php /** @var $event MediaEvent */
				echo CHtml::image('/'.$event->getPreview(MediaEvent::$preview['crop_300x213']), 'имя эвента', array('width'=>300, 'height'=>213) );
				?>
			</div>
			<div class="event_info">
				<?php /** @var $place MediaEventPlace */
				foreach ($places as $place) : ?>
				<?php $city = $place->getCity();
					if (is_null($city))
						continue;
				?>
				<div class="row">
					<p>Место проведения</p>
					<?php
					$flag = Country::getFlagById($city->country_id);
					$countryName = Country::getNameById($city->country_id);
					echo CHtml::image('/'.$flag, $countryName, array('title'=>$countryName));
					?>
					<?php echo $place->getCityName(); ?>, <?php echo $place->address; ?><br>
					<?php echo $place->name; ?>

					<?php $coordinates = unserialize($place->geocode);
					if ($coordinates) {
						echo '<br />';
						echo CHtml::tag('span', array('class'=>'show_map', 'data-coordinates'=>($coordinates[1].', '.$coordinates[0]) ), 'Посмотреть на карте');
					}
					?>
				</div>
				<?php if (!empty($place->event_time)) : ?>
				<div class="row">
					<p>Время проведения</p>
					<?php echo $place->event_time; ?>
				</div>
				<?php endif; ?>
				<?php endforeach; ?>
				<?php if (!empty($event->cost)) : ?>
				<div class="row">
					<p>Стоимость участия</p>
					<?php echo $event->cost; ?>
				</div>
				<?php endif; ?>
				<?php if (!empty($event->site)) : ?>
				<div class="row">
					<p>Веб-сайт</p>
					<noindex><?php echo CHtml::link($event->site, $event->site, array('rel' => 'nofollow', 'target'=>'_blank')); ?></noindex>
				</div>
				<?php endif; ?>
			</div>
			<div class="clear"></div>
		</div>

		<?php echo $event->content; ?>

		<script type="text/javascript">
			$('.article_text').find('.gallery_btn').each(function (index, element) {
				var $element = $(element);
				// ID модели
				var modelId = $element.data('model-id');
				// Номер галереи
				var numGallery = $element.data('num');
				// Загрузка галереи
				$.post(
					'/media/event/ajaxGetGallery',
					{modelId:modelId, num:numGallery},
					function (response) {
						if (response.success) {
							$element.parent('div').replaceWith($('<div>').attr('id', 'gallery_' + numGallery).append(response.html));
							$('#gallery_' + numGallery).hide().fadeIn(300, function () {
								var mpp = new mediaPhotoPlayer();
								mpp.init('player_' + numGallery);
							});

						}
					}, 'json'
				);
			});
		</script>

	</div>

	<?php if (!empty($mediaNews)) : ?>
	<div class="media_news">
		<h2>Новости и репортажи</h2>
		<?php /** @var $mediaNew MediaNew */
		foreach ($mediaNews as $mediaNew) : ?>
			<div class="item">
				<?php echo CHtml::link($mediaNew->title, $mediaNew->getElementLink()); ?>
				<div><span><?php echo CFormatterEx::formatDateToday($mediaNew->public_time); ?></span></div>
				<div class="clear"></div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php
	// Between
	$this->renderPartial('//widget/between/between_news_728x90');
	?>

	<?php // Несколько последних комментариев
	$this->widget('application.components.widgets.WComment', array(
		'model'        => $event,
		'hideComments' => !$event->getCommentsVisibility(),
		'showCnt'      => 0,
		'showRating'   => false,
		'guestComment' => true,
	));?>

</div>
<div id="right_side" class="narrow">
	<div class="right_block first">
		<div class="item_info">
			<div class="block_item_counters">
				<span class="views_quant" title="Просмотры"><i></i><?php echo number_format($event->count_view, 0, '', ' ');?></span>
				<span class="comments_quant" title="Комментарии"><i></i><a href="#"><?php echo number_format($event->count_comment, 0, '', ' ')?></a></span>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="right_block">
		<p class="small gray_font">Организатор</p>
		<?php echo nl2br($event->organizer); ?>
		<!--<a href="#">7 мероприятий</a>-->
	</div>
	<div class="right_block">
		<p class="small gray_font">Тип события</p>
		<ul class="article_params">
			<li><?php echo CHtml::link($eventType->name, MediaEvent::getListLink( array('type[]'=>$eventType->id) )); ?></li>
		</ul>
		<p class="small gray_font">Тематика</p>
		<ul class="article_params last">
			<?php
			if ( !empty($themes) ) {
				foreach ($themes as $theme) {
					echo CHtml::tag('li', array(), CHtml::link($theme->name, MediaEvent::getListLink(array('theme[]'=>$theme->id))), true);
				}
			}
			?>
		</ul>
	</div>

	<div class="tools visit_event <?php if ($isVisit) echo 'active'; ?>">
		<i></i>
		<span class="link<?php if (Yii::app()->getUser()->getIsGuest()) echo ' disable'; ?>">
			<?php if ($isVisit) : ?>
			<span>Я иду!</span>
			<div class="hint">
				<i></i>
				Вы идете на это мероприятие
				<a href="#" class="refuse">Отказаться</a>
			</div>
			<?php else : ?>
			<span>Я пойду!</span>
				<?php if (Yii::app()->getUser()->getIsGuest()) : ?>
				<div class="hint">
					<i></i>
					Функция доступна только зарегистрированным пользователям.
					<a class="" href="/site/registration">Зарегистрироваться</a>
				</div>
				<?php else : ?>
				<div class="hint hide">
					<i></i>
					Вы идете на это мероприятие
					<a class="refuse" href="#">Отказаться</a>
				</div>
				<?php endif; ?>

			<?php endif; ?>
		</span>
		<span class="visitors"><span><?php echo $visitorsCount; ?></span>
			<div class="hint visitors_list">
				<i></i>
				<?php if (empty($visitorsCount)) : ?>
				<p>Пока никто не планирует посетить</p>
				<?php else : ?>
				<p>Планируют посетить:</p>
				<?php endif; ?>
				<?php
				/** @var $visitor User */
				foreach ($visitors as $visitor) {
					echo CHtml::link( CHtml::image('/'.$visitor->getPreview(User::$preview['crop_23'])), $visitor->getLinkProfile(), array('title'=>$visitor->name)  );
				}
				?>
			</div>
		</span>
	</div>
	<?php if ($event->send_status != MediaEvent::NOTIFY_SENT && ( $event->start_time - MediaEvent::NOTIFY_PERIOD) > time() ) : ?>
	<div class="tools remind">
		<i></i>
		<span class="link">Напомнить мне</span>
	</div>
	<?php endif; ?>
<?php /* Отсутствует в связи с отсутствием вывода в избранном
	<div class="favorite_button add_this_to_favorite">
		<i></i>
		<a href="#">В избранное</a>
	</div>
 */ ?>
	<div class="social_links">
		<?php $this->widget('ext.sharebox.EShareBox', array(
		'view' => 'news',
		// url to share, required.
		'url' => Yii::app()->request->hostInfo.Yii::app()->request->requestUri,

		// A title to describe your link, required.
		'title'=> $event->name,

		// A small message for post
		'message' => '',
		'classDefinitions' => array(
			'livejournal' => 'ns-lj',
			'vkontakte' => 'ns-vk',
			'twitter' => 'ns-tw',
			'facebook' => 'ns-fb',
			'google+' => 'ns-gp',
		),
		'exclude' => array('odkl','pinterest'),
		'htmlOptions' => array('class' => 'social'),
	));?>
	</div>

	<?php $this->widget('media.components.SimilarEvents.SimilarEvents', array(
			'eventId' => $event->id,
	)); ?>


</div>
<div class="clear"></div>
<div class="spacer-18"></div>

<script type="text/javascript">
	$(document).ready(function(){
		media.setOptions({'eventId':<?php echo $event->id; ?>, 'email':'<?php echo (Yii::app()->getUser()->getIsGuest()) ? '' : Yii::app()->getUser()->getModel()->email; ?>'});
		media.visitEventInit();
		media.remindEventInit();
		media.showMap();
		media.commentScroll();
	})
</script>