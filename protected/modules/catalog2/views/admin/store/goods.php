<?php
$this->breadcrumbs=array(
	'Магазины'=>array('index'),
	$store->name
);
?>

<?php if($store->type == Store::TYPE_OFFLINE) : ?>
<h1><?php echo '«'.$store->name.'», г.'.$store->city->name.', '.$store->address;?>
	<br>Товары магазина
<?php echo CHtml::link('Список заданий', $this->createUrl('admin/catCsv/list'), array('style' => 'float: right;')); ?>
</h1>
<?php endif; ?>


<?php
if (Yii::app()->user->hasFlash('xml_import_progress')) {
	echo CHtml::openTag('p', array('class' => 'alert-message warning'));
	echo Yii::app()->user->getFlash('xml_import_progress');
	echo CHtml::closeTag('p');
}
if (Yii::app()->user->hasFlash('importError')) {
	echo CHtml::openTag('p', array('class' => 'alert-message danger'));
	echo Yii::app()->user->getFlash('importError');
	echo CHtml::closeTag('p');
}
if (Yii::app()->user->hasFlash('importSuccess')) {
    echo CHtml::openTag('p', array('class' => 'alert-message success'));
    echo Yii::app()->user->getFlash('importSuccess');
    echo CHtml::closeTag('p');
}
?>

<div class="row form-stacked">
	<div class="span3">
		<div class="actions">
			<?php echo CHtml::link('Добавить товар', '/catalog2/admin/store/addGood/id/'.$store->id, array('class' => 'btn'));?>
		</div>
	</div>
	<div class="span8 offset1">
		<form action="" method="post" enctype="multipart/form-data">
			<div class="actions">
				<input type="hidden" name="action" value="import">

				<input type="file" class="input-file span6" name="file_csv">
				<button type="submit" class="btn">Импорт</button>
			</div>
		</form>
	</div>
	<div class="span4 offset1">
		<form action="" method="post">
			<div class="actions">
				<input type="hidden" name="action" value="export">

				<button class="btn">Экспорт CSV</button>

				<?php
				if ($taskImport) {
					echo CHtml::link('Скачать', '/download/catCsv/id/'.$taskImport->id, array('class' => 'btn'));
				}
				?>
			</div>
		</form>
	</div>
    <div class="span8 offset1">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="actions">
                <input type="hidden" name="action" value="importXML">
                <input type="file" class="input-file span6" name="file_xml">
                <button type="submit" class="btn">Импорт XML</button>
            </div>
        </form>
    </div>

</div>

<?php Yii::app()->clientScript->registerScriptFile('/js/admin/jquery.maskMoney.js');?>


<?php $this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'             => 'product-grid',
	'dataProvider'   => $dataProvider,
	'template'       => "{summary}\n{items}\n{pager}",
	'selectableRows' => 2,
	'columns'        => array(
		'product_id',
		array(
			'name'  => 'Название товара',
			'value' => '$data->product ? CHtml::link($data->product->name, $data->product->getLink($data->product_id)) : null',
			'type'  => 'raw'
		),
		array(
			'name'  => 'Категория',
			'value' => '$data->product ? $data->product->category->name : null',
			'type'  => 'raw'
		),
		array(
			'name'  => 'Производитель',
			'value' => '$data->product ? $data->product->vendor->name : null',
			'type'  => 'raw'
		),
		array(
			'name'  => 'price',
			'value' => '$data->getPriceHtml()'
		),
		array(
			'name'  => 'discount',
			'value' => '$data->getDiscountHtml()',
			'visible' => $store->tariff_id == Store::TARIF_MINI_SITE
		),
		array(
			'name'  => 'update_time',
			'value' => 'date("d.m.Y ­ H:i", $data->update_time)'
		),
		array(
			'buttons'  => array(
				'delete' => array(
					'label' => 'Клон',
					'url'   => 'Yii::app()->createUrl("/catalog2/admin/store/deletePrice/", array("sid" => $data->store_id, "pid" => $data->product_id))',
				)
			),
			'class'    => 'CButtonColumn',
			'template' => '{delete}',

		),
	),
)); 
?>

<?php echo CHtml::button('Удалить выбранное', array('class' => 'btn danger delete_group', 'onclick' => 'removeGoods(); return false;')); ?>


<script type="text/javascript">
	function removeGoods(){
		var products = $.fn.yiiGridView.getSelection("product-grid");
		if(products.length == 0) return;
		if (confirm("Вы действительно хотите удалить выбранные товары?")) {
			$.post("/catalog2/admin/store/deletePrice/sid/<?php echo $store->id;?>/ids/"+products, function(response) {
				$.fn.yiiGridView.update('product-grid');
			}, "json");
		}
	}
</script>


<script type="text/javascript">

	$(function(){
		var $body = $('body');

		// Сохранение цены
		$body.on({
			'focusout':function(){
				var $input = $(this);
				var data = $input.data();
				var sid = data['sid'];
				var pid = data['pid'];
				var price = $input.val().replace(',', '.');
				$input.val(price);

				$input.next('img').css('visibility', 'visible');
				$.post(
					'/catalog2/admin/store/updatePrice/sid/'+sid+'/pid/'+pid,
					{ price: price },
					function(response){
						if ( ! response.success) {
							alert(response.errorMsg);
						}
						$input.next('img').css('visibility', 'hidden');
					}, 'json'
				);
			},
			'click':function(){
				return false;
			}
		}, '.prod_price');

		// Сохранение скидки
		$body.on({
			'focusout':function(){
				var $input = $(this);
				var data = $input.data();
				var sid = data['sid'];
				var pid = data['pid'];
				var discount = $input.val().replace(',', '.');
				$input.val(discount);

				$input.next('img').css('visibility', 'visible');
				$.post(
					'/catalog2/admin/store/updateDiscount/sid/'+sid+'/pid/'+pid,
					{ discount: discount },
					function(response){
						if ( ! response.success) {
							alert(response.errorMsg);
						}
						$input.next('img').css('visibility', 'hidden');
					}, 'json'
				);
			},
			'click':function(){
				return false;
			}
		}, '.prod_discount');
	});

</script>

