<?php $countModels = count($models); ?>

<?php if ($countModels > 0): ?>
	<div class="favorite_item">
		<h3>Специалисты<span class="items_count quant_f"><?php echo $countModels; ?></span></h3>
		<span class="hide_items"><i></i><a href="#">Свернуть</a></span>

		<div class="favorite_list_conteiner">
			<div class="specialists">

				<?php if ($models) : ?>
					<?php foreach($models as $model) : ?>

					<?php $this->renderPartial('//member/profile/specialist/_favoriteUserItem', array(
						'data' => $model,

					)); ?>

					<?php endforeach; ?>
				<?php endif; ?>

				<div class="clear"></div>
			</div>
		</div>
	</div>
<?php endif; ?>