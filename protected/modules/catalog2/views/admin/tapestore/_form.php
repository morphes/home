<?php
/**
 * @var $model Tapestore
 */

/** @var $form BootActiveForm */
$form = $this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id'                   => 'index-tapestore-form',
	'enableAjaxValidation' => false,
	'htmlOptions'          => array('enctype' => 'multipart/form-data')
)); ?>

<?php echo $form->errorSummary($model); ?>

<?php if ($model->image_id) : ?>
	<div class="clearfix">
		<label>Привязанный логотип</label>

		<div class="input"
		     style="width: 90px; height: 90px; border: 1px dashed #ddd">
			<?php echo CHtml::image('/' . $model->getImage()->getPreviewName(Tapestore::$preview[ 'resize_90' ])); ?>
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
				'select'=>'js:function(event, ui) {$("#Tapestore_city_id").val(ui.item.id).keyup();}',
				'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Tapestore_city_id").val("");}}',
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
		<?php echo CHtml::hiddenField('', 0, array('id'=>'city_id'));?>
	</div>
</div>

<?php
/* ----------------
 *  Магазин
 * ----------------
 */
?>
<div class="tab_store">
	<div class="clearfix">
		<label>Магазин</label>

		<div class="input">
			<?php
			$this->widget('application.components.widgets.EAutoComplete', array(
				'valueName'   => '',
				'sourceUrl'   => '#', // Генерится каждый раз в событии serach автокомплита.
				'value'       => $model->store_id,
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
							url: "/catalog2/admin/tapestore/ajaxGetLogoStore/id/" + storeId,
							type: "GET",
							dataType: "JSON",
							success: function(data) {
								$("#ac_logo").attr("src", data.imgUrl);
								$("#ac_image_id").val(data.imgId);
							},
							error: function(data) {
								alert(data.responseText);
							}
						});
					}'

				),
				'htmlOptions' => array('id' => 'store_ac', 'name' => 'ac_store_id', 'class' => ''),
				'cssFile'     => null,
			));
			?>
		</div>
	</div>
</div>

<?php // Сюда складывается ID Логотипа из автокомлпита ?>
<input type="hidden" name="ac_image_id" id="ac_image_id" value="">

<div class="clearfix">
	<div class="input">
		<?php // Это шаблон, в который вставляется логотип элемента
		// найденного по автокомплиту ?>
		<img id="ac_logo" src="" alt="">
	</div>
</div>


<?php echo $form->fileFieldRow($model, 'file'); ?>

<div class="clearfix span9">
	<label>Категории</label>
	<div class="input">
		<?php
		$this->widget('ext.NestedDynaTree.NestedDynaTree', array(
			'modelClass' => 'Category',
			'manipulationEnabled' => false,
			'dndEnabled' => false,
			'options' => array(
				'initAjax'=>null,
				'checkbox'=>true,
				'persist' => false,
				'selectMode'=>2,
				'debugLevel'=>0,
				'children'=>Tapestore::getTree($sCategory),
				'onSelect' => 'js:function(flag, node){
					$(node.span).find("input:checkbox").attr("checked", flag);
				}',
				'onRender' => 'js:function(node, nodeSpan){
					if (node.data.key=="_statusNode")
						return;
					var input=$("<input type=\'checkbox\' class=\'hide\'>").attr({"checked":node.data.select, name:"TapestoreCategory["+node.data.key+"]"});
					$(nodeSpan).append(input);
				}'
			),

		));
		?>
	</div>
</div>

<div class="clearfix">
	<?php echo CHtml::label($model->getAttributeLabel('start_time'), 'start_time'); ?>
	<div class="input">
		<?php
		$this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'htmlOptions' => array('class'=>'span5'),
			'name'=>'date_to',
			'value'=> empty($model->start_time) ? '' : date('d.m.Y', $model->start_time),
			'options' => array(
				'autoLanguage' => false,
				'dateFormat' => 'dd.mm.yy',
				'timeFormat' => 'hh:mm',
				'changeMonth' => true,
				'changeYear' => true,
			),
		));
		?>
	</div>
</div>

<div class="clearfix">
	<?php echo CHtml::label($model->getAttributeLabel('end_time'), 'end_time'); ?>
	<div class="input">
		<?php
		$this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'name'=>'date_from',
			'value'=> empty($model->end_time) ? '' : date('d.m.Y', $model->end_time),
			'htmlOptions' => array('class'=>'span5'),
			'options' => array(
				'autoLanguage' => false,
				'dateFormat' => 'dd.mm.yy',
				'timeFormat' => 'hh:mm',
				'changeMonth' => true,
				'changeYear' => true,
			),
		));
		?>
	</div>
</div>

<?php echo $form->dropDownListRow($model, 'status', Tapestore::$statusNames, array('class' => 'span5')); ?>


<div class="actions">
	<?php echo CHtml::submitButton($model->getIsNewRecord() ? 'Создать'
		: 'Сохранить', array('class' => 'btn primary')); ?>
</div>

<?php $this->endWidget(); ?>
