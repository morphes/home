<style type="text/css">
		/* Override some defaults */
	html, body {
		background-color: #eee;
	}
	body {
		padding-top: 40px; /* 40px to make the container go all the way to the bottom of the topbar */
	}
	.container > footer p {
		text-align: center; /* center align it with the container */
	}
	.container {
		width: 1080px; /* downsize our container to make the content feel a bit tighter and more cohesive. NOTE: this removes two full columns from the grid, meaning you only go to 14 columns and not 16. */
	}

		/* The white background content wrapper */
	.container > .content {
		background-color: #fff;
		padding: 20px;
		margin: 0 -20px; /* negative indent the amount of the padding to maintain the grid system */
		-webkit-border-radius: 0 0 6px 6px;
		-moz-border-radius: 0 0 6px 6px;
		border-radius: 0 0 6px 6px;
		-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.15);
		-moz-box-shadow: 0 1px 2px rgba(0,0,0,.15);
		box-shadow: 0 1px 2px rgba(0,0,0,.15);
	}

		/* Page header tweaks */
	.page-header {
		position: relative;
		background-color: #f5f5f5;
		padding: 20px 20px 10px;
		margin: -20px -20px 20px;
	}

		/* Styles you shouldn't keep as they are for displaying this base example only */
	.content .span10,
	.content .span13,
	.content .span4 {
		min-height: 500px;
	}
		/* Give a quick and non-cross-browser friendly divider */
	.content .span4 {
		margin-left: 0;
		padding-left: 19px;
		border-left: 1px solid #eee;
	}

	.btn_close {
		position: absolute;
		top: 20px;
		right: 15px;
	}

	.picture_wrapper {
		position: relative;
		margin: 0 auto;
	}

	.product {
		position: absolute; padding: 6px 6px 8px; border-radius: 6px;
		-moz-user-select: none; -webkit-user-select: none; -ms-user-select: none;
	}
	.product .content {
		width: 265px; margin-top:10px;
	}
	.product.move { cursor: move; }


	.product .icon:hover { cursor: pointer; }
	.product .icon_close { position: absolute; top: 6px; right: 6px; }
	.product .icon_close:hover { cursor: pointer; }

	.product .content { display: none; }
	.product .content input { height: auto; }

	.product.open { z-index: 2; background-color: white; box-shadow: 2px 2px 10px; }
	.product.open .content { display: block; }


	.product .btn_ok,
	.product .btn_edit { float: right; }
	.product .btn_cancel,
	.product .btn_delete { float: left; }

	.product .product_info { margin: 5px 0; }

	.product .icon_glow, .product:not(.open) .icon:hover { border-radius: 50%; box-shadow: 0px 0px 6px 4px rgb(25, 243, 191); }

	.products_list .del_cross { color: red; position: absolute; right: -20px; }

	.products_list .prod_item { position: relative; }
	.products_list .prod_item.item_select a {
		text-shadow: 2px 2px 3px #aaa;
		/*position: relative;
		top: -2px;
		left: -2px;*/
	}
</style>

<script type="text/javascript">
	$(function(){
		window.document.title = "Привязка товаров";
	});
</script>


<?php
// ----- Подключение скрипта для связки товаров с фотками -----

Yii::app()->clientScript->registerScriptFile('/js/admin/CBindProducts.js');
Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-1.8.22.custom.min.js');
?>
<script type="text/javascript">
	$(function(){
		// Работа с товарами на фотографии
		bindProducts.initAddingProd({model: 'Interior', model_id: '<?php echo $interior->id;?>'});
		bindProducts.setFileId('<?php echo $uFile->id;?>');
		bindProducts.initExistProd();

		// Работа с товарами в списке
		bindProducts.initList();
	});

</script>




<div class="container">

	<div class="content">
		<div class="page-header">
			<h1>Привязка товаров <small>к жилым интерьерам</small></h1>
			<button class="btn large danger btn_close" onclick="window.opener.location.reload(false); window.close();">закрыть</button>
		</div>
		<div class="row">
			<div class="span13 bunker">
				<h2>«<?php echo $interior->name;?>»</h2>

				<?php
				// Получаем размер фотки
				list($imageWidth, $imageHeight) = getimagesize($uFile->getPreviewName(Interior::$preview['resize_710x475']));
				$src = '/'.$uFile->getPreviewName(Interior::$preview['resize_710x475']);
				?>

				<div class="picture_wrapper" style="<?php echo "width: {$imageWidth}px";?>">

					<?php echo CHtml::image($src, '', array('class' => 'picture'));?>

					<!-- Сюда вставляем метки по клику -->

					<?php foreach ($products as $prod) { ?>
						<?php
						// Получаем смещение точки, относительно фото
						$offset = $prod->getOffset($imageWidth, $imageHeight);
						$params = unserialize($prod->params);
						?>
						<div class="product" style="<?php echo "top: {$offset['top']}px; left: {$offset['left']}px";?>" data-top="<?php echo $params['top'];?>" data-left="<?php echo $params['left'];?>" data-product_id="<?php echo $prod->product->id;?>">
							<img class="icon" src="/img/admin/small/circle_green.png" alt="" />
							<div class="content">
								<img class="icon_close" src="/img/admin/small/cancel_round.png" alt="" />


								<div class="tmpl">
									<div class="found_prod">
										<img src="<?php echo '/'.$prod->product->cover->getPreviewName(Product::$preview['crop_200']);?>" alt="" class="product_img" />

										<div class="product_info">
											<strong class="product_vendor"><?php echo $prod->product->vendor->name;?></strong> /
											<strong class="product_category"><?php echo $prod->product->category->name;?></strong>
											<br>
											<span class="product_name"><?php echo $prod->product->name;?></span>
										</div>
										<div>
											<?php echo CHtml::checkBox('type', $prod->type == ProductOnPhotos::TYPE_PRODUCT, array('class'=>'product_type')); ?>
											<span>Оригинальный товар</span>
										</div>

										<button class="btn success small btn_edit">ПЕРЕМЕСТИТЬ</button>
										<button class="btn danger small btn_delete" data-product_id="<?php echo $prod->product->id;?>">УДАЛИТЬ</button>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>

				</div>
				<div class="clearfix">&nbsp;</div>
				<p>Чтобы добавить товар — <strong>кликни на изображении</strong>!</p>
			</div>
			<div class="span4">
				<h3>Товары</h3>
				<ol class="products_list">
					<?php
					// Список товаров в правом блоке.
					foreach($products as $prod) {
						$this->renderPartial('bindProdItemList', array('product' => $prod->product));
					}
					?>
				</ol>

				<div class="product_preview">
					<!-- Сюда вставляется превью товара,
					при наведении на название в списке -->
				</div>
			</div>
		</div>
	</div>

	<footer>
		<p>&copy; MyHome <?php echo date('Y');?></p>
	</footer>

</div> <!-- /container -->



<!-- Шаблон для нового товара -->
<script type="text/template" id="new_template">

	<div class="product">
		<img class="icon" src="/img/admin/small/circle_green.png" alt="" />
		<div class="content">
			<img class="icon_close" src="/img/admin/small/cancel_round.png" alt="" />

			<!-- Для каждого состояния товара tmpl разный -->
			<div class="tmpl">
				<div class="desc">
					<input type="text" placeholder="ID товара" class="product_id" >
					<button class="btn primary small btn_search">найти</button>
				</div>
				<div class="found_prod"></div>
			</div>
		</div>
	</div>

</script>

<!-- Шаблон для ново-найденного товара -->
<script type="text/template" id="prod_template">
	<div class="tmpl">
		<div class="desc">
			<input type="text" placeholder="ID товара" class="product_id" >
			<button class="btn primary small btn_search">найти</button>
		</div>
		<div class="found_prod">
			<!-- заполняется Ajax'ом -->
			<img src="#" alt="" class="product_img" />

			<div class="product_info">
				<!-- заполняется Ajax'ом -->
				<strong class="product_vendor"></strong> /
				<strong class="product_category"></strong>
				<br>
				<!-- заполняется Ajax'ом -->
				<span class="product_name"></span><br>

			</div>
			<div>
				<input type="checkbox" name="type" class="product_type"/>
				<span>Оригинальный товар</span>
			</div>

			<button class="btn primary small btn_ok">ДОБАВИТЬ</button>
			<button class="btn danger small btn_cancel">ОТМЕНИТЬ</button>
		</div>
	</div>
</script>


<!-- Шаблон для существующего товара -->
<script type="text/template" id="bind_prod">
	<div class="tmpl">
		<div class="found_prod">
			<!-- заполняется Ajax'ом -->
			<img src="#" alt="" class="product_img" />

			<div class="product_info">
				<!-- заполняется Ajax'ом -->
				<strong class="product_vendor"></strong> /
				<strong class="product_category"></strong>
				<br>
				<!-- заполняется Ajax'ом -->
				<span class="product_name"></span>
			</div>
			<div>
				<input type="checkbox" name="type" class="product_type"/>
				<span>Оригинальный товар</span>
			</div>


			<button class="btn success small btn_edit">ПЕРЕМЕСТИТЬ</button>
			<button class="btn danger small btn_delete">УДАЛИТЬ</button>
		</div>
	</div>
</script>


