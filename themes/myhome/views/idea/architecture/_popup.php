<?php
/**
 * @var $model Architecture
 * @var $photoList array
 * @var $imgComp ImageComponent
 */

$imgComp = Yii::app()->img;
?>
<i class="close"></i>
<div class="player_left">
	<ul id= 'carousel' style = 'display: none;'>
	<?php
	foreach ($photoList as $photoId) {
		echo '<li><div id="origin_'.$photoId.'">';
		$src = $imgComp->getPreview($photoId, 'resize_1920x1080');
		echo CHtml::tag('img', array(
			'data-src' => $src,
			'width'=>$imgComp->getPreviewWidth($photoId, 'resize_1920x1080', 1920),
			'height'=>$imgComp->getPreviewHeight($photoId, 'resize_1920x1080', 1080)
		));
		echo '</div></li>';
	}
	?>
	</ul>

	<i class="prev page_arrow"></i>
	<i class="next page_arrow"></i>
</div>
<div class="player_right">
	<?php /** ROOMS LIST */ ?>
	<div id="room_id_1" class="popup_room_info">
		<?php
		$text = $model->name.'. '.$object->option_value;
		echo CHtml::tag('h2', array(), $text);
		?>
		<?php if ( !empty($styles) && isset($styles[$model->style_id]) ) : ?>
		<div class="room_styles">
			<?php echo CHtml::link($styles[$model->style_id]->option_value, $model->getFilterLink(array('object_type'=>$object->id, 'style'=>$model->style_id)) ); ?>
		</div>
		<?php endif; ?>

		<ul class="colors_list">
			<?php foreach ($colorsList as $colorId) {
				$url = $model->getFilterLink(array('object_type'=>$object->id, 'color'=>$colors[$colorId]->option_value));
				echo CHtml::tag('li',
					array('class'=>$colors[$colorId]->param),
					CHtml::link('', $url, array('title'=>$colors[$colorId]->option_value))
				);
			} ?>
			<div class="clear"></div>
		</ul>
		<div class="clear"></div>
	</div>
	<?php /** END ROOMS LIST */ ?>

	<?php /** IMAGE DESC LIST */ ?>
	<?php
	foreach ($photoList as $photoId) : ?>
		<div id="photo_id_<?php echo $photoId; ?>" class="popup_photo_info hide">
			<div class="photo_descript scrollbar">
				<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
				<div class="viewport">
					<div class="overview">
						<?php echo $imgComp->getDesc($photoId); ?>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
	<?php /** END IMAGE DESC LIST */ ?>

	<?php /** PREVIEW LIST */ ?>
	<div id="idea_thumbs">
		<?php if ($model->count_photos > 7) : ?>
		<a class="thumbs_arrow prev disabled" href="#"><i></i></a>
		<?php endif; ?>
		<?php
		$cnt = 0;
		foreach ($photoList as $photoId) {
			$cnt++;
			if ($cnt > 7 && $model->count_photos > 9)
				$class = 'hide';
			else
				$class = '';
			echo CHtml::link(
				CHtml::image( $imgComp->getPreview($photoId, 'crop_80')),
				'',
				array('id'=>'thumb'.$photoId,
				      'data-room'=>1,
					'data-photo'=>$photoId,
					'class'=>$class,
				)
			);
		} ?>
		<?php if ($model->count_photos > 7) : ?>
		<a class="thumbs_arrow next" href="#"><i></i></a>
		<?php endif; ?>
	</div>
	<?php /** END PREVIEW LIST */ ?>

	<?php $this->widget('ext.sharebox.EShareBox', array(
		'view' => 'ideaPopup',
		'url' => Yii::app()->request->hostInfo.$model->getIdeaLink().'/{id}/#p_{id}',
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