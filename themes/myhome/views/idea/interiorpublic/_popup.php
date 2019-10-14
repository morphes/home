<?php
/**
 * @var $model Interiorpublic
 * @var $photo UploadedFile
 */
?>
<i class="close"></i>
<div class="player_left">
	<ul id= 'carousel' style = 'display: none;'>
	<?php
	foreach ($photos as $photo) {
		echo '<li><div id="origin_'.$photo->id.'">';
		$src = $photo->getPreviewName(InteriorContent::$preview['resize_1920x1080']);
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
	<div id="room_id_1" class="popup_room_info">
		<?php
		$text = $model->name.'. '.$buildType->option_value;
		$styleUrl = '/idea/interiorpublic/'.$styles[$model->style_id]->eng_name;
		echo CHtml::tag('h2', array(), $text);
		?>
		<div class="room_styles">
			<?php echo CHtml::link($styles[$model->style_id]->option_value, $styleUrl); ?>
		</div>
		<ul class="colors_list">
			<?php foreach ($colorsList as $colorId) {
				$url = '/idea/interiorpublic/'.$colors[$colorId]->eng_name;
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
	foreach ($photos as $photo) : ?>
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
	<?php endforeach; ?>
	<?php /** END IMAGE DESC LIST */ ?>

	<?php /** PREVIEW LIST */ ?>
	<div id="idea_thumbs">
		<?php if ($model->count_photos > 7) : ?>
		<a class="thumbs_arrow prev disabled" href="#"><i></i></a>
		<?php endif; ?>
		<?php
		$cnt = 0;
		foreach ($photos as $photo) {
			$cnt++;
			if ($cnt > 7 && $model->count_photos > 9)
				$class = 'hide';
			else
				$class = '';
			echo CHtml::link(
				CHtml::image('/'.$photo->getPreviewName(Interiorpublic::$preview['crop_80'])),
				'',
				array('id'=>'thumb'.$photo->id,
				      'data-room'=>1,
					'data-photo'=>$photo->id,
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