<?php $countModels = count($models); ?>

<?php if ($countModels > 0): ?>
	<div class="favorite_item">
		<h3>Изображения<span class="items_count quant_f"><?php echo $countModels;?></span></h3>
		<span class="hide_items"><i></i><a href="#">Свернуть</a></span>

		<div class="favorite_list_conteiner">
			<div class="gallery-210">

				<?php foreach($items as $item) : ?>

					<div class="item" style="height: 235px;">

						<div class="item_photo">
							<a href="<?php echo $item->getParentObject() ? $item->getParentObject()->getIdeaLink() . '#p_' . $item->model_id : ''; ?>">
								<img align="left" width="210" height="210" alt="" src="<?php echo $item->getFavoriteObject() ? '/' . $item->getFavoriteObject()->getPreviewName(Config::$preview['crop_210']) : ''; ?>">
							</a>
						</div>

						<?php // Подключаем виджет для добавления в избранное
						$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
							'modelId' => $item->model_id,
							'modelName' => $item->model,
							'cssClass' => 'idea',
							'deleteItem' => true,
							'data' => $item->getData(),
						));?>

					</div>
				<?php endforeach; ?>

				<div class="clear"></div>
			</div>
		</div>
	</div>
<?php endif; ?>