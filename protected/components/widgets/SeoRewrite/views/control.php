<?php
/**
 * @var $model SeoRewrite
 */
?>

<div id="control_rewrite" class="-col-2 -gutter-bottom-dbl -inset-bottom-dbl">
	<p class="-gutter-bottom-hf -medium -semibold">
		<i class="<?php echo $model->status==SeoRewrite::STATUS_NO ? '-red' : '-skyblue'; ?> -icon-switch-on"></i> SEO rewrite</p>
	<?php
	if ( $model->status == SeoRewrite::STATUS_NO ) {
		$href = SeoRewrite::getLink('create', array('url' => urlencode(Yii::app()->getRequest()->getHostInfo().Yii::app()->getRequest()->getRequestUri() )));
		echo CHtml::link('Создать', '#', array('class'=>'commutator', 'data-href'=>$href));
	} else {
		$href = SeoRewrite::getLink('update', array('normal_md5' => urlencode($model->normal_md5) ));
		echo CHtml::link('Редактировать', '#', array('class'=>'commutator', 'data-href'=>$href));
	}

	?>
</div>

<script type="text/javascript">
	$('#control_rewrite .commutator').click(function(){
		var href=$(this).data('href');
		window.location.href=href;
	});
</script>