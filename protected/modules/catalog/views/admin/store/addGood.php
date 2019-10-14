<?php
$this->breadcrumbs=array(
	'Магазины'=>array('index'),
	$store->name=>array('goods', 'id' => $store->id),
	'Добавление товаров'
);

Yii::app()->clientScript->registerScript('add_goods', '

	// Удаление производителя из списка
	$("ul.list_vendor").on("click", ".delete", function(){
		$(this).parents("li").remove();
	});

        // Нажатие на кнопку Добавить
        $("button.add_goods").click(function(){
        	$btn = $(this);

        	if ($btn.hasClass("disabled"))
        		return false;

        	$btn.addClass("disabled");
        	$(".loader_add").show();

        	var ids = new Array();
        	$("ul.list_vendor li").each(function(index, element){
        		var vid = $(element).data("vendor_id");
			ids.push(vid);
        	});

        	$.post(
        		"/catalog/admin/store/addGood/id/'.$store->id.'",
        		{
        			vendor_ids: ids,
        			product_ids: $("#product_ids").val()
        		},
        		function(response){
        			$btn.removeClass("disabled");
        			$(".loader_add").hide();

				if (response.success) {
					document.location.href = "/catalog/admin/store/goods/id/'.$store->id.'";
				} else {
        				alert("Нечего добавлять.");
				}
        		}, "json"
        	);

        	return false;
        });
');
?>
<?php if($store->type == Store::TYPE_OFFLINE) : ?>
<h1><?php echo '«'.$store->name.'», г.'.$store->city->name.', '.$store->address;?>
	<br>Добавление товаров
</h1>
<?php endif; ?>

<form class="form-stacked" action="" method="post">
	<div class="clearfix">
		<label>Производитель</label>
		<div class="input">
			<?php
			$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'=>'vendor_id',
				'value'=> '',
				'sourceUrl'=>'/admin/utility/acvendor',
				'options'=>array(
					'minLength'=>'1',
					'showAnim'=>'fold',
					'select'=>'js:function(event, ui) {
						var li = $("<li>");
						li.attr("data-vendor_id", ui.item.id);
						li.html(ui.item.value+"&nbsp;&nbsp;<span><a href=\"#\" class=\"delete\">удалить</a></span>");
						$("ul.list_vendor").append(li);

						ui.item.value = "";

					}',
					'change'=>'js:function(event, ui) {  }',
				),
			));
			?>
		</div>
	</div>

	<div class="clearfix">
		<div class="input">
			<ul class="list_vendor">
				<?php // Сюда javascript'ом вставляется список выбранных Производителей ?>
			</ul>
		</div>
	</div>

	<div class="clearfix">
		<label for="product_ids">ID товара (через запятую)</label>
		<div class="input">
			<textarea cols="10" rows="5" name="product_ids" id="product_ids"></textarea>
		</div>
	</div>

	<div class="actions">
		<button class="btn add_goods">Добавить</button>
		<img class="loader_add" src="/img/load.gif" alt="" style="vertical-align: text-bottom; display: none;">
	</div>
</form>