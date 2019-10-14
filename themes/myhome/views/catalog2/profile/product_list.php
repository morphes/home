<?php
$this->pageTitle = 'Список товаров — MyHome.ru';
?>

<?php Yii::app()->clientScript->registerScriptFile('/js/CStoreProfile.js'); ?>

<script type="text/javascript">
    $(document).ready(function () {
        store.initForm();
    })
</script>
        <div class="added_products goods_head">
            <h2 class="-inline -gutter-top-null">Мои товары</h2>
            <span id="productQt"><?php echo $dataProvider->totalItemCount; ?></span>

            <div class="btn_conteiner -push-right">
		<?php if ( count(Store::getOwnedStores(Yii::app()->user->id)) > 0 ) : ?>
		    <a class="btn_grey in_process" id="create_product" href="/catalog2/profile/productSelectCategory">Добавить новый товар</a>
		    <div class="notice hide">
			    <i class="close"></i>
			    <p>Вы еще не закончили создание товара в категории <br>«<span id="exist_product_cat_name"></span>». При добавлении нового товара незавершенный ранее товар будет удален.</p>
			    <a href="#" id="continue_update" class="complete">Продолжить создание</a> или <a href="/catalog2/profile/productSelectCategory">добавить новый товар</a>?
		    </div>
		<?php else : ?>
		    <a class="btn_grey in_process store-alert" id="create_product">Добавить новый товар</a>
		<?php endif; ?>
            </div>

        </div>

        <div class="added_products">
                <form id="products_filter" method="post">
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
                            <input type="text" name="searchWord" class="textInput" placeholder="Поиск по наименованию">
                            <input type="submit" value=" " class="search">
                        </div>
                        <div class="clear"></div>

                    </div>
                    <div class="filter_params hide">
                        <div class="params_list"></div>
                        <span class="clear_filter">Сбросить фильтр</span>
                    </div>
                    <input type="hidden" id="order" name="order" value=""/>
                    <input type="hidden" id="sort" name="sort" value=""/>
                </form>

                <div class="added_products_list">
                    <div class="products_list_header">
                        <div class="photo">
                            Фото
                        </div>
                        <div class="name">
                            <span data-fieldname="name"><a href="#">Наименование</a></span>,
                            <span data-fieldname="vendor"><a href="#">производитель</a></span>
                        </div>
                        <div class="category">
                            <span data-fieldname="category"><a href="#">Категория</a></span>
                        </div>
                        <div class="date">
                            <span data-fieldname="date"><a href="#">Дата создания</a></span>
                        </div>
                        <div class="status">
                            <span data-fieldname="status"><a href="#">Статус</a></span>
                        </div>
                        <div class="clear"></div>
                    </div>

                    <?php $highlight_id = Yii::app()->user->hasFlash('highlight_product') ? Yii::app()->user->getFlash('highlight_product') : null; ?>

                    <div class="products_list">
                            <?php foreach($dataProvider->getData() as $data) : ?>
                                <?php $this->renderPartial('_product_list_item', array('data'=>$data, 'highlight_id'=>$highlight_id)); ?>
                            <?php endforeach; ?>

                            <?php if($dataProvider->pagination->currentPage < $dataProvider->pagination->pageCount - 1) : ?>
                                <?php echo CHtml::hiddenField('next_page_url', $dataProvider->pagination->createPageUrl($this, $dataProvider->pagination->currentPage + 1)); ?>
                            <?php else : ?>
                                <?php echo CHtml::hiddenField('next_page_url', 0); ?>
                            <?php endif;?>
                    </div>

                    <div class="loader"></div>
                </div>
        </div>


<script>
    store.initProductsFilter();
</script>

<?php Yii::app()->clientScript->registerScript('scripts', '
	$("#create_product").click(function(){
		if ( $(this).hasClass("store-alert") ) {
			$("#popup-store-alert").modal({
				overlayClose:true,
			})
		}
		return false;
	});
', CClientScript::POS_READY); ?>

<div class="-hidden">
	<div class="-white-bg -inset-all -col-7" id="popup-store-alert">
		<div class="-gutter-bottom">
			<h2 class="-gutter-bottom-dbl">Чтобы иметь возможность добавлять товары, необходимо создать магазин</h2>
			<button class="-button -button-skyblue" onclick="document.location.href = '/catalog2/profile/storeCreate'; return false;">Создать магазин</button>
			<a class="-red -gutter-left" onclick='$.modal.close();return false;'>Отмена</a>
		</div>
	</div>
</div>