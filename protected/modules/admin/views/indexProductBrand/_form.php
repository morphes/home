<?php
/**
 * @var $model IndexProductBrand
 */


Yii::app()->clientScript->registerCss('brand_tab', '
	#IndexProductBrand_tabIds label {
		float: none;
	}
');




/** @var $form BootActiveForm */
$form = $this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id'                   => 'index-product-brand-form',
	'enableAjaxValidation' => false,
	'htmlOptions'          => array('enctype' => 'multipart/form-data')
)); ?>

<p><a target="_blank" href="http://doc.myhome.ru/p/51">Подробное описание заполнения формы бренда</a></p>


<?php echo $form->errorSummary($model); ?>

<?php if ($model->image_id) : ?>
	<div class="clearfix">
		<label>Привязанный логотип</label>

		<div class="input"
		     style="width: 90px; height: 90px; border: 1px dashed #ddd">
			<?php echo CHtml::image('/' . $model->uploadedFile->getPreviewName(IndexProductBrand::$preview[ 'resize_90' ])); ?>
		</div>
	</div>
<?php endif; ?>

<div class="clearfix">
	<?php echo CHtml::label($model->getAttributeLabel('city_id').' <span class="required">*</span>', 'City_id'); ?>
	<div class="input">
		<?php
		$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
			'name'=>'AC_City_id',
			'value'=> isset($model->city->id) ? "{$model->city->name} ({$model->city->region->name}, {$model->city->country->name})" : '',
			'sourceUrl'=>'/utility/autocompletecity',
			'options'=>array(
				'minLength'=>'3',
				'showAnim'=>'fold',
				'select'=>'js:function(event, ui) {$("#IndexProductBrand_city_id").val(ui.item.id).keyup();}',
				'change'=>'js:function(event, ui) {if(ui.item === null) {$("#IndexProductBrand_city_id").val("");}}',
			),
			'htmlOptions' => array('class' => 'span5')
		));
		?>
		<?php echo CHtml::activeHiddenField($model,  "city_id");?>
	</div>
</div>

<div class="clearfix">
	<label>Город для фильтрации магазинов</label>
	<div class="input">
		<?php
		$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
			'name'        => 'City_id',
			'value'       => '',
			'sourceUrl'   => '/utility/autocompletecity',
			'options'     => array(
				'minLength' => '3',
				'showAnim'  => 'fold',
				'select'    => 'js:function(event, ui) {$("#city_id").val(ui.item.id).keyup();}',
				'change'    => 'js:function(event, ui) {if(ui.item === null) {$("#city_id").val("");}}',
			),
			'htmlOptions' => array('class' => 'span5')
		));
		?>
		<?php echo CHtml::hiddenField("city_id", 0);?>
	</div>
</div>


<?php echo $form->dropDownListRow($model, 'type', array('' => '( выберите тип )') + IndexProductBrand::$typeName, array('class' => 'span5')); ?>

<script>
	$('#IndexProductBrand_type').change(function () {
		var type = $(this).val();
		// Очищаем сохраненное значение сущности (магазни или производитель)
		$('IndexProductBrand_item_id').val(0);
		switch (type) {
			case '<?php echo IndexProductBrand::TYPE_VENDOR;?>':
				$('.tab_store').addClass('hide');
				$('.tab_vendor').removeClass('hide');
				break;
			case '<?php echo IndexProductBrand::TYPE_STORE;?>':
				$('.tab_vendor').addClass('hide');
				$('.tab_store').removeClass('hide');
				break;
			default:
				$('.tab_vendor, .tab_store').addClass('hide');
				break;
		}
	});
</script>


<?php
/* ----------------
 *  Производитель
 * ----------------
 */
?>
<div class="tab_vendor hide">
	<div class="clearfix">
		<label>Производитель</label>

		<div class="input">
			<?php
			$this->widget('application.components.widgets.EAutoComplete', array(
				'valueName'   => '',
				'sourceUrl'   => '/admin/utility/AcVendor',
				'value'       => '',
				'options'     => array(
					'showAnim'  => 'fold',
					'minLength' => 2,
					'select'    => 'js:function(event, ui){
							var vendorId = ui.item.id;

							$.ajax({
								url: "/admin/indexProductBrand/AjaxGetLogoVendor/id/" + vendorId,
								type: "GET",
								dataType: "JSON",
								success: function(data) {

									// Показываем фотку
									$("#ac_logo").attr("src", data.imgUrl);
									$("#IndexProductBrand_name").attr("value", data.name);
									$("#ac_image_id").val(data.imgId);

									// Сохраняем id элемента.
									$("#IndexProductBrand_item_id").val(data.id);
								},
								error: function(data) {
									alert(data.responseText);
								}
							});
						}'
				),
				'htmlOptions' => array('id' => 'vendor_ac', 'name' => 'vendor_name', 'class' => ''),
				'cssFile'     => null,
			));
			?>
		</div>
	</div>
</div>

<?php
/* ----------------
 *  Магазин
 * ----------------
 */
?>
<div class="tab_store hide">
	<div class="clearfix">
		<label>Магазин</label>

		<div class="input">
			<?php
			$this->widget('application.components.widgets.EAutoComplete', array(
				'valueName'   => '',
				'sourceUrl'   => '#', // Генерится каждый раз в событии serach автокомплита.
				'value'       => '',
				'options'     => array(
					'showAnim'  => 'fold',
					'minLength' => 2,
					'search'    => 'js:function(event) {
						// С учетом выбранного города генерим ссылку для автокомплита
						$( "#store_ac" ).autocomplete(
							"option",
							"source",
							"/admin/utility/AcStore/largeReturn/true/city_id/" + $("#city_id").val()
						);
					}',
					'select'    => 'js:function(event, ui){
						var storeId = ui.item.id;

						$.ajax({
							url: "/admin/indexProductBrand/AjaxGetLogoStore/id/" + storeId,
							type: "GET",
							dataType: "JSON",
							success: function(data) {

								// Показываем фотку

								$("#ac_logo").attr("src", data.imgUrl);
								$("#IndexProductBrand_name").attr("value", data.name);
								$("#ac_image_id").val(data.imgId);

								// Сохраняем id элемента.
								$("#IndexProductBrand_item_id").val(data.id);
							},
							error: function(data) {
								alert(data.responseText);
							}
						});
					}'

				),
				'htmlOptions' => array('id' => 'store_ac', 'name' => 'store_name', 'class' => ''),
				'cssFile'     => null,
			));
			?>
		</div>
	</div>
</div>

<?php // Сюда складывается ID Логотипа из автокомлпита ?>
<input type="hidden" name="ac_image_id" id="ac_image_id" value="">

<?php echo $form->hiddenField($model, 'item_id'); ?>

<div class="clearfix">
	<div class="input">
		<?php // Это шаблон, в который вставляется логотип элемента
		// найденного по автокомплиту ?>
		<img id="ac_logo"
		     src=""
		     alt="">
	</div>
</div>


<?php echo $form->fileFieldRow($model, 'file'); ?>

<?php echo $form->textFieldRow($model, 'name'); ?>


<?php echo $form->dropDownListRow($model, 'status', IndexProductBrand::$statusName, array('class' => 'span5')); ?>


<div class="clearfix">
	<label>Вкладки</label>

	<div class="input">
		<?php
		echo CHtml::activeCheckBoxList(
			$model,
			'tabIds',
			Chtml::listData(IndexProductTab::model()->findAll(), 'id', 'name')
		);
		?>
	</div>
</div>

<div class="actions">
	<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать'
		: 'Сохранить', array('class' => 'btn primary')); ?>
</div>

<?php $this->endWidget(); ?>
