<?php Yii::app()->clientScript->registerScriptFile('/js-new/folders.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js-new/scroll.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/media.css'); ?>

<?php if($isOwner) Yii::app()->getClientScript()->registerScript('sorting', 'folders.sortItems();', CClientScript::POS_READY);?>

<div class="-grid-wrapper page-title">
	<div class="-grid">
		<?php
		$this->widget('catalog2.components.widgets.CatBreadcrumbs',
			array(
				'category' => Category::getRoot(),
				'homeLink' => '<a href="'.Yii::app()->params->bmHomeUrl.'">Каталог ТВК «Большая Медведица»</a>',
				'pageName' => $folder->name,
				'folderListLink' => '<a href="'.Yii::app()->params->bmHomeUrl.'/catalog/folders/list">Спецпредложения</a>',
			)
		);?>
		<div class="-col-8 lead"><?php echo $folder->description; ?></div>
<!--		<div class="-col-4 -relative">-->
<!--			<div class="bm-banner">-->
<!--				<span class="-block -small">Все товары вы можете купить в</span>-->
<!--				<a href="http://bm.myhome.ru/about" class="-strong">ТВК «Большая медведица»</a>-->
<!--				<span class="-block -small">Новосибирск, Светлановская, 50</span>-->
<!--			</div>-->
<!--		</div>-->
	</div>
</div>

<?php $models = $folderItemProvider->getData(); ?>
<?php if ($models) : ?>

	<div class="-grid-wrapper page-content goods-index">
		<div class="-grid folder-content">

			<div class="-col-8 -pass-4 -gutter-bottom-dbl -inset-bottom social-likes">
				<?php $this->widget('application.components.widgets.likes.Likes');?>
			</div>

			<?php foreach ($models as $model) : ?>
				<?php
				$storePrice = false;
				$storeIds = Product::getStoresInMall($model->model_id);

				$storeId = reset($storeIds);

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

			<?php if ( $folder->count > $limit ) : ?>
				<div class="-col-12 -text-align-center -inset-top-dbl -inset-bottom-dbl">
					<a href="#" class="-button -button-skyblue -inset-all" id="scroll"><i class="-icon-refresh-s"></i><span class="-large -strong">Еще <?php echo $folder->count-$limit ?> товаров</span></a>
				</div>
			<?php else : ?>
				<div class="-col-4 last">
					<p class="-text-align-center -inset-all">
						<a href="<?php echo Yii::app()->params->bmHomeUrl.'/catalog/folders/list'?>" class="-skyblue -huge -semibold">Другие<br>спецпредложения</a>
						<span class="-block -small -gray">от ТВК «Большая Медведица»</span>
					</p>
					<span class="-block -gray -small -text-align-center -inset-all">Еще больше товаров для дома и ремонта в<br><a href="http://bm.myhome.ru/" class="-black">каталоге Большой Медведицы</a> или по адресу<br><a href="http://bm.myhome.ru/about">Новосибирск, ул. Светлановская, 50</a></span>
				</div>
			<?php endif; ?>
		</div>

		<div class="-col-8 -pass-4 -gutter-bottom-dbl -inset-bottom social-likes">
			<?php $this->widget('application.components.widgets.likes.Likes', array(
				'vkLikePostfix' => '2',
				'okLikePostfix' => '2',
			));?>
		</div>
	</div>
<?php endif; ?>

<?php if ($folderItemProvider->pagination->currentPage < $folderItemProvider->pagination->pageCount - 1) : ?>
	<?php echo CHtml::hiddenField(
		'next_page_url',
		Yii::app()->createUrl('catalog/folders/AjaxFolder', $params = array('id'=>$folder->id, 'page' => $folderItemProvider->pagination->currentPage + 1))
	); ?>
<?php endif; ?>

<script>
	folders.initFolderProductsActions();
	folders.productDiscount();
</script>

<div class="-col-wrap -white-bg -hidden" id="discount-popup">

</div>
