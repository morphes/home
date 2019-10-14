<div class="item" id="1">
	<div class="item_image">
		<a href="<?php echo $data->getElementLink();?>"><img src="/<?php echo $data->preview->getPreviewName(MediaNew::$preview['crop_160x110']);?>"/></a>
	</div>
	<div class="descript">
		<h2><a class="item_head" href="<?php echo $data->getElementLink();?>"><?php echo $data->title;?></a></h2>

		<p><?php echo Amputate::getLimb($data->lead, '220');?></p>

		<div class="item_info">
			<div class="block_item_info">

				<?php //• <a>Дизайн интерьера</a>?>
			</div>
			<div class="block_item_counters">
				<span class="-icon-eye-s -small -gray -gutter-left"><?php echo number_format($data->count_view, 0, '', ' '); ?></span>
				<span class="-pseudolink -icon-bubble-s -small -gray -gutter-left"><a href="<?php echo $data->getElementLink(); ?>#comments"><i></i><?php echo $data->count_comment;?></a></span>
				<span class="-icon-thumb-up-xs -small -gray -gutter-left"><?php echo LikeItem::model()->countLikes(get_class($data),$data->id);?></span>
			</div>
		</div>
	</div>
	<div class="clear"></div>
</div>