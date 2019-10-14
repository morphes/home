<div class="-gutter-bottom-dbl -inset-bottom -gutter-top-dbl -inset-top -clear">
	<?php echo CHtml::link(
		CHtml::image('/'.$src, '', array('width'=>700)),
		$url,
		array('onclick'=>'_gaq.push(["_trackEvent","Banner","click"]); return true;')
	); ?>
</div>