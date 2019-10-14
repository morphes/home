<?php
if ( ! isset($comment) || ! $comment instanceof Comment)
	return;
?>

<div class="-col-3">
	<?php
	if(!$comment->guest_id) {
		?>
		<div class="-col-wrap">
			<?php echo  CHtml::image('/'.$comment->author->getPreview( Config::$preview['crop_45'] ), '', array('width' => 45, 'height' => 45, 'class' => '-quad-45'));?>
		</div>
		<div class="-col-wrap -inset-left-hf">
			<a href="<?php echo $comment->author->getLinkProfile();?>"><?php echo $comment->author->name;?></a>
			<span class="-block -gray -small -gutter-top-hf"><?php echo CFormatterEx::formatDateToday($comment->create_time);?></span>
		</div>
	<?php
	} else {
		echo '<div class="-col-wrap -inset-left-hf">';
		echo CHtml::tag('span', array('class' => 'guest'), $comment->author->name);
		echo '</div>';
	}
	?>
</div>
<div class="-col-5 -gray">
	<?php echo nl2br(CHtml::value($comment, 'message'));?>
</div>
<hr class="-col-8 -dotted -gutter-top -gutter-bottom-dbl">