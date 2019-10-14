<?php Yii::app()->clientScript->registerCssFile('/css/media.css'); ?>

<?php $countModels = count($models); ?>

<?php if ($countModels > 0): ?>
<div class="favorite_item">
	<h3>Новости магазинов<span class="items_count quont_f"><?php echo $countModels;?></span></h3>
	<span class="hide_items"><i></i><a href="#">Свернуть</a></span>
	<div class="favorite_list_conteiner">
		<div class="knowledge_items list">

			<?php foreach($models as $data) : ?>
			<div class="item" id="<?php echo $data->id;?>">
				<div class="item_image">
					<a href="<?php echo StoreNews::getLink($data->store, 'element', $data->id);?>">
						<img src="/<?php echo $data->preview->getPreviewName(StoreNews::$preview['crop_140']);?>"/>
					</a>
				</div>
				<div class="descript">
					<h2><a class="item_head" href="<?php echo StoreNews::getLink($data->store, 'element', $data->id);?>"><?php echo $data->title;?></a></h2>

					<p><?php echo Amputate::getLimb($data->content, '220');?></p>

					<div class="item_info">
						<div class="block_item_info">
							<span><?php echo CFormatterEx::formatDateToday($data->create_time);?></span>
							<?php //• <a>Дизайн интерьера</a>?>
						</div>
						<div class="block_item_counters">
							<span class="comments_quant" title="комментарии"><a href="<?php echo StoreNews::getLink($data->store, 'element', $data->id);?>#comments"><i></i><?php echo $data->count_comment;?></a></span>
						</div>
					</div>

					<?php // Подключаем виджет для добавления в избранное
					$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
						'modelId' => $data->id,
						'modelName' => get_class($data),
						'deleteItem' => true
					));?>
				</div>

				<div class="clear"></div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php endif; ?>