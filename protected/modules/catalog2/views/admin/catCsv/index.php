<?php
$this->breadcrumbs=array(
	'CSV'=>array('index'),
	'Управление экспортом',
);


Yii::app()->clientScript->registerScript('list_vendor', '

	// Удаление производителя из списка
	$("ul.list_vendor").on("click", ".delete", function(){
		$(this).parents("li").remove();
	});

        // Нажатие на кнопку Экспорт
        $("button.export_csv").click(function(){
        	var ids = new Array();
        	$("ul.list_vendor li").each(function(index, element){
        		var vid = $(element).data("vendor_id");
			ids.push(vid);
        	});

        	$.post(
        		"/catalog2/admin/catCsv/exportForVendors",
        		{ vendor_ids: ids },
        		function(response){
				if (response.success) {
					document.location = "/catalog2/admin/catCsv/list";
				}
        		}, "json"
        	);

        	return false;
        });
');
?>

<h1>CSV <?php echo CHtml::link('Список заданий', $this->createUrl('list'), array('style' => 'float: right;')); ?></h1>



<form action="" method="post" class="form-stacked">

	<div class="clearfix" id="vendor">
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


	<div class="actions">
		<button class="btn export_csv">Экспорт CSV</button>
	</div>
</form>
