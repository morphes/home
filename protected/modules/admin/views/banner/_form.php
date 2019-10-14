<?php
/**
 * Для автокомплитов
 */
$cs = Yii::app()->clientScript;
$cssCoreUrl = $cs->getCoreScriptUrl();
$cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css');
$cs->registerCoreScript('jquery.ui');
?>

<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'banner-item-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div id="banner_item">

		<?php echo CHtml::hiddenField('item_id', $model->id); ?>

		<?php echo $form->textFieldRow($model,'customer',array('class'=>'span5','maxlength'=>255)); ?>
		<?php echo $form->textFieldRow($model,'url',array('class'=>'span5','maxlength'=>255)); ?>

		<?php echo $form->textAreaRow($model,'htmlcode',array('class'=>'span10')); ?>

		<div class="clearfix">
			<label for="BannerItem[imageFile]">Статический файл</label>
			<div class="input">
				<?php $this->widget('ext.FileUpload.FileUpload', array(
					'url'=> $this->createUrl('fileUpload', array('type'=>'image')),
					'postParams'=>array('item_id'=>$model->id),
					'config'=> array(
						'fileName' => 'BannerItem[imageFile]',
						'onSuccess'=>'js:function(response){
							if (response.success)
								$("#imageBanner").html(response.html);
							else
								alert(response.errors);
						}',
					),
					'htmlOptions'=>array('id'=>'imageInput', 'name'=>'imageInput'),
				)); ?>
			</div>
		</div>

		<div id="imageBanner">
			<?php if ($model->file_id) $this->renderPartial('_bannerFile_image', array('model'=>$model)); ?>
		</div>

		<div class="clearfix">
			<label for="BannerItem[swfFile]">Динамический файл</label>
			<div class="input">
				<?php $this->widget('ext.FileUpload.FileUpload', array(
				'url'=> $this->createUrl('/admin/banner/fileUpload', array('type'=>'swf')),
				'postParams'=>array('item_id'=>$model->id),
				'config'=> array(
					'fileName' => 'BannerItem[swfFile]',
					'onSuccess'=>'js:function(response){
							if (response.success)
								$("#swfBanner").html(response.html);
							else
								alert(response.errors);
						}',
				),
				'htmlOptions'=>array('id'=>'swfInput', 'name'=>'swfInput'),
			)); ?>
			</div>
		</div>

		<div id="swfBanner">
			<?php if ($model->swf_file_id) $this->renderPartial('_bannerFile_swf', array('model'=>$model)); ?>
		</div>

		<?php echo $form->dropDownListRow($model,'type_id',BannerItem::$typeLabels, array('class'=>'span5 banner_type')); ?>

		<?php echo $form->dropDownListRow($model,'status', BannerItem::$statusLabels, array('class'=>'span5')); ?>

		<div class="alert-message block-message error" id="banner_item_error" style="display: none;"></div>
	</div>

	<div id="sections_place">
		<?php foreach ($model->itemSections as $itemSection) : ?>
			<?php $this->renderPartial('_sectionForm', array('model'=>$model, 'itemSection'=>$itemSection)); ?>
		<?php endforeach; ?>
	</div>

	<hr />

	<p style="padding-left: 190px; font-weight: bold; margin-bottom: 20px; font-size: 16px;">
		<?php echo CHtml::link('Добавить раздел', '#', array('id'=>'create_section')); ?>
	</p>



	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>


<?php Yii::app()->clientScript->registerScript('form', '

	// добавленеи секции
	$("#create_section").click(function(){
		$.post("/admin/banner/ajaxCreateItemSection", { item_id : $("#item_id").val() }, function(response) {
			response = $.parseJSON(response);
			if (response.success)
				$("#sections_place").append(response.html);
			else
				alert("Ошибка создания раздела для баннера");
		});
		return false;
	});

	// удаление секции
	$("#sections_place").on("click", ".delete_section", function(){
		var itemSectionId = $(this).attr("item_section_id");
		$.post("/admin/banner/ajaxDeleteItemSection", { itemSectionid : itemSectionId }, function(response) {
			response = $.parseJSON(response);
			if (response.success)
				$("#section_" + itemSectionId).remove();
			else
				alert("Ошибка удаления раздела для баннера");
		});
		return false;
	});

	// сохранение формы базовых настроек баннера
	$("#banner_item").on("change", "input:not([type=file]), select", function(){
		var error_block = $("#banner_item_error");

		$.post("/admin/banner/ajaxUpdateBannerItem/id/" + $("#item_id").val(), $("#banner_item").find("input, textarea, select, checkbox").serialize(), function(response) {
			response = $.parseJSON(response);
			if (!response.success) {
				error_block.html(response.errors);
				error_block.show();
			} else {
				error_block.html("");
				error_block.hide();
			}

		});
		return false;
	});

	// автосохранение формы секции
	$("#sections_place").on("change", "input, select", function(){
		var form = $(this).parents(".section");
		var itemSectionId = form.attr("item_section_id");
		var form_data = form.find("input, textarea, select, checkbox" ).serialize();
		var error_block = form.find(".itemSectionErrors");
		var error_block_geos = $("#section_"+itemSectionId).find(".itemSectionGeoErrors");

		$.post("/admin/banner/ajaxUpdateItemSection/item_section_id/" + itemSectionId, form_data, function(response) {
			response = $.parseJSON(response);
			if (!response.success) {
				error_block.html(response.errors);
				error_block.show();
			} else {
				error_block.html("").hide();
				error_block_geos.html("").hide();
			}

		});
		return false;
	});

	// выбор типа добавляемого гео-таргетинга (страна, город)
	$("#sections_place").on("change", ".geo_switcher", function(){
		if ( $(this).val() == "city_id" ) {
			$(this).parent().find(".country_id_selector").hide();
			$(this).parent().find(".city_id_selector").show();
		} else {
			$(this).parent().find(".country_id_selector").show();
			$(this).parent().find(".city_id_selector").hide();
		}

		return false;
	});

	$("#sections_place").on("click", ".deleteGeo", function(){
		var $this = $(this);
		$.post("/admin/banner/ajaxDeleteItemSectionGeo", {geo_id : $this.parent().attr("geo_id")}, function(response) {
			response = $.parseJSON(response);
			if (response.success) {
				$this.parent().remove();
			} else {
				alert("Ошибка удаления");
			}

		});
		return false;
	});

	// удаление swf файла
	$("#banner_item").on("click", ".delSwf", function(){
		$.post("/admin/banner/ajaxDeleteFile", {item_id : $("#item_id").val(), type:"swf"}, function(response) {
			response = $.parseJSON(response);
			if (response.success) {
				$(".swf-preview").remove();
			} else {
				alert("Ошибка удаления");
			}
		});
		return false;
	});

	// удаление image файла
	$("#banner_item").on("click", ".delImg", function(){
		$.post("/admin/banner/ajaxDeleteFile", {item_id : $("#item_id").val(), type: "img"}, function(response) {
			response = $.parseJSON(response);
			if (response.success) {
				$(".img-preview").remove();
			} else {
				alert("Ошибка удаления");
			}
		});
		return false;
	});

	$(".banner_type").change(function(){
		$.post("/admin/banner/ajaxLoadSectionsForType", {item_id : $("#item_id").val(), type: $(this).val()}, function(response) {
			response = $.parseJSON(response);
			if (response.success) {
				$(".available_sections").html(response.html).change();
			} else {
				alert("Ошибка загрузки доступных разделов");
			}
		});
		return false;
	});
', CClientScript::POS_READY); ?>

<?php Yii::app()->clientScript->registerScript('geo-assign', '

	// привязка выбранной страны/города к секции
	function assignGeo(item_section_id, type, geo_id) {

		var error_block = $("#section_"+item_section_id).find(".itemSectionGeoErrors");

		$.post("/admin/banner/ajaxCreateItemSectionGeo/item_section_id/" + item_section_id, {type:type, geo_id:geo_id}, function(response){
			response = $.parseJSON(response);
			if (response.success) {
				$("#itemSection_"+item_section_id+"_Geos").append(response.html);
				error_block.html("");
				error_block.hide();
			} else {
				error_block.html(response.errors);
				error_block.show();
			}
		});

		return false;
	}
', CClientScript::POS_END); ?>