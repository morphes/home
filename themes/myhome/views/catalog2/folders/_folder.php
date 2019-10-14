<?php $models = $folderItemProvider->getData(); ?>

<?php if ($models) : ?>
	<?php foreach ($models as $model) : ?>
		<?php
		$storePrice = false;
		$storeIds = Product::getStoresInMall($model->model_id);

		$storeId = current($storeIds);

		if($storeId) {
			$storePrice = StorePrice::model()->findByAttributes(array(
				'store_id'   => $storeId,
				'product_id' => $model->model_id
			));
		}

		?>
		<?php $this->renderPartial('_item', array(
			'model'=>$model,
			'price'=>$storePrice->price,
			'isOwner'=>$isOwner,
			'storePrice' =>$storePrice
		));?>
	<?php endforeach; ?>
<?php endif; ?>


<?php if ($folderItemProvider->pagination->currentPage < $folderItemProvider->pagination->pageCount-1) : ?>
	<?php echo CHtml::hiddenField(
		'next_page_url',
		Yii::app()->createUrl('catalog2/folders/AjaxFolder', $params = array('id'=>$id, 'page' => $folderItemProvider->pagination->currentPage + 1))
	); ?>

<?php else : ?>
	<div class="-col-4 last">
		<p class="-text-align-center -inset-all">
			<a href="<?php echo Yii::app()->params->bmHomeUrl.'/catalog/folders/list'?>" class="-skyblue -huge -semibold">Другие<br>спецпредложения</a>
			<span class="-block -small -gray">от ТВК «Большая Медведица»</span>
		</p>
		<span class="-block -gray -small -text-align-center -inset-all">Еще больше товаров для дома и ремонта в<br><a href="http://bm.myhome.ru/" class="-black">каталоге Большой Медведицы</a> или по адресу<br><a href="http://bm.myhome.ru/about">Новосибирск, ул. Светлановская, 50</a></span>
	</div>
<?php endif; ?>