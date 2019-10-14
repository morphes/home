<?php

/**
 * @var $cs CClientScript
 * @var $author User
 * @var $interior Interior
 * @var $layout UploadedFile
 */

$cs = Yii::app()->getClientScript();
$cs->registerCssFile('/css/ideas.css');

$cs->registerScriptFile('/js/scroll.js');
$cs->registerScriptFile('/js/CIdeas.js');
$cs->registerScriptFile('/js/jquery.popup.carousel.js');

$cs->registerScriptFile('/js/bootstrap-scrollspy.js');

$this->pageTitle = $interior->name.' — Интерьеры квартир, домов — MyHome.ru';
$this->description = $interior->desc;
$this->keywords = $interior->name.', интерьеры квартир, интерьеры домов, галерея интерьеров, идеи интерьера, майхоум, myhome, май хоум, myhome.ru';

// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----
/** Добавление OG тегов в контроллере */
Yii::app()->openGraph->renderTags();
?>

<div class="pathBar">

	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(
			'Идеи' => array('/idea'),
			'Галерея интерьеров' => array('/idea/interior'),
		),
		'encodeLabel' => false
	));?>

	<h1><?php
		echo CHtml::value($interior, 'name');
		if (Yii::app()->user->checkAccess(array(User::ROLE_ADMIN, User::ROLE_JUNIORMODERATOR, User::ROLE_MODERATOR, User::ROLE_POWERADMIN, User::ROLE_SENIORMODERATOR))) {
			echo CHtml::link('[ред.]', Yii::app()->createUrl($this->module->id.'/admin/create/interior', array('id'=>$interior->id)));
		}
		?></h1>
	<div class="spacer"></div>
</div>
<div id="right_side" class="new_template">
<?php Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_item_above'); ?>

<?php /** Вывод описания интерьера */
if (mb_strlen($interior->desc, 'utf-8') <= 365) : ?>
	<p class="description"><?php echo nl2br(CHtml::value($interior, 'desc')); ?></p>
<?php else : ?>
	<p class="description">
		<span class="desc_min"><?php echo nl2br(Amputate::getLimb( $interior->desc, 365, '')); ?></span>
		<span class="ellipsis">...</span>
		<span class="hide"><?php echo nl2br($interior->desc); ?></span>
	</p>
	<span class="all_elements_link">
		<span>Показать полностью</span><span class="arrow">&darr;</span>
	</span>

<?php endif; ?>

<script type="text/javascript">
	idea.showDescript();
</script>
<div class="photos">
<?php
$countPhotos = array();
/*
 * Вычисляем после какого по счету помещения выводить Яндекс.Директ
 */
$directNumber = ceil(count($contents) / 2);

$ind = 1;

/** @var $content InteriorContent */
foreach ($contents as $content) { ?>
	<div class="room" id="<?php echo $content->getHash(); ?>">
		<div class="room_head">
			<?php echo CHtml::tag('h2', array(), $rooms[$content->room_id]->option_value); ?>
			<ul class="room_property">
				<?php
				$tags = $content->getTags();
				$photos = $content->getPhotos();
				/** Подсчет фото для вывода в неск местах */
				$countPhotos[$content->id] = count($photos);
				$styleUrl = '/idea/interior/'.$rooms[$content->room_id]->eng_name.'-'.$styles[$content->style_id]->eng_name;
				$colorsList = $content->getColorsList();
				?>
				<?php if (!empty($tags)) : ?>
					<li class="tags "><span>Метки <?php echo count($tags); ?></span> &darr;</li>
				<?php endif; ?>
				<li class="photos_quant"><i></i><span><?php echo $countPhotos[$content->id]; ?></span></li>
				<li><a href="<?php echo $styleUrl; ?>"><span class="text_block">стиль </span><?php echo $styles[$content->style_id]->option_value; ?><span class="text_block"> в интерьере <?php echo $rooms[$content->room_id]->param; ?></span></a></li>
				<li>
					<ul class="colors_list">
						<?php foreach ($colorsList as $colorId) {
							$url = '/idea/interior/'.$rooms[$content->room_id]->eng_name.'-'.$colors[$colorId]->eng_name;
							echo CHtml::tag('li',
								array('class'=>$colors[$colorId]->param),
								CHtml::link(
									CHtml::tag('span', array('class'=>'text_block'), $colors[$colorId]->option_value.' цвет в интерьере '.$rooms[$content->room_id]->param),
									$url,
									array('title'=>$colors[$colorId]->option_value)
								)
							);
						} ?>
					</ul>
				</li>
			</ul>
			<div class="tags_list hide">
				<?php
				$cnt = 0;
				/** @var $tag Tag */
				foreach ($tags as $tag) {
					if ($cnt == 0)
						$cnt++;
					else
						echo ', ';
					$tagName = trim($tag->name);
					echo CHtml::link( $tagName, $interior->getFilterLink(array('tags-list'=>$tagName)));
				}
				?>
			</div>
		</div>
		<?php

		/** @var $photo UploadedFile */
		foreach ($photos as $photo) : ?>
			<div class="photo_item" id="<?php echo 'p_'.$photo->id; ?>">
				<?php $text = $interior->name.'. '.$rooms[$content->room_id]->option_value; ?>
				<div class="photo_item_img">
					<div class="image_container">

						<?php
						$fileName = $photo->getPreviewName(InteriorContent::$preview['width_520'], 'interiorContent');
						$imageSize = UploadedFile::getImageSize($fileName);
						$params = array_merge( array('data-id'=>$photo->id), $imageSize );
						?>

						<?php echo CHtml::image('/'.$fileName, $text, $params); ?>


						<?php
						/*
						 * Т О В А Р Ы, привязанные к фото
						 */
						if($this->beginCache("productsOnPhoto{$photo->id}", array(
							'duration'   => 3600,
						)))
						{
							Yii::import('application.modules.catalog.models.*');
							$productsOnPhoto = ProductOnPhotos::model()->findAllByAttributes(array('ufile_id' => $photo->id));
							foreach($productsOnPhoto as $prod) {
								$offset = unserialize($prod->params);
								?>
								<div class="product_label" style="<?php echo "top:{$offset['top']}; left:{$offset['left']}";?>" data-left="<?php echo $offset['left'];?>" data-top="<?php echo $offset['top'];?>">
									<i class="-icon-tag-s -relative -icon-round"></i>
									<div class="product_item">
										<span class="similar"><?php echo ProductOnPhotos::$typeNames[$prod->type]; ?></span>
										<a class="item_name" title="<?php echo $prod->product->name; ?>" href="<?php echo Product::getLink($prod->product->id, null, $prod->product->category_id); ?>">
											<?php echo CHtml::image('/'.$prod->product->cover->getPreviewName(Product::$preview['crop_60']), $prod->product->name, array('width' => '60', 'height' => '60')); ?>
											<?php echo $prod->product->name;?>
										</a><br>
										<?php
										$price = StorePrice::getPriceOffer($prod->product->id);
										$strPrice = '';
										if ($price['min'] == 0.0 && $price['mid'] == 0.0)
										{
											$strPrice = CHtml::tag('span', array('class' => 'price not_specified'), 'Цена не указана');
										}
										elseif ($price['min'] == 0.0 && $price['mid'] > 0)
										{
											$strPrice = CHtml::tag('span', array('class' => 'price'), number_format($price['mid'], 0, '.', ' ').' руб.');
										}
										elseif ($price['min'] > 0 && $price['mid'] > 0) {
											$strPrice = CHtml::tag('span', array('class' => 'price'), 'от '.number_format($price['min'], 0, '.', ' ').' руб.');
										}
										?>
										<span class="vendor"><span><?php echo Country::model()->findByPk((int)$prod->product->country)->name;?></span>, <a href="<?php echo Vendor::getLink($prod->product->vendor_id);?>"><?php echo $prod->product->vendor->name;?></a></span>
										<?php echo $strPrice;?>

									</div>
								</div>
							<?php
							}

							$this->endCache();
						}
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
						'url' => Yii::app()->request->hostInfo.$interior->getIdeaLink().'/#p_'.$photo->id,
						'imgUrl' =>Yii::app()->request->hostInfo.'/'.$photo->getPreviewName(InteriorContent::$preview['resize_1920x1080']),
						'title'=> $text,
						'message' => $photo->desc,
						'classDefinitions' => array(
							'vkontakte' => 'vk',
							'twitter' => 'tw',
							'facebook' => 'fb',
							'google+' => 'gp',
							'odkl' => 'ok',
							'pinterest' => 'pi'
						),
						'exclude' => array('livejournal'),
						'htmlOptions' => array('class' => 'share_block'),
					));?>

					<?php $this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
						'modelId' => $photo->id,
						'modelName' => get_class($photo),
						'viewHeart' => 'favoriteImages',
						'deleteItem' => true,
						'data' => array(
							'parent_object_class'=>get_class($interior),
							'parent_object_id'=>$interior->id,
						),
					));?>
				</div>
				<div class="clear"></div>
			</div>
		<?php endforeach; ?>

	</div>

<?php } ?>

<?php if (count($layouts) > 0): ?>

	<div class="room" id="layout">
		<div class="room_head">
			<?php echo CHtml::tag('h2', array(), 'Планировки'); ?>
			<ul class="room_property">
				<li class="photos_quant"><i></i><span><?php echo count($layouts); ?></span></li>
			</ul>
		</div>
		<?php

		/** @var $photo UploadedFile */
		foreach ($layouts as $layout) : ?>
			<div class="photo_item" id="<?php echo 'p_'.$layout->id; ?>">
				<?php $text = $interior->name.'. Планировки'; ?>
				<div class="photo_item_img">
					<div class="image_container">
						<?php
						$fileName = $layout->getPreviewName(Interior::$preview['width_520'], 'interior');
						$params = array_merge( array('data-id'=>$layout->id), UploadedFile::getImageSize($fileName) );
						echo CHtml::image('/'.$fileName, $text, $params);
						?>
					</div>
				</div>
				<div class="photo_item_desc">
					<div class="photo_head">
						<p><?php echo $text; ?></p>
						<a href="<?php echo '#p_'.$layout->id; ?>">#</a>
					</div>

					<?php if (!empty( $layout->desc )) {
						echo CHtml::tag('p', array(), $layout->desc);
					} ?>

					<?php $this->widget('ext.sharebox.EShareBox', array(
						'view' => 'idea',
						'url' => Yii::app()->request->hostInfo.$interior->getIdeaLink().'/#p_'.$layout->id,
						'title'=> $text,
						'message' => $layout->desc,
						'imgUrl' =>Yii::app()->request->hostInfo.'/'.$layout->getPreviewName(InteriorContent::$preview['resize_1920x1080']),
						'classDefinitions' => array(
							'vkontakte' => 'vk',
							'twitter' => 'tw',
							'facebook' => 'fb',
							'google+' => 'gp',
							'odkl' => 'ok',
							'pinterest' => 'pi'
						),
						'exclude' => array('livejournal'),
						'htmlOptions' => array('class' => 'share_block'),
					));?>
				</div>
				<div class="clear"></div>
			</div>
		<?php endforeach; ?>

	</div>

<?php endif; ?>


	<a href="/products/?utm_content=cat-idea-item-banner" class="ideaHorCard">
		<div class="ideaHorCard-img">
			<img src="/img/idea-img-shower.png" alt="">
		</div>
		<div class="ideaHorCard-text"><strong>85 140</strong> товаров для вашего дома<br>по низким ценам в каталоге MyHome.ru</div>
		<div class="ideaHorCard-btn">Перейти в каталог</div>
	</a>


<script type="text/javascript">
	idea.showTags();
	idea.initProductLayer();
</script>



<?php // Несколько последних комментариев
$this->widget('application.components.widgets.WComment', array(
	'model' => $interior,
	'hideComments' => !$interior->getCommentsVisibility(),
	'showCnt' => 0,
));?>
<div class="spacer-30"></div>

</div>
</div>
<div id="left_side" class="-new_template">
	<div class="idea_sidebar" data-scrollspy="true">

		<div class="idea_sidebar_item">
			<?php echo CHtml::image('/'.$interior->getPreview(Interior::$preview['crop_180']), $interior->name, array('class'=>'cover', 'width'=>180, 'height'=>180)); ?>
			<?php
			$this->widget('application.components.widgets.WStar', array(
				'selectedStar' => $interior->average_rating,
				'addSpanClass' => 'rating-s',
			));
			?>
			<div class="block_item_counters">
				<span class="views_quant"><i></i><?php echo $viewCount; ?></span>
				<span class="photos_quant"><i></i><?php echo $interior->count_photos; ?></span>
				<a href="#" class="comments_quant"><i></i><?php echo $interior->count_comment; ?></a>
			</div>
			<div class="clear"></div>
		</div>

		<?php if ( !empty($contents) || !empty($layouts)) : ?>
			<div class="idea_sidebar_item">
				<ul class="room_list navbar">
					<?php foreach ($contents as $content) : ?>
						<li>
							<a href="<?php echo '#'.$content->getHash(); ?>">
								<span><?php echo $countPhotos[$content->id]; ?><i></i></span>
								<i><?php echo $rooms[$content->room_id]->option_value; ?></i>
							</a>
						</li>

					<?php endforeach;
					if (count($layouts) > 0) : ?>
						<li>
							<a href="#layout">
								<span><?php echo count($layouts); ?><i></i></span>
								<i>Планировки</i>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="idea_sidebar_item last">
			<div class="sidebar-tools comment">
				<span class="-icon-bubble-s -red"><i class="-acronym">Комментировать</i></span>
			</div>

			<?php // Подключаем виджет для добавления в избранное
			$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
				'modelId'   => $interior->id,
				'modelName' => get_class($interior),
				'viewHeart' => 'favoriteMedia'
			));?>
		</div>

		<?php $this->widget('ext.sharebox.EShareBox', array(
			'view' => 'mainIdea',
			'url' => Yii::app()->request->hostInfo.$interior->getIdeaLink(),
			'title'=> $interior->name,
			'message' => $interior->desc,
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
					<a href="#" class="pseudo-link"><i><?php echo $interior->count_comment; ?></i><i class="icon icon-comment"></i></a>
				</li>
				<li>
					<a href="#" class="icon icon-share"></a>
				</li>

				<ul class="ideas-seeAlso" style="padding-left: 0; text-align: left;">
					<span>Смотрите также:</span>
					<li><a href="/products/hygiene/?utm_content=cat-idea-item-sidebar">Мебель для ванной</a></li>
					<li><a href="/products/bathroom_furniture/?utm_content=cat-idea-item-sidebar">Сантехника</a></li>
					<li><a href="/products/kitchen_furniture/?utm_content=cat-idea-item-sidebar">Мебель для кухни</a></li>
					<li><a href="/products/living_headset/?utm_content=cat-idea-item-sidebar">Гостиные</a></li>
					<li><a href="/products/sofas/?utm_content=cat-idea-item-sidebar">Диваны</a></li>
					<li><a href="/products/juvenile/?utm_content=cat-idea-item-sidebar">Детская мебель</a></li>
					<li><a href="/products/hall_furniture_sets/?utm_content=cat-idea-item-sidebar">Прихожие</a></li>
				</ul>
			</ul>
		</div>
	</div>
	<div id="idea_info">
		<div class="idea_properties">
			<div class="property_item">
				<p>Опубликовано </p>
				<span><?php echo Yii::app()->getDateFormatter()->format('dd.MM.yyyy', $interior->create_time); ?></span> / <a href="/idea/interior">Дизайн интерьера</a>
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

<script type="text/javascript">
	$(document).ready(function(){
		idea.setOptions(<?php echo json_encode(
			array('ideaType'=>Config::INTERIOR,
				'ideaId'=>$interior->id,
				'popupUrl' => '/idea/interior/popup/',
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