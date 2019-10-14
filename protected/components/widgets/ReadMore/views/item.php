<?php if($data) :?>
<h3 class="-inset-top">Читайте также</h3>
<div class="-grid similar-articles">
	<?php foreach($data as $da) : ?>
	<div class="-col-3">
		<a onclick = "_gaq.push(['_trackEvent','readMore','click']);return true;" href="<?php echo $da->getElementLink(); ?>">
			<img src="/<?php echo $da->preview->getPreviewName(MediaKnowledge::$preview['crop_220x130']); ?>" class="-rect-210-140">
			<?php echo CHtml::tag('span',array(),$da->title) ?>
		</a>
		<span class="-icon-eye-s"><?php echo $da->count_view ?></span>
		<span class="-icon-bubble-s"><?php echo $da->count_comment ?></span>
	</div>
	<?php endforeach ?>
</div>
<?php endif; ?>
