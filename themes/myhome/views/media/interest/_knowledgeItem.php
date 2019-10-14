<div class="-col-3">
	<a class="-block" href="<?php echo $model->getElementLink() ?>" onclick = "_gaq.push(['_trackEvent','interest','click']);return true;">
		<img src="/<?php echo $model->preview->getPreviewName(MediaKnowledge::$preview['crop_220x175']); ?>" class="-rect-220-175">

		<span><?php echo $model->title; ?></span>
	</a>
	<span class="-block"><?php echo $model->lead ?></span>
	<span class="-icon-eye-s -gray -gutter-right-hf"><?php echo $model->count_view ?></span>
	<span class="-icon-bubble-s -gray -gutter-right-hf"><?php echo $model->count_comment ?></span>
	<span class="-icon-thumb-up-s -gray -gutter-right-hf"><?php echo LikeItem::model()->countLikes(get_class($model),$model->id);?></span>

</div>


