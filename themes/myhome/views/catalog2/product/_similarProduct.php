<div class="-col-3">
	<span class="-giant">Варианты модели</span>
</div>
<div class="-col-9 similar-goods">
	<div class="-grid">
		<?php foreach ($similars as $key => $similar) : ?>
			<div class="-col-3">
				<a class="-block"
				   title="<?php echo $similar->name ?>"
				   href="<?php echo Product::getLink($similar->id, null, $similar->category_id); ?>">
					<?php if ($similar->cover) : ?>
						<noindex><?php echo CHtml::image(
							'/' . $similar->cover->getPreviewName(Product::$preview['crop_220']),
							$similar->name,
							array(
								'width'  => 220,
								'height' => 220,
								'class'  => '-quad-220 -gutter-bottom-hf'
							)
						); ?></noindex>
					<?php endif; ?>

					<span class="-block -inset-left-hf -inset-right-hf"><?php echo $similar->name ?></span>
				</a>

				<div class="-inset-all-hf -strong">
					<?php
					if($similar->average_price) {
						echo number_format($similar->average_price, 0, '.', ' ') . ' руб.';

					} else {
						echo 'Цена не указана.';
					}
					?>
				</div>
				<?php // Подключаем виджет для добавления в избранное
				$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
					'modelId'   => $similar->id,
					'modelName' => get_class($similar),
					'viewHeart'  => 'favorite',
				));?>
			</div>
		<?php endforeach; ?>

	</div>
</div>