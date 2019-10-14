<div class="clearfix">
	<label>Название товара</label>
	<div class="input">
		<?php echo CHtml::textField('MainUnit[name]', $product->name, array('class'=>'span5', 'maxlength'=>255)); ?>
	</div>
</div>
<div class="clearfix photos">
	<label>Фоточке</label>
	<div class="input">
		<ul class="media-grid">
		<?php /** @var $image UploadedFile */
		foreach ($images as $image) {
			echo CHtml::tag('li', array(),
				CHtml::link(
					CHtml::image('/'.$image->getPreviewName(Product::$preview['crop_60']),
						'',
						array('class'=>'thumbnail',
							'data-url'=>'/'.$image->getPreviewName(Product::$preview['resize_420']),
							'data-id'=>$image->id,
							'width'=>60,
							'height'=>60,
						)
					)
				)
			);
		} ?>
		</ul>
	</div>
</div>


<div class="clearfix">
	<label>Ссылка на магазин</label>
	<div class="input">
	<?php echo CHtml::dropDownList('MainUnit[store_id]', '', $stores, array('class'=>'span7')); ?>
	</div>
</div>