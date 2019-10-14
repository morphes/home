<?php
/**
 * @var $ideas array
 */

Yii::import('idea.models.Idea');
Yii::import('idea.models.Interior');
Yii::import('idea.models.InteriorContent');
?>
<div class="-col-12 -idea-products">
	<h2 class="block_head">Идеи интерьеров</h2>
	<a class="-photo-counter" href="/idea/interior"><?php echo Idea::getIdeasPhotoQuantity(); ?> фото идей интерьеров</a>
	<div class="-grid -gutter-bottom">

		<div class="-col-8" id="-cat-idea-cover">
			<?php
			$cnt=0;
			/** @var $ideaUnit MainUnit */
			foreach ($ideas as $ideaUnit) : ?>
				<div class="image_container <?php if ($cnt !== 0) echo 'hide'; ?>" id="idea-cover-<?php echo $ideaUnit->file_id; ?>">
				<?php
				$image = $ideaUnit->getImage();
				if (empty($image))
					continue;
				/*
				 * Т О В А Р Ы, привязанные к фото
				 */
				if($this->beginCache("productsOnPhoto{$ideaUnit->file_id}", array(
					'duration'   => 3600,
				)))
				{
					Yii::import('application.modules.catalog.models.*');
					$productsOnPhoto = ProductOnPhotos::model()->findAllByAttributes(array('ufile_id' => $ideaUnit->file_id));
					foreach($productsOnPhoto as $prod) {
						$offset = unserialize($prod->params);
						?>
						<div class="product_label" style="<?php echo "top:{$offset['top']}; left:{$offset['left']}";?>" data-left="<?php echo $offset['left'];?>" data-top="<?php echo $offset['top'];?>">
							<i class="-icon-tag-s -relative -icon-round"></i>
							<div class="product_item">
								<span class="similar"><?php echo ProductOnPhotos::$typeNames[$prod->type]; ?></span>
								<a class="item_name" title="<?php echo $prod->product->name; ?>" href="<?php echo Product::getLink($prod->product->id, null, $prod->product->categoty_id); ?>">
									<?php echo CHtml::image('/'.$prod->product->cover->getPreviewName(Product::$preview['crop_60']), $prod->product->name, array('width' => '60', 'height' => '60')); ?>
									<?php echo $prod->product->name;?>
								</a><br>
								<?php
								$price = StorePrice::getPriceOffer($prod->product->id);
								$strPrice = '';
								if ($price['min'] == 0.0 && $price['mid'] == 0.0)
								{
									$strPrice = CHtml::tag('span', array('class' => 'price not_specified'), 'Цена не указана');
								}
								elseif ($price['min'] == 0.0 && $price['mid'] > 0)
								{
									$strPrice = CHtml::tag('span', array('class' => 'price'), number_format($price['mid'], 0, '.', ' ').' руб.');
								}
								elseif ($price['min'] > 0 && $price['mid'] > 0) {
									$strPrice = CHtml::tag('span', array('class' => 'price'), 'от '.number_format($price['min'], 0, '.', ' ').' руб.');
								}
								?>
								<span class="vendor"><span><?php echo Country::model()->findByPk((int)$prod->product->country)->name;?></span>, <a href="<?php echo Vendor::getLink($prod->product->vendor_id);?>"><?php echo $prod->product->vendor->name;?></a></span>
								<?php echo $strPrice;?>

							</div>
						</div>
						<?php
					}

					$this->endCache();
				}

				echo CHtml::image('/'.$image->getPreviewName(InteriorContent::$preview['height_420']));
				?>

				</div>
			<?php
			$cnt++;
			endforeach;
			?>
		</div>
		<div class="-col-4">
			<div class="-idea-info">
			<?php
			$cnt = 0;
			foreach ($ideas as $ideaUnit) : ?>
				<?php
				/** @var $origin Interior */
				$origin = $ideaUnit->getOrigin();
				if ($origin===null)
					continue;

				?>
				<div id="idea-desc-<?php echo $ideaUnit->file_id; ?>" class="<?php if ($cnt!==0) echo 'hide'; ?>">
					<a href="<?php echo $origin->getIdeaLink(); ?>"><?php echo $ideaUnit->name; ?></a><br>
					<?php if ($origin->author->getIsWriter()) {
						echo CHtml::tag('span', array('class'=>'idea-authors'), 'Редакция MyHome');
					} else {
						echo CHtml::link($origin->author->name, $origin->author->getLinkProfile(), array('class'=>'idea-authors'));
					}?>
				</div>
			<?php
				$cnt++;
			endforeach; ?>
			</div>
			<div class="-grid -cat-idea-thumbs">
			<?php
			$cnt = 0;
			foreach ($ideas as $ideaUnit) : ?>
				<?php
				/** @var $origin Interior */
				$origin = $ideaUnit->getOrigin();
				$image = $ideaUnit->getImage();
				if ($origin===null || $image===null)
					continue;

				?>
				<div class="-col-2 -gutter-bottom-dbl <?php if ($cnt===0) echo 'current'; ?>" data-id="<?php echo $ideaUnit->file_id; ?>">
					<?php echo CHtml::image(
						'/'.$image->getPreviewName(InteriorContent::$preview['crop_140']),
						$ideaUnit->name,
						array(
							'width'=>140,
							'height'=>140,
							'title'=>$ideaUnit->name,
						)
					); ?>

				</div>
			<?php
			$cnt++;
			endforeach; ?>
			</div>
		</div>
	</div>

</div>