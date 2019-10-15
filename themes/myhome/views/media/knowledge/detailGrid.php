<?php
/**
 * @var $model MediaKnowledge
 */
$this->pageTitle = $model->title . ' — Журнал — MyHome';
$this->description = $model->meta_desc;
$this->keywords = '';

// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = $model->title;
Yii::app()->openGraph->description = $model->lead;

// Вычленяем все фотки из страницы
preg_match_all('/<img src="(.*?)"/', $model->content, $matches);
if (isset($matches[1]) && is_array($matches[1]))
	foreach ($matches[1] as $src)
		Yii::app()->openGraph->image = $src;

Yii::app()->openGraph->renderTags();
?>


<script type="text/javascript">
	$(function () {
		media.commentScroll();
	})
</script>
<div class="-grid-wrapper page-title">
	<div class="-grid">
		<div class="-col-12">
			<ul class="-menu-inline -breadcrumbs">
				<li><a>Главная</a></li>
				<li><a href="/journal">Журнал</a></li>
				<li><a href="<?php echo MediaKnowledge::getSectionLink(); ?>">Знания</a></li>
			</ul>
		</div>
		<div class="-col-8"><h1><?php echo CHtml::value($model, 'title'); ?></h1></div>
		<div class="-col-4 -text-align-right -inset-top"><a class="-icon-rss -small -gray" href="#">Подписаться</a></div>
	</div>
</div>
<div class="-grid-wrapper page-content">
	<div class="-grid">
		<div class="-col-9 -inset-right">
			<div class="-border-bottom -inset-bottom-dbl -gutter-bottom -gutter-top-hf">
				<span class="-small -gray">
					Автор: <?php echo !empty($model->author_name) ? $model->author_name : $model->author->name; ?>&nbsp;

				</span>
				<span class="-icon-eye-s -small -gray -gutter-left"><?php echo number_format($model->count_view, 0, '', ' '); ?></span>
				<span class="-pseudolink -icon-bubble-s -small -gray -gutter-left"
				      onclick="CCommon.scrollTo($('#comments'))"><i><?php echo number_format($model->count_comment, 0, '', ' ') ?></i></span>
				<span class="-pseudolink -icon-thumb-up-xs -small -gray -gutter-left"><i><?php echo LikeItem::model()->countLikes(get_class($model),$model->id);?></i></span>
			</div>

            <div class="-gutter-top-dbl -gutter-bottom-dbl -inset-bottom">
				<div class="-col-wrap social-likes">
					<?php
					/* -------------------------------------
					 *  Кнопки лайка разных соцсетей
					 * -------------------------------------
					 */
					$this->widget('application.components.widgets.likes.Likes');
					?>
				</div>
				<span class="-push-right">
					<?php
					/* -------------------------------------
					 *  Виджет для добавления в избранное
					 * -------------------------------------
					 */
					$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
						'modelId'   => $model->id,
						'modelName' => get_class($model),
						'viewHeart' => 'favoriteSolidRed'
					));?>
				</span>
			</div>
			<div class="article_text">

				<?php
				/* ---------------------------------------------
				 *  Основной текст статьи
				 * ---------------------------------------------
				 */
				echo CHtml::value($model, 'content');
				?>

				<script type="text/javascript">
					/*
					 * Код, который находит в странице метки,
					 * и заменяет их галереями изображений.
					 */
					$('.article_text').find('.gallery_btn').each(function (index, element) {
						var $element = $(element);
						// ID модели
						var modelId = $element.data('model-id');
						// Номер галереи
						var numGallery = $element.data('num');
						// Загрузка галереи
						$.post(
							'/media/knowledge/ajaxGetGallery',
							{modelId: modelId, num: numGallery},
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
			<div class="-inset-all -gutter-top-dbl -text-align-center">
				<?php
				/* ---------------------------------------------
				 *  Кнопка "Лайк". Большая и красивая
				 * ---------------------------------------------
				 */
				$this->widget('application.components.widgets.LikeItem.LikeWidget',array(
					'modelId'   => $model->id,
					'modelName' => get_class($model),
				));
				?>
				<p class="-small">
					<span class="-gray">Темы: </span>
					<?php
					if ($themes) {
						$html = '';
						foreach ($themes as $key => $theme) {
							$html .= CHtml::link($theme->name, $this->createUrl(MediaKnowledge::getSectionLink()));
							if ($key < count($themes) - 1) {
								$html .= ', ';
							}
						}
						echo $html;
					}
					?>
				</p>
			</div>
			<div class="-gutter-top-dbl -gutter-bottom-dbl -inset-bottom">
				<div class="-col-wrap social-likes">
					<?php
					/* -------------------------------------
					 *  Кнопки лайка разных соцсетей
					 * -------------------------------------
					 */
					$this->widget('application.components.widgets.likes.Likes', array(
						'vkLikePostfix' => '2',
						'okLikePostfix' => '2',
					));
					?>
				</div>
				<span class="-push-right">
					<?php
					/* -------------------------------------
					 *  Виджет для добавления в избранное
					 * -------------------------------------
					 */
					$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
						'modelId'   => $model->id,
						'modelName' => get_class($model),
						'viewHeart' => 'favoriteSolidRed'
					));?>
				</span>
			</div>


            <hr class="-solid -pattern -gutter-top-dbl -gutter-bottom-dbl">
			<?php
			/* -----------------------------------------------------
			 *  Читайте также
			 * -----------------------------------------------------
			 */
			if ($this->beginCache(
				'JOURNAL:READ_MORE:' . get_class($model) . ':' . $model->id,
				array(
					'dependency' => array(
						'class' => 'system.caching.dependencies.CDbCacheDependency',
						'sql'   => 'SELECT update_time FROM ' . MediaKnowledge::model()->tableName(). ' WHERE id = ' . $model->id
					)
				)
			)
			) {
				$this->widget('application.components.widgets.ReadMore.ReadMore', array(
					'model' => $model,
				));

				$this->endCache();
			}
			?>

			<hr class="-solid -gutter-top-dbl -gutter-bottom-dbl">


			<?php
			/* -----------------------------------------------------
			 *  Популярные товары
			 * -----------------------------------------------------
			 */
			?>

			<h3 class="-inset-top -gutter-bottom-hf">Популярные категории каталога товаров</h3>

			<?php // --- Список категорий --- ?>

			<?php if($this->beginCache('JOURNAL:RELATED:CATEGORIES:' . $model->id, array('duration' => 7200))) { ?>
				<?php if($relatedCategories) : ?>
					<div class="-gutter-bottom">
						<ul class="-menu-inline -small -gray popular-goods-menu">
							<?php
							$count =12;

							foreach($relatedCategories as $rc) :
								$count = $count + iconv_strlen($rc->name,'UTF-8');

								if($count<=80):?>
									<li><?php echo CHtml::link($rc->name, $rc->getLink($rc->id), array('target'=>'_blank', 'onclick'=>"_gaq.push(['_trackEvent','relatedProducts','сlick']);return true;"));?></li>
								<?php endif; ?>
							<?php endforeach; ?>

							<li class="-pointer-right -red"><a onclick = "_gaq.push(['_trackEvent','relatedProducts','сlick']);return true;" class="-red" href="/catalog">Весь каталог</a></li>

						</ul>
					</div>
				<?php endif;?>
			<?php $this->endCache(); } ?>

			<?php // --- Список товаров --- ?>

			<div class="-grid popular-goods">
<!--				--><?php //if($this->beginCache('JOURNAL:RELATED:PRODUCTS:' . $model->id, array('duration' => 7200))) { ?>
					<?php foreach($relatedProducts as $rl) : ?>
						<div class="-col-wrap">
							<a onclick = "_gaq.push(['_trackEvent','relatedProducts','сlick']);return true;" href="<?php echo Product::model()->getLink($rl->id) ?>">
								<?php echo CHtml::image('/' . $rl->cover->getPreviewName(Product::$preview['crop_120']), '',
									array('class' => '-quad-122')); ?>
								<span><?php echo Amputate::getLimb($rl->name, 20, '...') ?> </span>
							</a>
							<?php
							$price = $rl->average_price;

							if ($price) {
								echo CHtml::tag('span', array('class' => '-gray -strong' ), number_format($price, 0, '.', ' ') . ' руб.');
							} else {
								echo CHtml::tag('span', array('class' => '-gray'), 'Цена не указана');
							}
							?>
						</div>
					<?php endforeach; ?>
<!--				--><?php //$this->endCache(); } ?>
			</div>
			<hr class="-solid -gutter-top-dbl -gutter-bottom-dbl">

			<?php
			/* -----------------------------------------------------
			 *  Комментарии
			 * -----------------------------------------------------
			 */
			$this->widget('application.components.widgets.WComment', array(
				'model'        => $model,
				'hideComments' => !$model->getCommentsVisibility(),
				'showCnt'      => 0,
				'showRating'   => false,
				'guestComment' => true,
                'showDirect'   => false,
			));?>

		</div>
		<div class="-col-3 article-sidebar">

			<div class="-gutter-bottom-dbl">
				<?php $this->widget('application.components.widgets.banner.BannerWidget', array(
					'section' => Config::SECTION_KNOWLEDGE,
					'type'    => 2
				)); ?>
			</div>

			<div class="-gutter-bottom">
				<script type="text/javascript"
					src="//vk.com/js/api/openapi.js?87"></script>

				<!-- VK Widget -->
				<div id="vk_groups"></div>
				<script type="text/javascript">
					VK.Widgets.Group("vk_groups", {mode: 1, width: "220", height: "153"}, 32251753);
				</script>
			</div>
			<div class="-gutter-bottom">
				<!--280648101946177-->

				<iframe src="//www.facebook.com/plugins/facepile.php?href=http%3A%2F%2Ffacebook.com%2Fmyhome.ru&amp;action&amp;size=small&amp;max_rows=2&amp;show_count=true&amp;width=220&amp;colorscheme=light"
					scrolling="no"
					frameborder="0"
					style="border:none; overflow:hidden; width:220px;"
					allowTransparency="true"></iframe>


			</div>
		</div>

		<?php
		$interestData = $interestProvider->getData();

		//Формируем колонки если есть данные
		if ($interestData) :

			$column1 = array();
			$column2 = array();
			$column3 = array();

			$column1[] = current($interestData);

			while (1) {
				$tmp = next($interestData);

				if (!$tmp) {
					break;
				}
				$column2[] = $tmp;

				$tmp = next($interestData);

				if (!$tmp) {
					break;
				}
				$column3[] = $tmp;

				$tmp = next($interestData);

				if (!$tmp) {
					break;
				}
				$column1[] = $tmp;
			}


			?>
			<hr class="-col-12 -solid -pattern -gutter-top-dbl -gutter-bottom-dbl">
			<h1 class="-text-align-center -inset-top-dbl">У нас
								      еще много
								      интересного</h1>
			<div class="interest-content">
				<!--Выводим первую колонку-->
				<div class="-col-3">
					<div class="-grid items">
						<?php foreach ($column1 as $cl) {
							echo InterestData::getItemHtml($cl);
						}
						?>

					</div>
				</div>

				<!--Выводим вторую колонку-->
				<div class="-col-6">
					<div class="-grid items">
						<?php
						//Устанавливаем номер позиции
						//Это необходимо что бы кажду третью картинку
						//в колонке выводить большой
						//Первоначально ставим 3 так как первую картику в колонке так же необходимо
						//вывести больной
						$position = 3;
						?>
						<?php foreach ($column2 as $cl) {
							//выводим большую картинку по
							//выполнию условия
							if ($position == 3) {
								echo InterestData::getItemHtml($cl, true);
								$position = 0;
							} else {
								echo InterestData::getItemHtml($cl);
							}
							$position = $position + 1;
						}
						?>
					</div>
				</div>

				<!--Выводим третью картинку-->
				<div class="-col-3">
					<div class="-grid items">
						<?php foreach ($column3 as $cl) {
							echo InterestData::getItemHtml($cl);
						}
						?>

					</div>
				</div>

				<?php

				if ($interestProvider->pagination->currentPage < $interestProvider->pagination->pageCount - 1) {
					echo CHtml::hiddenField(
						'next_page_url',
						Yii::app()->createUrl('media/knowledge/AjaxInterest', $params = array('page' => $interestProvider->pagination->currentPage + 1))
					);
				}

				?>
			</div>
			<div class="-col-12 -text-align-center -inset-top-dbl" id="contentLoader">
				<a href="#"
				   class="-icon-eye-s -inline -pseudolink -gutter-bottom-dbl -icon-gray -large more-button"
				   id="interestScroll"><i class="-skyblue">Посмотреть
									   еще</i></a>
			</div>
		<?php endif; ?>
	</div>
</div>
<script>
	Cmedia.likeItem();
	Cmedia.setOptions({itemId:<?php echo $model->id;?>});
	Cmedia.initInterestActions();
</script>
