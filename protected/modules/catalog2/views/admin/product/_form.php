<?php Yii::app()->clientScript->registerScriptFile('/js/admin/jquery.maskMoney.js');?>

<style>
        input, select, textarea {width: 130px;}
        .error, .errorMessage {border-color: #EE5F5B;color: #B94A48;}
        #topNewProductButton {position: fixed; width: 100px; right: 20px; top: 120px;}
        .condensed-table th, .condensed-table td {padding: 5px 1px 4px 1px;}
        .condensed-table { table-layout: fixed; }
        .grid-view table th { white-space: normal; }
        .thumbnail a > img {
            display: block;
            max-width: 100%;
            margin-left: auto;
            margin-right: auto;
        }
</style>

<?php
/**
 * Для автокомплитов
 */
$cs = Yii::app()->clientScript;
$cssCoreUrl = $cs->getCoreScriptUrl();
$cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css');
$cs->registerCoreScript('jquery.ui');
?>

<div id="topNewProductButton"><?php echo CHtml::button('Новый товар',array('class'=>'btn', 'id'=>'product-add')); ?></div>


<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'product-form',
	'enableAjaxValidation'=>false,
)); ?>

        <?php echo CHtml::hiddenField('category_id', $category->id)?>

        <div id="category-ext-options" class="grid-view">

                <table class="condensed-table" style="margin-top: 10px;">
                        <thead>
                        <tr>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-actions', 'width'=>'70px;'), '')?>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-name', 'width'=>'180px'), $product->getAttributeLabel('name') . ' *');?>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-desc', 'width'=>'280px'), $product->getAttributeLabel('desc'));?>
                                <?php /*echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-tags', 'width'=>'140px'), $product->getAttributeLabel('tags'))*/?>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-barcode', 'width'=>'140px'), $product->getAttributeLabel('barcode'));?>
				<?php /*echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-price', 'width'=>'140px'), $product->getAttributeLabel('price'));*/?>

                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-country', 'width'=>'140px'), $product->getAttributeLabel('country') . ' *');?>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-guaranty', 'width'=>'140px'), $product->getAttributeLabel('guaranty'));?>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-usageplace', 'width'=>'140px'), $product->getAttributeLabel('usageplace'));?>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-similar', 'width'=>'110px'), 'Аналогичные');?>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-related', 'width'=>'140px'), $product->getAttributeLabel('related_product'));?>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-eco', 'width'=>'110px'), $product->getAttributeLabel('eco'));?>

                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-image_id', 'width'=>'220px'), $product->getAttributeLabel('image_id').'*');?>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-images', 'width'=>'220px'), $product->getAttributeLabel('images'));?>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-vendor_id', 'width'=>'140px'), $product->getAttributeLabel('vendor_id') . ' *');?>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-collection_id', 'width'=>'130px'), $product->getAttributeLabel('collection_id'));?>

                                <?php foreach ($category->availableOptions as $option) : ?>
                                        <?php $required = $option->required ? ' *' : ''; ?>
                                        <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-' . $option->key, 'width'=>'140px'), $option->name . $required)?>
                                <?php endforeach; ?>

                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-admin_comment', 'width'=>'140px'), $product->getAttributeLabel('admin_comment'))?>
                                <?php echo CHtml::tag('th', array('class'=>'header', 'id'=>'product-option-status', 'width'=>'140px'), $product->getAttributeLabel('status'))?>
                        </tr>
                        </thead>
                        <tbody>
                                <?php foreach ($products as $prod) : ?>
                                        <?php $this->renderPartial('_productRow', array('model'=>$prod, 'options'=>$category->options, 'errors'=>$errors, 'class'=>'update')); ?>
                                <?php endforeach; ?>
                        </tbody>
                </table>
        </div>

        <div id="bottomNewProductButton"><?php echo CHtml::button('Новый товар',array('class'=>'btn', 'id'=>'product-add')); ?></div>

        <div class="actions">
                <?php echo CHtml::button('Сохранить', array('class'=>'btn primary', 'id'=>'submitUnsavedForm')); ?>
                <?php echo CHtml::button('К списку товаров', array('class'=>'btn','onclick'=>'document.location = \''.$this->createUrl('index', array('cid'=>$category->id)).'\''))?>
        </div>

<?php $this->endWidget(); ?>



</div><!-- form -->


<!-- Модальное окно установки цен на товар в магазинах (содержимое обновляется аяксом при открытии окна) -->
	<?php $this->beginWidget('zii.widgets.jui.CJuiDialog',array(
		'id'=>'price-dialog',
		'options'=>array(
			'title'=>'Price',
			'autoOpen'=>false,
			'width'=>1080,
			'height'=>500,
		),
	));?>

		<div id="price-dialog-content"></div>

	<?php $this->endWidget('zii.widgets.jui.CJuiDialog');?>

	<?php Yii::app()->getClientScript()->registerScript('price-modal', '

		// открытие модального окна
		$("#product-form").on("click", ".price", function(){
			var button = $(this);
			var pid = button.data("pid");

			$.post("/catalog2/admin/product/ajaxProductStoreBind/pid/"+pid, {}, function(response){
				var response = $.parseJSON(response);

				if (!response.success) {
					alert("Ошибка загрузки окна");
					return false;
				}

				$("#ui-dialog-title-price-dialog").html(response.title);
				$("#price-dialog-content").html(response.html);
				$("#price-dialog").dialog("open");

				return false;
			})
		});

		$("#price-dialog-content").on("keyup", "form", function(e){
			if(e.which == 13) {
				$(this).find("input[type=button]").click();
			}
		});

		$("#price-dialog-content").on("click", "#price-dialog-search", function(){
			var form = $(this).parents("form");
			var url = form.prop("action");
			var data = form.serialize();

			$.post(url, data, function(response){
				var response = $.parseJSON(response);

				if (!response.success) {
					alert("Ошибка загрузки окна");
					return false;
				}

				$("#price-dialog-stores").html( $(response.html).find("#price-dialog-stores").html() );
			});
		});

		$("#price-dialog-content").on("click", "#price-dialog-save-for-selected", function(){

			var price = $("#price-dialog-price-for-selected").val();
			var button = $(this);
			var url = button.data("url");

			if ( price == "" ) {
				alert("Цена не указана");
				return false;
			}

			$("#price-dialog-content").find("input").prop("disabled", true);

			if ( !confirm("Вы действительно хотите применить цену ко всем выбранным магазинам?") )
				return false;

			var data = {
				price : price,
				store_ids : $("#price-dialog-store_ids").val(),
			};

			$.post(url, data, function(response){
				var response = $.parseJSON(response);

				if (!response.success) {
					alert("Ошибка сохранения");
					$("#price-dialog-content").find("input").prop("disabled", false);
					return false;
				}

				$(".input-price").each(function() {
					$(this).val(response.price);
				});

				$("#price-dialog-content").find("input").prop("disabled", false);
			});

		});

		$("#price-dialog-content").on("click", ".price-dialog-save-price", function(){

			var button = $(this);
			var price = button.parents(".product-dialog-store-item").find("input[name=\"price\"]").val();
			var url = button.parents(".product-dialog-store-item").find("input[name=\"url\"]").val();
			var ajaxUrl = button.data("url");

			if (price == "" && url != "") {
				if (confirm("Если товар не связан с магазином ценой, то url товара не сохранится! Продолжить?"))
					button.parents(".product-dialog-store-item").find("input[name=\"url\"]").val("")
				else
					return false;
			}

			$.ajax({
				url: ajaxUrl,
				dataType: "json",
				data: {
					price:price,
					url:url
				},
				type: "post",
				success: function(response) {
					if ( !response.success )
						alert(response.error);
				},
				beforeSend: function() {
					button.hide().next().show();
				},
				complete: function() {
					button.show().next().hide();
				},
				error: function() {
					alert("Неизвестная ошибка. Обратитесь к разработчикам.");
				},
			});
		});
	', CClientScript::POS_READY);?>
<!-- /Модальное окно -->

<?php Yii::app()->clientScript->registerScript('initial', '

        $.ajaxSetup({async:true});
        autoScalePage();


        $(document).scroll(function(){
                var topButtonOffset = 120 - $(this).scrollTop();
                $("#topNewProductButton").css("top", topButtonOffset + "px");

        });

        /*
         * Marking product row form as unsaved after changing value in some field
         */
        $("#product-form").on("change", ".product", function(){
                $(this).attr("class", "product unsaved");
        });

        /*
         * Submit form with only .unsaved rows (removing .saved rows)
         */
        $("#submitUnsavedForm").click(function(){
                // quantity of products for submit
                var qt = $(".product").filter(".saved").length;

                if(qt == 0)
                        $("#product-form").submit();

                // find and delete alredy saved products
                $(".product").filter(".saved").each(function(index, element){
                        $(this).remove();
                        // if current element - last then submit form
                        if(index == qt - 1)
                                $("#product-form").submit();
                });
        });

        /*
         * Create new product and product row
         */
        $("#product-add").live("click", function(){
                // create new empty product and insert new row for product
                $.ajax({
                        url: "' . $this->createUrl('productCreate', array('category_id'=>$category->id)) . '",
                        dataType: "json",
                        success: function(data) {
                                if(data.result) {
                                        $(".condensed-table tbody").append(data.html);
                                        autoScalePage()
                                }
                                // saving .unsaved rows
                                autoSaveForm();
                        }
                });
        });

        /*
         * Create new product and copy attributes of current row to new row
         */
        $(".clone").live("click", function(){
                // id of product for clonning
                var pid = $(this).attr("pid");

                // attributes of product for clonning
                var formDataForSave = $("#product-" + pid).find(":input:not(.btn)").serialize();

                $.post("/catalog2/admin/product/productClone/id/" + pid + "/category_id/' . $category->id . '", formDataForSave, function(response) {
                        if(response.result)
                                $(".condensed-table tbody").append(response.html);
                        else
                                alert(response.text);
                        // saving .unsaved rows
                        autoSaveForm();
                }, "json");
        });

        /*
         * Delete product
         */
        $(".delete").live("click", function(){

                if (confirm("Вы действительно хотите удалить товар?")) {
                        // id of product for clonning
                        var pid = $(this).attr("pid");

                        $.post("/catalog2/admin/product/delete/id/" + pid, null, function(response) {
                                if(response.result)
                                        $("#product-" + pid).remove();

                                // saving .unsaved rows
                                autoSaveForm();
                        }, "json");
                }
        });

        /*
         * Product preview
         */
        $(".preview").live("click", function(){
		var url = $(this).data("url");
		var win=window.open(url, "_blank");
  		win.focus();
        })

        /*
         * Delete file (cover, option images..)
         */
        $(".deleteFile").live("click", function(){
                var pid = $(this).attr("pid");
                if(pid == undefined) pid = 0;

                var vid = $(this).attr("vid");
                if(vid == undefined) vid = 0;

                var file_id = $(this).attr("file_id");
                var type = $(this).attr("ftype");

                $.post("/catalog2/admin/product/deleteFile", {type: type, file_id: file_id, pid: pid, vid:vid}, function(response) {
                        if(response.result)
                                $("#uploaded-file-" + file_id).remove();
                }, "json");
        });

        $(".similar-button-add").live("click", function(){
                var pid = $(this).attr("pid");
                var spid = $("#" + pid + "_similar-text-id").val();
                $.post("/catalog2/admin/product/createSimilar", {pid: pid, spid: spid}, function(response) {
                        if(response.success)
                                $("#similar-product-" + response.pid + "-list").append(response.html);
                        else
                                alert(response.message);
                }, "json");
        });

        $(".similar-button-delete").live("click", function(){
                var pid = $(this).attr("pid");
                var spid = $(this).attr("spid");

                if (confirm("Вы действительно хотите удалить связку с аналогичным товаром?")) {
                        $.post("/catalog2/admin/product/deleteSimilar", {pid: pid, spid: spid}, function(response) {
                                if(response.success)
                                        $("#similar-" + response.pid + "-" + response.spid).remove();
                                else
                                        alert(response.message);
                        }, "json");
                }
        });

        $(".color-value-add").live("click", function(){
                var value_id = $(this).attr("value_id");
                var value = $("#Value_"+value_id+"_value").val();
                $.post("/catalog2/admin/product/colorValue", {action: "add", value_id: value_id, value: value}, function(response) {
                        if(response.success)
                                $("#color-value-list-"+value_id).append(response.html);
                        else
                                alert(response.message);
                }, "json");
        });

        $(".color-value-delete").live("click", function(){
                var value_id = $(this).attr("value_id");
                var value = $(this).attr("value");
                $this = $(this);
                $.post("/catalog2/admin/product/colorValue", {action: "delete", value_id: value_id, value: value}, function(response) {
                        if(response.success)
                                $this.parent().remove();
                        else
                                alert(response.message);
                }, "json");
        });

', CClientScript::POS_READY);?>

<?php Yii::app()->clientScript->registerScript('autoSave', '
        /*
         * Saving product rows marked as .unsaved (via ajax post)
         */
        function autoSaveForm() {
                var pidsForSave = new Array(); // array of product id`s for saving
                var formDataForSave;           // serialized products rows for saving via post request
                var cid;                       // category_id of products

                // find products for saving
                $(".product").filter(".unsaved").each(function(){

                        // insert product_id into array for next saving
                        var pid = $(this).attr("pid");
                        pidsForSave.push(pid);

                        // delete product_id from array if product row has empty fields
                        $(this).find(":input:not(.btn)").each(function(){
                                if($(this).val() === "")
                                        delete pidsForSave[pid];
                        });
                });

                // if there are products for saving then getting product rows form data and sending via post
                if(pidsForSave.length > 0) {
                        cid = $("#category_id").val(); // current category_id
                        formDataForSave = $(".unsaved").find(":input:not(.btn)").serialize();
                        $.post("/catalog2/admin/product/update/ids/" + pidsForSave + "/category_id/" + cid, formDataForSave, function(response) {
                                var pid;       // tmp value for product_id
                                // marking products as .saved or .unsaved according to response
                                for(pid in response.result) {
                                        if(response.result[pid])
                                                $("#product-" + pid).attr("class", "product saved");
                                        else
                                                $("#product-" + pid).attr("class", "product unsaved");
                                }
                        }, "json");
                }
        }

        function autoScalePage()
        {
                $(".container-fluid").children(".content").width($(".condensed-table").width() + 20);
        }

        function showHideSelector(button, id)
        {
                var sel = $("#" + id);
                if(sel.css("display") == "none") {
                        sel.css("display", "block");
                        button.text("скрыть");
                }
                else {

                        sel.css("display", "none");
                        button.text("показать");
                }
        }
', CClientScript::POS_BEGIN);?>



<script>
        $(document).ready(function() {

                /**
                 * Event for cover upload
                 */
                $('.coverFile').live("change", function(){
                        var pid = $(this).attr('pid');
                        sendFiles({
                                files:this.files,
                                update: "#cover-preview-" + pid,
                                url: '<?php echo $this->createUrl("imageUpload"); ?>/type/cover/pid/'+pid
                        });
                });

                /**
                 * Event for option image upload
                 */
                $('.optionImage').live("change", function(){
                        var pid = $(this).attr('pid');
                        var oid = $(this).attr('oid');
                        sendFiles({
                                files:this.files,
                                append: "#option-preview-" + pid + "-" + oid,
                                url: '<?php echo $this->createUrl("imageUpload"); ?>/type/value/pid/'+pid+'/oid/'+oid
                        });
                });

                /**
                 * Event for product images upload
                 */
                $('.imageFiles').live("change", function(){
                        var pid = $(this).attr('pid');
                        sendFiles({
                                files:this.files,
                                append: "#images-preview-" + pid,
                                url: '<?php echo $this->createUrl("imageUpload"); ?>/type/image/pid/'+pid
                        });
                });

		$('.form').on("click", ".url-image-upload", function(){

			var button = $(this);
			var input = $(this).prev('input[type=text]');
			var type = input.prop('name');
			var url = input.val();
			var pid = input.data('pid');

			$.post('<?php echo $this->createUrl("imageUrlUpload"); ?>/pid/' + pid, {url:url,type:type}, function(response) {
				response = $.parseJSON(response);
				if (!response.result)
					alert('Ошибка загрузки');

				if (type == "cover") {
					$("#cover-preview-" + pid).html(response.html);
				}

				if (type == "image") {
					$("#images-preview-" + pid).append(response.html);
				}

				input.val("");
				return false;
			});
		});

                /**
                 * Iterator for files array
                 */
                function sendFiles(options, i) {
                        if(typeof(i) == 'undefined')
                                i = 0;
                        if(i < options.files.length) {
                                // begin upload for file № i
                                uploadFile(options.files[i], options.url, i, function(i, response){
                                        /*
                                         * handler for uploaded file
                                         */
                                        response = $.parseJSON(response);

                                        if(options.update != undefined)
                                                $(options.update).html(response.html);

                                        if(options.append != undefined)
                                                $(options.append).append(response.html);

                                        sendFiles(options, i+1);
                                });
                        }
                }

                /**
                 * Upload file via ajax
                 */
                function uploadFile(file, url, i, callback) {
                        var xhr = new XMLHttpRequest();
                        var formData = new FormData();
                        // Событие, вызванное по итогу отправки очередного файла
                        xhr.onreadystatechange = function(){
                                if(this.readyState == 4) {
                                        if(this.status == 200) {
                                                // some handler
                                        }
                                        delete file;
                                        delete this;
                                        if(callback != undefined) callback(i, this.responseText);
                                }
                        }
                        xhr.open("POST", url);
                        formData.append('Product[file]', file);
                        xhr.send(formData);
                }
        });
</script>


<script type="text/javascript">
	$(function(){
		// Нажатие на кнопку "Обрезать"
		$('#product-form').on('click', 'a.crop_img', function(){
			var data = $(this).data();
			var w = window.open('/catalog2/admin/product/cropCover/type/'+data['type']+'/pid/'+data['pid']+'/fileid/'+data['fileid'], '', 'top=10,left=0,width=600,height=600,location=0,menubar=0');

			return false;
		});
	});
</script>




