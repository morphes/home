<div id="control_meta" class="-col-2 -gutter-bottom-dbl -inset-bottom-dbl">
	<p class="-gutter-bottom-hf -medium -semibold">
		<i class="<?php echo $model===null ? '-red' : '-skyblue'; ?> -icon-switch-on"></i> SEO плагин</p>
	<?php
	if (is_null($model)) {
		$href = SeoMetaTag::getLink('create', array('url' => urlencode(Yii::app()->request->requestUri)));
		echo CHtml::link('Включить', '#', array('class'=>'commutator', 'data-href'=>$href));
	} else {
		$href = SeoMetaTag::getLink('update', array('id' => $model->id));
		echo CHtml::link('Редактировать', '#', array('class'=>'commutator', 'data-href'=>$href));
	}
	?>
</div>

<script type="text/javascript">
	$('#control_meta .commutator').click(function(){
		var href=$(this).data('href');
		window.location.href=href;
	});
</script>