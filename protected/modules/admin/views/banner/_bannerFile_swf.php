<div class="clearfix swf-preview">
	<div class="input">
		<div>
			<object style="width: 940px;" scale="noscale" data="<?php echo $model->getSwfLink(); ?>" type="application/x-shockwave-flash">
				<param name="movie" value="<?php echo $model->getSwfLink(); ?>">
				<param name="quality" value="high">
				<param name="wmode" value="opaque">
				<param name="AllowScriptAccess" value="always">
				<param name="scale" value="noscale">
			</object>
		</div>
		<a class="delSwf" href="#">Удалить</a>
	</div>
</div>