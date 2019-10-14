<?php
/**
 * @var $interior Interior
 * @var $content InteriorContent
 * @var $photo UploadedFile
 */
$photoList = array();
$layoutsCount = count($layouts);
?>

<i class="close"></i>
<div class="player_left">
	<ul id= 'carousel' style = 'display: none;'>
	<?php foreach ($contents as $content) {
		$contentPhotos = $content->getPhotos();

		foreach ($contentPhotos as $photo) {
			echo '<li><div id="origin_'.$photo->id.'">';
			$src = $photo->getPreviewName(InteriorContent::$preview['resize_1920x1080']);
			echo CHtml::tag('img', array('data-src' => '/'.$src)+UploadedFile::getImageSize($src));
			echo '</div></li>';
		}
		$photos[$content->id] = $contentPhotos;
	}

	foreach ($layouts as $layout) {
		echo '<li><div id="origin_'.$layout->id.'">';
		$src = $layout->getPreviewName(InteriorContent::$preview['resize_1920x1080']);
		echo CHtml::tag('img', array('data-src' => '/'.$src)+UploadedFile::getImageSize($src));
		echo '</div></li>';
	}
	?>
	</ul>

	<i class="prev page_arrow"></i>
	<i class="next page_arrow"></i>
</div>
<div class="player_right">
	<?php /** ROOMS LIST */ ?>
	<?php foreach ($contents as $content) : ?>

	<div id="<?php echo $content->getPopupHash(); ?>" class="popup_room_info">
		<?php
		$text = $interior->name.'. '.$rooms[$content->room_id]->option_value;
		$styleUrl = '/idea/interior/'.$rooms[$content->room_id]->eng_name.'-'.$styles[$content->style_id]->eng_name;
		$colorsList = $content->getColorsList();

		echo CHtml::tag('h2', array(), $text);

		?>
		<div class="room_styles">
			<?php echo CHtml::link($styles[$content->style_id]->option_value, $styleUrl); ?>
		</div>
		<ul class="colors_list">
			<?php foreach ($colorsList as $colorId) {
				$url = '/idea/interior/'.$rooms[$content->room_id]->eng_name.'-'.$colors[$colorId]->eng_name;
				echo CHtml::tag('li',
					array('class'=>$colors[$colorId]->param),
					CHtml::link('', $url, array('title'=>$colors[$colorId]->option_value))
				);
			} ?>
			<div class="clear"></div>
		</ul>
		<div class="clear"></div>
	</div>

	<?php endforeach; ?>
	<?php if ($layoutsCount > 0) : ?>
	<div id="room_id_0" class="popup_room_info">
		<?php
		$text = $interior->name.'. Планировки';
		echo CHtml::tag('h2', array(), $text);
		?>
		<div class="clear"></div>
	</div>
	<?php endif; ?>
	<?php /** END ROOMS LIST */ ?>

	<?php /** IMAGE DESC LIST */ ?>
	<?php
	foreach ($photos as $contentId => $contentPhotos) {
		foreach ($contentPhotos as $photo) { ?>
		<div id="photo_id_<?php echo $photo->id; ?>" class="popup_photo_info hide">
			<div class="photo_descript scrollbar">
				<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
				<div class="viewport">
					<div class="overview">
						<?php echo $photo->desc; ?>
					</div>
				</div>
			</div>
		</div>
	<?php }
	} ?>
	<?php foreach ($layouts as $layout) : ?>
		<div id="photo_id_<?php echo $layout->id; ?>" class="popup_photo_info hide">
			<div class="photo_descript scrollbar">
				<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
				<div class="viewport">
					<div class="overview">
						<?php echo $layout->desc; ?>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>



	<?php /** END IMAGE DESC LIST */ ?>

	<?php /** PREVIEW LIST */ ?>
	<div id="idea_thumbs">
		<?php if ($interior->count_photos > 9) : ?>
		<a class="thumbs_arrow prev disabled" href="#"><i></i></a>
		<?php endif; ?>
		<?php
		$cnt = 0;
		foreach ($photos as $contentId => $contentPhotos) {
			foreach ($contentPhotos as $photo) {
				$cnt++;
				if ($cnt > 7 && $interior->count_photos > 9)
					$class = 'hide';
				else
					$class = '';

				echo CHtml::link(
					CHtml::image('/'.$photo->getPreviewName(InteriorContent::$preview['crop_80'])),
					'',
					array('id'=>'thumb'.$photo->id,
					      'data-room'=>$contentId,
						'data-photo'=>$photo->id,
						'class'=>$class,
					)
				);
			}
		} ?>
		<?php
		foreach ($layouts as $layout) {
			$cnt++;
			if ($cnt > 7 && $interior->count_photos > 9)
				$class = 'hide';
			else
				$class = '';

			echo CHtml::link(
				CHtml::image('/'.$layout->getPreviewName(Interior::$preview['crop_80'])),
				'',
				array('id'=>'thumb'.$layout->id,
				      'data-room'=>0,
				      'data-photo'=>$layout->id,
					'class'=>$class,
				)
			);
		}

		?>
		<?php if ($interior->count_photos > 9) : ?>
		<a class="thumbs_arrow next" href="#"><i></i></a>
		<?php endif; ?>
	</div>
	<?php /** END PREVIEW LIST */ ?>

	<?php $this->widget('ext.sharebox.EShareBox', array(
		'view' => 'ideaPopup',
		'url' => Yii::app()->request->hostInfo.$interior->getIdeaLink().'/{id}/#p_{id}',
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

</div>