<?php

if($storePrice->discount > 0) {
	$class = '-col-4 discount';
} else {
	$class = '-col-4';
}

?>

<div class="<?php echo $class ?>"  data-id="<?php echo $model->id?>" >
	<a href="<?php echo Yii::app()->params->bmHomeUrl.$model->Product->getElementLink(); ?>" class="-block">
		<?php echo CHtml::image('/' . $model->Product->cover->getPreviewName(Product::$preview['crop_300']), $model->Product->name, array('class' => '-quad-300')); ?>
		<span class="-block -large -gutter-top-hf -inset-left-hf -inset-right-hf -gutter-right"><?php echo Amputate::getLimb($model->Product->name, 60, '...'); ?></span>
	</a>

	<?php if ($price == 0.0)  : ?>
			<span class="-gray -gutter-left-hf">Цена не указана</span>
	<?php else : ?>

			<?php if($storePrice->discount > 0) { ?>
				<span class="-inline -large -gutter-left-hf"><strong><?php echo number_format($storePrice->getNumberDiscount(), 0, '.', ' ') ?> руб.</strong></span>
				<span class="-large -gray -gutter-left-hf"> <?php echo number_format($price, 0, '.', ' ') . ' руб.'; ?></span>
			<?php } else { ?>
				<strong class="-large -gutter-left-hf"><?php echo number_format($price, 0, '.', ' ') . ' руб.'; ?></strong>
			<?php } ?>



	<?php endif; ?>

	<?php if($isOwner) : ?>
		<span class="folder-owner -gray"><a href="#" class="-icon-cross-circle-xs -icon-only"></a></span>
	<?php endif; ?>

	<?php $this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
		'modelId'   => $model->model_id,
		'modelName' => get_class($model->Product),
		'cssClass'  => 'catalog'
	));?>
	<?php if($isOwner) : ?>
		<span data-product-id="<?php echo $model->Product->id ?>" class="-acronym -gray -small -gutter-left-hf discount-link">Указать скидку</span>
	<?php endif; ?>
</div>