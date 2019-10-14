<?php
/**
 * @var $store Store
 */
?>

<?php $this->pageTitle = 'Прикрепление товаров — MyHome.ru'; ?>


<h2>Все товары <sup id="productQt"><?php echo $dataProvider->getTotalItemCount(); ?></sup></h2>



<?php echo CHtml::hiddenField('updateByWidget', true, array('id'=>'update-by-widget'))?>

<div class="added_products">
        <form name="product_filter" id="products_filter" action="<?php echo $this->createUrl('/catalog/profile/storeProductList'); ?>" method="get">
            <div class="products_filter">
                <div class="products_filter_item">
                    <span>Категории</span>

                    <div class="hidden_list hide">
                        <input type="text" class="textInput" placeholder="Быстрый поиск категории">
                        <ul id="category">

                            <?php foreach($categories as $cat) : ?>
                                <?php
                                    if($cat_ids && in_array($cat['id'], $cat_ids)) $cat_checked=true;
                                    else $cat_checked=false;
                                ?>

                                <li id="category_<?php echo $cat['id']; ?>">
                                    <label><?php echo CHtml::checkBox('categories[]', $cat_checked, array('value'=>$cat['id'], 'id'=>'cat_'.$cat['id'])); ?><?php echo $cat['name']; ?></label>
                                </li>
                            <?php endforeach; ?>

                            <li class="no_results hide">Ничего не найдено</li>
                        </ul>
                        <a class="btn_grey" href="#">Показать товары</a>
                        <a class="cancel_link" href="#">Отмена</a>
                    </div>

                </div>
                <div class="products_filter_item">
                    <span>Производители</span>

                    <div class="hidden_list hide">
                        <input type="text" class="textInput" placeholder="Быстрый поиск производителям">
                        <ul id="vendors">
                            <?php foreach($vendors as $vendor) : ?>
                                <?php
                                    if($vendor_ids && in_array($vendor['id'], $vendor_ids)) $vnd_checked=true;
                                    else $vnd_checked=false;
                                ?>

                                <li id="vendors_<?php echo $vendor['id']; ?>"><label>
                                    <?php echo CHtml::checkBox('vendors[]', $vnd_checked, array('value'=>$vendor['id'], 'id'=>'vnd_'.$vendor['id'])); ?><?php echo $vendor['name']; ?></label>
                                </li>
                            <?php endforeach; ?>
                            <li class="no_results hide">Ничего не найдено</li>
                        </ul>
                        <a class="btn_grey" href="#">Показать товары</a>
                        <a class="cancel_link" href="#">Отмена</a>
                    </div>
                </div>
                <div class="products_filter_item_long">
                    <input name="searchWord" class="textInput" type="text" placeholder="Поиск по наименованию или ID">
                    <input type="submit" value=" " class="search">
                </div>
                <div class="clear"></div>

            </div>
            <div class="filter_params hide">
                <div class="params_list">

                </div>

                <span class="clear_filter">Сбросить фильтр</span>
            </div>
            <div class="store_filter_params">
                <label><?php echo CHtml::checkBox('onlyStoreProducts', $onlyStoreProducts, array('class'=>'textInput')); ?> Только прикрепленные товары <span><?php echo $store->productQt; ?></span></label>
                <?php echo CHtml::dropDownList('store_id', $store->id, CHtml::listData($stores, 'id', 'fullName'), array('class'=>'textInput')); ?>

		<?php if($store->type !=  Store::TYPE_ONLINE) { ?>
			 <span class="store_actions">
				<?php echo CHtml::openTag('a', array('href'=>$this->createUrl('storeUpdate', array('id'=>$store->id)), 'title'=>'редактировать магазин')); ?><i class="edit"></i><?php echo CHtml::closeTag('a'); ?>
				<!--<a href="#"><i class="deactivate"></i></a>-->
				<!--<a href="#"><i class="del"></i></a>-->
			</span>
		<?php } ?>
            </div>
            <input type="hidden" id="order" name="order" value=""/>
            <input type="hidden" id="sort" name="sort" value=""/>
        </form>

	<!-- В следующий <div> добавить класс «vizitka» в случае если мы в интернет магазине.-->
	<div class="added_products_list bind_list">
		<div class="products_list_header">
			<div class="photo">
				Фото
			</div>
			<div class="name">
				<span data-fieldname="name"><a href="#">Наименование</a></span>,
				<span>Производитель</span>
			</div>
			<div class="category">
				<span>Категория</span>
			</div>
			<?php if($store->type == Store::TYPE_ONLINE) { ?>
				<div class="url">
					<span>Ссылка</span>
				</div>
			<?php } ?>
			<div class="price">
				<span>Цена</span>
			</div>

			<?php if ($store->tariff_id == Store::TARIF_MINI_SITE) { ?>
				<div class="discount">
					<span>Скидка</span>
				</div>
			<?php } ?>

			<div class="bind">
				<span>Прикрепить</span>
			</div>
			<div class="clear"></div>
		</div>

		<?php $this->widget('zii.widgets.CListView', array(
			'dataProvider'    => $dataProvider,
			'viewData'        => array('store' => $store),
			'template'        => "{items}\n{pager}",
			'itemView'        => ($store->tariff_id == Store::TARIF_MINI_SITE)
				? '_store_product_list_item_mini_site'
				: '_store_product_list_item',
			'pager'           => array('class' => 'application.components.widgets.CustomPager2'),
			'pagerCssClass'   => 'pages',
			'loadingCssClass' => 'loader',
			'ajaxUpdate'      => false,
			'emptyText'       => '<div class="no_result"> Нет товаров для отображения. </div>',
			'itemsCssClass'   => 'products_list',
		)); ?>

		<div class="loader"></div>
	</div>
</div>

<script>
        store.initProductsFilter();
	<?php if ($store->tariff_id == Store::TARIF_MINI_SITE) { ?>
		store.bindProductWithDiscount();
	<?php } else { ?>
		store.bindProduct();
	<?php } ?>
        store.initForm();
</script>

<?php Yii::app()->clientScript->registerScript('filter', '
        $("#onlyStoreProducts").live("click", function(){
                store._formSubmit();
        });

        $("#store_id").live("change", function(){
                window.location.href = "/catalog2/profile/storeProductList/store_id/"+$(this).val();
        });
', CClientScript::POS_READY);?>