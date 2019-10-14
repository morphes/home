<?php $this->pageTitle = 'Журнал — Новости — MyHome'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>


<?php
// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = 'Новости MyHome';
Yii::app()->openGraph->description = 'Новинки сферы дизайна, архитектуры, обустройства дома и строительства';

foreach($newsProvider->getData() as $data) {
	Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$data->preview->getPreviewName(MediaNew::$preview['crop_300x213']);
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
			'defaultH1' => 'Новости'
	));?></h1>

	<span class="rss">
		<i></i>
		<a href="/content/feed/rss2JournalNews">Подписаться</a>
		<?php Yii::app()->clientScript->registerLinkTag('alternate', 'application/rss+xml', '/content/feed/rss2JournalNews', '', array('title' => 'MyHome.ru — Журнал — Новости')); ?>
	</span>

	<div class="spacer"></div>
</div>

<div id="right_side" class="new_template">
	<?php if ($newsProvider->getTotalItemCount() > 0) : ?>
	<div class="page_settings new">

		<div class="page_template view_type_list">
			<?php
			if ($filter['f_viewtype'] == '1') {
				$cls[0] = 'list';
				$cls[1] = 'elements current';
			} else {
				$cls[0] = 'list current';
				$cls[1] = 'elements';
			}
			?>
			<a title="Показать списком" data-value="0" class="<?php echo $cls[0];?>" href="#"><img src='/img/list.png'/></a>
			<a title="Показать плиткой" data-value="1" class="<?php echo $cls[1];?>" href="#"><img src='/img/elements.png'/></a>

		</div>
		<div class="pages"><?php // -- ПОСТРАНИЧКА --
		    	$this->widget('application.components.widgets.CustomPager2', array(
			    	'pages' => $newsProvider->getPagination()
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
	if ($filter['f_viewtype'] == '1') {
		$view = '//media/new/_item';
		$optionClass = 'elements';
	} else {
		$view = '//media/new/_itemList';
		$optionClass = 'list';
	}
	$this->widget('application.components.widgets.MediaItemsList', array(
		'dataProvider'        => $newsProvider,
		'itemView'            => $view,
		'saveUri'             => true,
		'emptyText'           => 'Список статей пуст',
		'pageSize'            => $pageSize,
		'htmlOptions'         => array('class' => 'knowledge_items '.$optionClass),
		/*'bannerText' => $this->renderPartial('//widget/banner/_mediaBanner', Config::getBannerData(), true),*/
	));
    Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_under');

    ?>

	<div class="page_settings bottom new">
		<div class="pages"><?php // -- ПОСТРАНИЧКА --
			$this->widget('application.components.widgets.CustomPager2', array(
				'pages' => $newsProvider->getPagination()
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
		'totalItemCount'        => $newsProvider->getTotalItemCount(),
		'themes'                => $themes,
		'filter'                => $filter,
		'pageSize'              => $pageSize,
		'view'			=> 'new'
	));
	?>

	<div class="-gutter-top-dbl -gutter-bottom-dbl -relative"><?php $this->widget('application.components.widgets.banner.BannerWidget', array(
			'section'=>Config::SECTION_NEWS,
			'type'=>2
	)); ?></div>

	<?php // --- ПОХОЖИЕ ЗНАНИЯ --- ?>
	<?php if ( ! empty($sameKnowledges)) : ?>
	<div class="articles_block">
		<h3 class="arch_name">Похожие материалы</h3>

		<?php foreach ($sameKnowledges as $know) : ?>
		<div class="block_item">
			<a href="<?php echo $know->getElementLink();?>"><?php echo $know->title;?> </a>

			<div class="block_item_info">
				<span><?php echo CFormatterEx::formatDateToday($know->public_time);?></span>
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
</script>
