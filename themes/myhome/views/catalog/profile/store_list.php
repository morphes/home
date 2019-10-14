<?php
$this->pageTitle = 'Список магазинов — MyHome.ru';
?>



<script type="text/javascript">
    $(document).ready(function () {
        //cat.initStoresForm();
    })
</script>
<div class="added_stores">
	<h2 class="-inline -gutter-top-null">Мои магазины</h2>
	<span id="storesQt"><?php echo $dataProvider->getTotalItemCount(); ?></span>

	<div class="btn_conteiner -push-right">
		<?php echo CHtml::link('Добавить новый магазин', $this->createUrl('storeCreate'), array('class' => 'btn_grey in_process')); ?>
	</div>
	<div class="clear"></div>
</div>

<div class="added_stores">
	<form id="stores_filter">
		<input type="hidden"
		       id="order"
		       name="order"
		       value=""/>
		<input type="hidden"
		       id="sort"
		       name="sort"
		       value=""/>
	</form>
	<div class="added_stores_table">
		<div class="stores_list_header ">
			<div class="name">
				<span data-fieldname="address"><a href="#">Адрес
									   магазина</a></span>,
				<span class=""
				      data-fieldname="name"><a href="#">название
									магазина</a></span>
			</div>
			<div class="products_quant">
				<span class=""
				      data-fieldname="product_qt"><a href="#">Количество
									      товаров</a></span>
			</div>
			<div class="bind_product">
				<span>Прикрепить товары</span>
			</div>
			<div class="copy_product">
				<span>Копировать товары</span>
			</div>
			<div class="actions">
				<span>Действия</span>
			</div>
			<div class="clear"></div>
		</div>
		<div class="added_stores_list">

			<?php foreach ($dataProvider->getData() as $data) : ?>
				<?php $this->renderPartial('_store_list_item', array('data' => $data)); ?>
			<?php endforeach; ?>


			<?php if ($dataProvider->pagination->currentPage < $dataProvider->pagination->pageCount - 1) : ?>
				<?php echo CHtml::hiddenField('next_page_url', $dataProvider->pagination->createPageUrl($this, $dataProvider->pagination->currentPage + 1)); ?>
			<?php else : ?>
				<?php echo CHtml::hiddenField('next_page_url', 0); ?>
			<?php endif; ?>

		</div>
		<div class="loader">

		</div>
	</div>
</div>



<script>
    store.initStoreFilter();
    store.initStoreList();
</script>