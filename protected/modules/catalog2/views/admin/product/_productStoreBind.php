<?php echo CHtml::beginForm();?>

	<div>
		<?php echo CHtml::activeTextField($store,'id', array('placeholder'=>'ID', 'style'=>'width:60px;')); ?>
		<?php echo CHtml::activeTextField($store,'name', array('placeholder'=>'Название магаз.')); ?>
		<?php echo CHtml::activeTextField($store,'address', array('placeholder'=>'Адрес магазина')); ?>
		<?php
		$city = City::model()->findByPk($store->city_id);
		$this->widget('application.components.widgets.CAjaxAutoComplete', array(
			'name'=>'City_id',
			'value'=> !is_null($city) ? "{$city->name} ({$city->region->name}, {$city->country->name})" : '',
			'sourceUrl'=>'/utility/autocompletecity',
			'options'=>array(
				'minLength'=>'3',
				'showAnim'=>'fold',
				'select'=>'js:function(event, ui) {$("#Store_city_id").val(ui.item.id).keyup();}',
				'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Store_city_id").val("");}}',
			),
			'htmlOptions' => array('placeholder'=>'Город магазина')
		));
		?>
		<?php echo CHtml::activeHiddenField($store,  "city_id");?>

		<?php echo CHtml::activeDropDownList($store,'bindedToProduct', array(
			''           => 'Все магазины',
			$product->id => 'С ценами',
			'fake'       => 'Фейковые'
		)); ?>
		<?php echo CHtml::button('Найти', array('id'=>'price-dialog-search', 'class'=>'btn primary')); ?>

	</div>

<?php echo CHtml::endForm();?>

<hr>

<?php echo CHtml::beginForm();?>

<div>
	<?php $url = $this->createUrl("/catalog2/admin/product/ajaxProductSelectedStoreBindPrice", array('product_id'=>$product->id)); ?>
	<?php echo CHtml::textField('price-dialog-price-for-selected', '', array('placeholder'=>'Цена', 'style'=>'width:100px')); ?>
	<?php echo CHtml::button('Применить ко всем', array('id'=>'price-dialog-save-for-selected', 'class'=>'btn', 'data-url'=>$url)); ?>

</div>

<?php echo CHtml::endForm();?>

<hr>

<div>
	<?php $stores = $store->search(200, $product->id); ?>
	<?php $this->widget('zii.widgets.CListView', array(
		'dataProvider'=>$stores,
		'id'=>'price-dialog-stores',
		'itemView'=>'_productStoreBindItem',
		'template'=>'{items}',
		'viewData'=>array('pid'=>$product->id)
	)); ?>
</div>

<?php
	// формирование скрытого поля со значением типа JSON массива,
	// содержащего набор id магазинов на странице
	$store_ids = array();
	foreach($stores->getData() as $s) $store_ids[] = $s->id;
	echo CHtml::hiddenField('price-dialog-store_ids', CJSON::encode($store_ids));
?>