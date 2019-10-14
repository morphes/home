<?php
/**
 * @var $model Architecture
 * @var $author User
 * @var $photoList array
 * @var $object IdeaHeap
 * @var $materials array
 * @var $styles array
 * @var $floors array
 * @var $colorsList array
 */
/** @var $imgComp ImageComponent */
$imgComp = Yii::app()->img;

$this->pageTitle = $model->name.' — Архитектура — MyHome.ru';
$this->description = '';
$this->keywords = '';


$cs = Yii::app()->getClientScript();
$cs->registerCssFile('/css/ideas.css');

$cs->registerScriptFile('/js/scroll.js');
$cs->registerScriptFile('/js/CIdeas.js');
$cs->registerScriptFile('/js/jquery.popup.carousel.js');
$cs->registerScriptFile('/js/bootstrap-scrollspy.js');

// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----
Yii::app()->openGraph->renderTags();
?>

<div class="pathBar">

	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(
			'Идеи<span class="text_block"> для интерьера</span>' => array('/idea'),
			'Архитектура' => array('/idea/architecture/catalog'),
		),
		'encodeLabel' => false
	));?>
	<h1><?php
		echo CHtml::value($model, 'name');
		if (Yii::app()->user->checkAccess(array(User::ROLE_ADMIN, User::ROLE_JUNIORMODERATOR, User::ROLE_MODERATOR, User::ROLE_POWERADMIN, User::ROLE_SENIORMODERATOR))) {
			echo CHtml::link('[ред.]', Yii::app()->createUrl('/idea/admin/architecture/update', array('id'=>$model->id)));
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
		/** @var $photoId UploadedFile */
		foreach ($photoList as $photoId) : ?>
			<div class="photo_item" id="<?php echo 'p_'.$photoId; ?>">
				<?php $text = $model->name.'. '.$object->option_value; ?>
				<div class="photo_item_img">
					<div class="image_container">
					<?php
					$fileName = $imgComp->getPreview($photoId, 'width_520');
					$params = array('data-id'=>$photoId, 'width'=>520, 'height'=>$imgComp->getPreviewHeight($photoId, 'width_520'));
					echo CHtml::image($fileName, $text, $params);
					?>
					</div>
				</div>
				<div class="photo_item_desc">
					<div class="photo_head">
						<p><?php echo $text; ?></p>
						<a href="<?php echo '#p_'.$photoId; ?>">#</a>
					</div>

					<?php
					$photoDesc =  $imgComp->getDesc($photoId);
					if ( !empty($photoDesc) ) {
						echo CHtml::tag('p', array(), $photoDesc);
					} ?>

					<?php $this->widget('ext.sharebox.EShareBox', array(
						'view' => 'idea',
						'url' => Yii::app()->request->hostInfo.$model->getIdeaLink().'/#p_'.$photoId,
						'title'=> $text,
						'imgUrl' => $imgComp->getPreview($photoId, 'resize_1920x1080'),
						'message' => $photoDesc,
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
			<?php echo CHtml::image($imgComp->getPreview($model->image_id, 'crop_180'), $model->name, array('class'=>'cover', 'width'=>180, 'height'=>180)); ?>
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
				<span><?php echo Yii::app()->getDateFormatter()->format('dd.MM.yyyy', $model->create_time); ?></span> / <a href="/idea/architecture">Архитектура</a>
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
				<p>Тип объекта</p>
				<span><?php echo CHtml::link($object->option_value, $model->getFilterLink(array('object_type'=>$object->id)) ); ?></span>
			</div>

			<?php if (!empty($styles) && !empty($model->style_id)) : ?>
			<div class="property_item">
				<p>Стиль</p>
				<span><?php echo CHtml::link($styles[$model->style_id]->option_value, $model->getFilterLink(array('object_type'=>$object->id, 'style'=>$model->style_id)) ); ?></span>
			</div>
			<?php endif; ?>


			<?php if (!empty($materials) && !empty($model->material_id)) : ?>
			<div class="property_item">
				<p>Материал</p>
				<span><?php echo CHtml::link($materials[$model->material_id]->option_value, $model->getFilterLink(array('object_type'=>$object->id, 'material'=>$model->material_id)) ); ?></span>
			</div>
			<?php endif; ?>

			<?php if (!empty($floors) && !empty($model->floor_id)) : ?>
			<div class="property_item">
				<p>Этажность</p>
				<span><?php echo CHtml::link($floors[$model->floor_id]->option_value, $model->getFilterLink(array('object_type'=>$object->id, 'floor'=>$model->floor_id)) ); ?></span>
			</div>
			<?php endif; ?>

			<?php if ($model->room_mansard || $model->room_garage || $model->room_ground || $model->room_basement) : ?>
			<div class="property_item">
				<p>Дополнительные строения</p>
					<?php
					if ($model->room_mansard) {
						echo CHtml::tag('div', array(),
							CHtml::link('Мансарда', $model->getFilterLink( array('object_type'=>$object->id, 'room'=>'mansard') ) )
						);
					}
					if ($model->room_garage) {
						echo CHtml::tag('div', array(),
							CHtml::link('Гараж', $model->getFilterLink( array('object_type'=>$object->id, 'room'=>'garage') ) )
						);
					}
					if ($model->room_ground) {
						echo CHtml::tag('div', array(),
							CHtml::link('Цокольный этаж', $model->getFilterLink( array('object_type'=>$object->id, 'room'=>'ground') ) )
						);
					}
					if ($model->room_basement) {
						echo CHtml::tag('div', array(),
							CHtml::link('Подвал', $model->getFilterLink( array('object_type'=>$object->id, 'room'=>'basement') ) )
						);
					}
					?>
			</div>
			<?php endif; ?>


			<div class="property_item">
				<ul class="colors_list">
					<?php foreach ($colorsList as $colorId) {
						if($colorId==null)
						{
							continue;
						}
					$url = $model->getFilterLink(array('object_type'=>$object->id, 'color'=>$colors[$colorId]->option_value));
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
			array('ideaType'=>Config::ARCHITECTURE,
			      'ideaId'=>$model->id,
			      'popupUrl' => '/idea/architecture/popup/',
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