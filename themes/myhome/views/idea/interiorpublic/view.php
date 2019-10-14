<?php
/**
 * @var $model Interiorpublic
 * @var $styles array
 * @var $colors array
 * @var $buildType IdeaHeap
 */

$cs = Yii::app()->getClientScript();
$cs->registerCssFile('/css/ideas.css');
//$cs->registerCssFile('/css/fotorama.css');

$cs->registerScriptFile('/js/scroll.js');
$cs->registerScriptFile('/js/CIdeas.js');
//$cs->registerScriptFile('/js/fotorama.js');
$cs->registerScriptFile('/js/jquery.popup.carousel.js');
$cs->registerScriptFile('/js/bootstrap-scrollspy.js');

// Заголовок по-умолчанию
$this->pageTitle = $model->name.' — Интерьеры — MyHome.ru';

/**
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

$seoCaseColors = array(
	'бежевый'    => 'бежевом',
	'белый'      => 'белом',
	'желтый'     => 'желтом',
	'зеленый'    => 'зеленом',
	'золотой'    => 'золотом',
	'коричневый' => 'коричневом',
	'красный'    => 'красном',
	'оранжевый'  => 'оранжевом',
	'розовый'    => 'розовом',
	'серый'      => 'сером',
	'синий'      => 'синем',
	'фиолетовый' => 'фиолетовом',
	'черный'     => 'черном',
);


if (isset($seoCase[$model->building_type_id]))
{
	$build_0_lower = $seoCase[$model->building_type_id][0];
	$build_1_lower = $seoCase[$model->building_type_id][1];

	$this->pageTitle = str_replace(array('«','»','"'), '', $model->name).' — Интерьеры '.$build_0_lower.' — MyHome.ru';


	// Определяем цвета
	$colorName = '';
	$cnt = 0;
	foreach ($colorsList as $colorId) {
		if ($cnt>0)
			$colorName .= ', ';
		else
			$cnt++;
		$colorName .= $colors[$colorId]->option_value;
	}

	if (in_array($author->role, array(User::ROLE_ADMIN, User::ROLE_MODERATOR, User::ROLE_JUNIORMODERATOR, User::ROLE_POWERADMIN, User::ROLE_SALEMANAGER, User::ROLE_SENIORMODERATOR))) {
		$author_name = 'Редакция MyHome';
		$author_city = 'Москва';
	} else {
		$author_name = $author->name;
		$author_city = $author->getCity();
	}

	$this->description = 'Название идеи интерьера: '.str_replace(array('«','»','"'), '', $model->name).'. '
			     	.'Идея опубликована: '.$author_name.', '
				.'г. '.$author_city.'. '
				.'Тип интерьера: '.$build_1_lower.'. '
				.'Стиль интерьера: '.$styles[ $model->style_id ]->option_value.'. '
				.'Цвет интерьера: '.$colorName;

	$this->keywords = str_replace(array('«','»','"'), '', $model->name).', '
			.$author_name.', '
			.$styles[ $model->style_id ]->option_value.', '
			.$colorName.', '
			.'общественные интерьеры, идеи интерьеров, идеи для интерьера, дизайн интерьеров, майхоум, myhome, myhome.ru.';
}

// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----
Yii::app()->openGraph->renderTags();
?>

<div class="pathBar">
	<?php
	$this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(
			'Идеи<span class="text_block"> для интерьера</span>' => array('/idea'),
			'Общественные интерьеры'	=> array('/idea/interiorpublic'),
			$buildType->option_value	=> array('/idea/interiorpublic/' . $buildType->eng_name)
		),
		'encodeLabel' => false
	));?>

	<h1><?php
		echo CHtml::value($model, 'name');
		if (Yii::app()->user->checkAccess(array(User::ROLE_ADMIN, User::ROLE_JUNIORMODERATOR, User::ROLE_MODERATOR, User::ROLE_POWERADMIN, User::ROLE_SENIORMODERATOR))) {
			echo CHtml::link('[ред.]', Yii::app()->createUrl('/idea/admin/interiorpublic/update', array('id'=>$model->id)));
		}
		?></h1>
	<div class="spacer"></div>
</div>

<div class="idea_page">

<div id="right_side" class="new_template">
   <?php Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_item_above'); ?>

	<?php /** Вывод описания интерьера */
	if (mb_strlen($model->desc, 'utf-8') <= 365) : ?>
		<p class="description"><?php echo nl2br(CHtml::value($model, 'desc')); ?></p>
		<?php else : ?>
		<p class="description">
			<span class="desc_min"><?php echo nl2br(Amputate::getLimb( $model->desc, 365, '')); ?></span>
			<span class="ellipsis">...</span>
			<span class="hide"><?php echo nl2br($model->desc); ?></span>
		</p>
		<span class="all_elements_link">
		<span>Показать полностью</span><span class="arrow">&darr;</span>
	</span>

		<?php endif; ?>

	<script type="text/javascript">
		idea.showDescript();
	</script>
	<div class="spacer-18"></div>
	<div class="photos room">
        <?php
		/** @var $photo UploadedFile */

		foreach ($photos as $photo) : ?>
			<div class="photo_item" id="<?php echo 'p_'.$photo->id; ?>">
				<?php $text = $model->name.'. '.$buildType->option_value; ?>
				<div class="photo_item_img">
					<div class="image_container">
					<?php
					$fileName = $photo->getPreviewName(Interiorpublic::$preview['width_520'], 'interiorContent');
					$params = array_merge( array('data-id'=>$photo->id), UploadedFile::getImageSize($fileName) );
					echo CHtml::image('/'.$fileName, $text, $params);
					?>
					</div>
				</div>
				<div class="photo_item_desc">
					<div class="photo_head">
						<p><?php echo $text; ?></p>
						<a href="<?php echo '#p_'.$photo->id; ?>">#</a>
					</div>

					<?php if (!empty( $photo->desc )) {
						echo CHtml::tag('p', array(), $photo->desc);
					} ?>

					<?php $this->widget('ext.sharebox.EShareBox', array(
					'view' => 'idea',
					'url' => Yii::app()->request->hostInfo.$model->getIdeaLink().'/#p_'.$photo->id,
					'title'=> $text,
					'message' => $photo->desc,
					'imgUrl' =>Yii::app()->request->hostInfo.'/'.$photo->getPreviewName(InteriorContent::$preview['resize_1920x1080']),
					'classDefinitions' => array(
						'vkontakte' => 'vk',
						'twitter' => 'tw',
						'facebook' => 'fb',
						'google+' => 'gp',
						'odkl' => 'ok',
						'pinterest' => 'pi',
					),
					'exclude' => array('livejournal'),
					'htmlOptions' => array('class' => 'share_block'),
				));?>
				</div>
				<div class="clear"></div>
			</div>
		<?php endforeach; ?>

		<script type="text/javascript">
			idea.showTags();
			//idea.showPhotos();
		</script>

		<?php // Несколько последних комментариев
		$this->widget('application.components.widgets.WComment', array(
			'model' => $model,
			'hideComments' => !$model->getCommentsVisibility(),
			'showCnt' => 0,
		));?>
		<div class="spacer-30"></div>

	</div>
</div>
<div id="left_side" class="-new_template">
	<div class="idea_sidebar" data-scrollspy="true">

		<div class="idea_sidebar_item">
			<?php echo CHtml::image('/'.$model->getPreview(Interiorpublic::$preview['crop_180']), $model->name, array('class'=>'cover', 'width'=>180, 'height'=>180)); ?>
			<?php
			$this->widget('application.components.widgets.WStar', array(
				'selectedStar' => $model->average_rating,
				'addSpanClass' => 'rating-s',
			));
			?>
			<div class="block_item_counters">
				<span class="views_quant"><i></i><?php echo $viewCount; ?></span>
				<span class="photos_quant"><i></i><?php echo $model->count_photos; ?></span>
				<a href="#" class="comments_quant"><i></i><?php echo $model->count_comment; ?></a>
			</div>
			<div class="clear"></div>
		</div>

		<div class="idea_sidebar_item last">
			<div class="sidebar-tools comment">
				<span class="-icon-bubble-s -red"><i class="-acronym">Комментировать</i></span>
			</div>
			<?php // Подключаем виджет для добавления в избранное
			$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
				'modelId'   => $model->id,
				'modelName' => get_class($model),
				'viewHeart' => 'favoriteMedia'
			));?>
		</div>

		<?php $this->widget('ext.sharebox.EShareBox', array(
			'view' => 'mainIdea',
			'url' => Yii::app()->request->hostInfo.$model->getIdeaLink(),
			'title'=> $model->name,
			'message' => $model->desc,
			'classDefinitions' => array(
				'vkontakte' => 'vk',
				'twitter' => 'tw',
				'facebook' => 'fb',
				'google+' => 'gp',
				'odkl' => 'ok',
			),
			'exclude' => array('livejournal','pinterest'),
			'htmlOptions' => array('class' => 'share_block'),
		));?>
		<div class="idea_short_controls">
			<ul>
				<li>
					<a href="#" class="icon icon-info"></a>
				</li>
				<li>
					<a href="#" class="pseudo-link"><i><?php echo $model->count_comment; ?></i><i class="icon icon-comment"></i></a>
				</li>
				<li>
					<a href="#" class="icon icon-share"></a>
				</li>
			</ul>
		</div>
	</div>
	<div id="idea_info">
		<div class="idea_properties">
			<div class="property_item">
				<p>Опубликовано </p>
				<span><?php echo Yii::app()->getDateFormatter()->format('dd.MM.yyyy', $model->create_time); ?></span> / <a href="/idea/interiorpublic">Общественные интерьеры</a>
			</div>


			<?php if (in_array($author->role, array(User::ROLE_ADMIN, User::ROLE_MODERATOR, User::ROLE_JUNIORMODERATOR, User::ROLE_POWERADMIN, User::ROLE_SALEMANAGER, User::ROLE_SENIORMODERATOR))) : ?>
				<div class="property_item">
					<p>Автор публикации</p>
					<span>Редакция Myhome</span>
				</div>
				<?php if ( !empty($sources) ) : ?>
					<div class="property_item">
						<p>Источник</p>
						<?php foreach ($sources as $source) : ?>
						<a href="<?php echo CHtml::normalizeUrl($source->source_url);?>" target="_blank" class=""><?php echo $source->source_name;?></a><br />
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php else: ?>
				<div class="property_item">
					<p>Автор публикации</p>
					<?php echo CHtml::link($author->name, $author->getLinkProfile()); ?>
				</div>

				<?php if ( !empty($coauthors) ) : ?>
					<div class="property_item">
						<p>Соавторы</p>
						<?php foreach ($coauthors as $coauthor) {
							if ($coauthor->url) {
								$url = $coauthor->url;
								echo CHtml::link(CHtml::value($coauthor, 'name'), $url);
							} else {
								echo '<strong>'.CHtml::value($coauthor, 'name').'</strong>';
								echo '<br>';
							}
							echo '<div>'.CHtml::value($coauthor, 'specialization').'</div>';
						}
						?>
					</div>
				<?php endif; ?>
			<?php endif; ?>


			<div class="property_item">
				<p>Тип помещения</p>
				<span><?php echo CHtml::link($buildType->option_value, '/idea/interiorpublic/'.$buildType->eng_name); ?></span>
			</div>

			<div class="property_item">
				<p>Стиль</p>
				<span><?php echo CHtml::link($styles[$model->style_id]->option_value, '/idea/interiorpublic/'.$styles[$model->style_id]->eng_name); ?></span>
			</div>

			<div class="property_item">
				<ul class="colors_list">
				<?php foreach ($colorsList as $colorId) {
					$url = '/idea/interiorpublic/'.$colors[$colorId]->eng_name;
					echo CHtml::tag('li',
						array('class'=>$colors[$colorId]->param),
						CHtml::link('', $url, array('title'=>$colors[$colorId]->option_value))
					);
				} ?>
				</ul>
			</div>

		</div>
		<?php /*
		<div class="similar_ideas">
			<h2 class="block_head">Похожие идеи</h2>
			<table>
				<tr>
					<td><img src="/img/tmp/ideas/similar.jpg" alt="Флористический дизайн"/></td>
					<td><a href="#">Флористический дизайн</a></td>
				</tr>
				<tr>
					<td><img src="/img/tmp/ideas/similar.jpg" alt="Флористический дизайн"/></td>
					<td><a href="#">Флористический дизайн</a></td>
				</tr>
				<tr>
					<td><img src="/img/tmp/ideas/similar.jpg" alt="Флористический дизайн"/></td>
					<td><a href="#">Флористический дизайн</a></td>
				</tr>
			</table>
		</div>
 		*/ ?>
	</div>

</div>
<div class="clear"></div>

</div>

<script type="text/javascript">
	$(document).ready(function(){
		idea.setOptions(<?php echo json_encode(
			array('ideaType'=>Config::INTERIOR_PUBLIC,
			      'ideaId'=>$model->id,
			      'popupUrl' => '/idea/interiorpublic/popup/',
			), JSON_NUMERIC_CHECK); ?>);
		idea.commentScroll();
		idea.showPopup();
		js.scrollTop();
		idea.initSidebar();
	});
</script>

<div id="blind" class="hide">
	<div id="container">
	</div>
</div>