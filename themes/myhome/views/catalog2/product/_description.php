<!-- Описание товара //-->

<div class="-col-3">
	<span class="-giant">Описание товара</span>
</div>
<div class="-col-9 product-desc">
	<?php if ($model->desc) : ?>

		<?php
		$visibleDesc = Amputate::getLimb($model->desc, 500, '', 'UTF-8', true);
		$descLength = mb_strlen($model->desc, 'utf-8');
		$hiddenDesc = mb_substr($model->desc, 500, $descLength - 500, 'utf-8');
		?>
		<p class="desc">
			<span class="visible"><?php echo nl2br($visibleDesc); ?></span><span class="hidden"><?php echo nl2br($hiddenDesc); ?> </span>
		</p>
		<?php if ($descLength > 500) : ?>
			<span class="-acronym -gray toggle-desc"
			      data-title="Свернуть описание">Полное описание</span>
		<?php endif; ?>
	<?php endif; ?>
	<div class="-grid -inset-top specs">
		<?php $this->widget('catalog.components.widgets.CatFullcardOptionsGrid', array('model' => $model)); ?>
	</div>
	<span class="-acronym -gray toggle-specs"
	      data-title="Свернуть характеристики">Все характеристики</span>


</div>