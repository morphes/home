<?php $this->pageTitle = 'Журнал — Знания — MyHome'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>


<?php
// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = 'Знания MyHome';
Yii::app()->openGraph->description = 'Статьи о дизайне и строительстве, интервью с яркими личностями, репортажи с тематических событий, видео и многое другое';

foreach($knowledgeProvider->getData() as $data) {
	Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$data->preview->getPreviewName(MediaKnowledge::$preview['crop_300x213']);
}

Yii::app()->openGraph->renderTags();


// Подключаем виджед для SEO оптимизации вручную
$this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags');
?>


<div class="pathBar">
	<p class="path">
		<a href="/">Главная</a>
		<a href="/journal">Журнал</a>
	</p>

	<h1><?php $this->widget('application.components.widgets.SeoMetaTags.WSeoMetaTags', array(
			'renderH1' => true,
			'defaultH1' => 'Знания'
	));?></h1>

	<span class="rss">
		<i></i>
		<a href="/content/feed/rss2JournalKnowledge">Подписаться</a>
		<?php Yii::app()->clientScript->registerLinkTag('alternate', 'application/rss+xml', '/content/feed/rss2JournalKnowledge', '', array('title' => 'MyHome.ru — Журнал — Знания')); ?>
	</span>

	<div class="spacer"></div>
</div>
<div id="right_side" class="new_template">
	<?php if ($knowledgeProvider->getTotalItemCount() > 0) : ?>
	<div class="page_settings new">

		<div class="page_template view_type_list">
			<?php
			if ($filter['f_viewtype'] == MediaKnowledge::VIEW_TYPE_ELEM OR empty($filter['f_viewtype']))
			{
				$cls[MediaKnowledge::VIEW_TYPE_LIST] = 'list';
				$cls[MediaKnowledge::VIEW_TYPE_ELEM] = 'elements current';
			}
			else
			{
				$cls[MediaKnowledge::VIEW_TYPE_LIST] = 'list current';
				$cls[MediaKnowledge::VIEW_TYPE_ELEM] = 'elements';
			}
			?>
			<a title="Показать списком" data-value="<?php echo MediaKnowledge::VIEW_TYPE_LIST;?>" class="<?php echo $cls[MediaKnowledge::VIEW_TYPE_LIST];?>" href="#"><img src='/img/list.png'/></a>
			<a title="Показать плиткой" data-value="<?php echo MediaKnowledge::VIEW_TYPE_ELEM;?>" class="<?php echo $cls[MediaKnowledge::VIEW_TYPE_ELEM];?>" href="#"><img src='/img/elements.png'/></a>

		</div>
		<div class="sort_elements">
			Сортировать по
			<div class="<?php if (empty($filter['sorttype']) || $filter['sorttype'] == MediaKnowledge::SORT_NEWER) echo 'current';?>">
				<span data-value="<?php echo MediaKnowledge::SORT_NEWER;?>">новизне</span></div>
			<div class="<?php if ($filter['sorttype'] == MediaKnowledge::SORT_COMMENT) echo 'current';?>">
				<span data-value="<?php echo MediaKnowledge::SORT_COMMENT;?>">комментариям</span></div>
			<div class="<?php if ($filter['sorttype'] == MediaKnowledge::SORT_VIEW) echo 'current';?>"><span
				data-value="<?php echo MediaKnowledge::SORT_VIEW;?>">просмотрам</span></div>
		</div>
		<div class="pages"><?php // -- ПОСТРАНИЧКА --
			$this->widget('application.components.widgets.CustomPager2', array(
				'pages' => $knowledgeProvider->getPagination()
			));
		?></div>
		<div class="elements_on_page drop_down">
			Показать <span class="exp_current"><?php echo Config::$mediaPageSizes[$pageSize]; ?>
                	<i></i></span>
			<ul>
				<?php
			foreach (Config::$mediaPageSizes as $key => $value) {
				echo CHtml::tag('li', array('data-value' => $key), $value);
			}
			?>
			</ul>
		</div>
		<div class="clear"></div>
	</div>

   <?php  Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_above'); ?>

   <?php
	if ($filter['f_viewtype'] == MediaKnowledge::VIEW_TYPE_ELEM OR empty($filter['f_viewtype']))
	{
		$view = '//media/knowledge/_item';
		$optionClass = 'elements';
	}
	else
	{
		$view = '//media/knowledge/_itemList';
		$optionClass = 'list';
	}
	$this->widget('application.components.widgets.MediaItemsList', array(
		'dataProvider'        => $knowledgeProvider,
		'itemView'            => $view,
		'saveUri'             => true,
		'emptyText'           => 'Список статей пуст',
		'pageSize'            => $pageSize,
		'htmlOptions'         => array('class' => 'knowledge_items '.$optionClass),
		//'bannerText' => $this->renderPartial('//widget/banner/_mediaBanner', Config::getBannerData(), true),
	));
        Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_under');
    ?>

        <div class="page_settings bottom new">
		<div class="pages"><?php // -- ПОСТРАНИЧКА --
			$this->widget('application.components.widgets.CustomPager2', array(
				'pages' => $knowledgeProvider->getPagination()
			));
		?></div>
		<div class="elements_on_page drop_down">
			Показать <span class="exp_current"><?php echo Config::$mediaPageSizes[$pageSize]; ?>
                	<i></i></span>
			<ul class="set_input need_submit">
				<?php
			foreach (Config::$mediaPageSizes as $key => $value) {
				echo CHtml::tag('li', array('data-value' => $key), $value);
			}
			?>
			</ul>
		</div>
		<div class="clear"></div>
	</div>

	<?php else: ?>

	<div class="gallery-210 margin-top-37">
		<div class="no_result">
                	К сожалению, у нас пока нет материалов, подходящих вашему запросу, но вскоре они обязательно появятся.
			<br>
                    	<a href="#" onclick="$('form .clear_filter a').trigger('click'); return false;">Сбросить фильтр</a>
		</div>
		<div class="clear"></div>
	</div>

	<?php endif;?>

</div>
<div id="left_side" class="new_template">
	<?php
	$this->widget('media.components.MediaFilterBar.MediaFilterBar', array(
		'totalItemCount'        => $knowledgeProvider->getTotalItemCount(),
		'themes'                => $themes,
		'filter'                => $filter,
		'pageSize'              => $pageSize,
		'view'			=> 'knowledge',
	));
	?>

	<div class="-gutter-top-dbl -gutter-bottom-dbl -relative"><?php $this->widget('application.components.widgets.banner.BannerWidget', array(
			'section'=>Config::SECTION_KNOWLEDGE,
			'type'=>2
	)); ?></div>

	<?php // --- ПОХОЖИЕ НОВОСТИ --- ?>
	<?php if ( ! empty($sameNews)) : ?>
	<div class="articles_block">
		<h3 class="arch_name">Новости по теме</h3>

		<?php foreach ($sameNews as $new) : ?>
		<div class="block_item">
			<a href="<?php echo $new->getElementLink();?>"><?php echo $new->title;?> </a>

			<div class="block_item_info">
				<!--<span><?php /*echo CFormatterEx::formatDateToday($new->public_time);*/?></span>-->
				<!--• <a href="#">Дизайн интерьера</a>-->
			</div>
			<div class="clear"></div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>


	<?php
	// Яндекс.Директ
	$this->renderPartial('//widget/google/adsense_120x600_media_list');
	?>
	<?php
	// Яндекс.Директ
    Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_vertical');
	?>
</div>
<div class="clear"></div>

<script type="text/javascript">
	/**
	 * При выборе нового кол-ва элементов на странице, вставляем
	 * это значение в форму левого фильтра и сабмитим.
	 */
	$('.elements_on_page ul li').click(function () {
		$('#filter_form input[name=pagesize]').val($(this).data('value'));
		$('#filter_form').submit();
	});
	/**
	 * При выборе вида списка вставляем это значение в форму левого фильтра и сабмитим.
	 */
	$('.view_type_list a').click(function () {
		$('#filter_form input[name=f_viewtype]').val($(this).data('value'));
		$('#filter_form').submit();
		return false;
	});

	$('.sort_elements span').click(function () {
		$('#filter_form input[name=sorttype]').val($(this).data('value'));
		$('#filter_form').submit();
		return false;
	});
</script>
