<?php
/**
 * @var $model MainUnit
 * @var $photo UploadedFile
 */
$this->breadcrumbs=array(
	'Каталог товаров'=>array('/catalog/admin/catgory/index'),
	'Главная товаров, список помещений'=>array('/catalog/admin/mainproduct/index'),
	'Редактирование предложения',
);
/** @var $cs CClientScript */
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile('/js/jquery.Jcrop.min.js');
$cs->registerCssFile('/css/jquery.Jcrop.css');
?>

<h1>Редактирование предложения #<?php echo $model->id; ?></h1>

<?php /** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'main-unit-form',
	'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->errorSummary($model); ?>

<div class="row">
	<div class="span8 clearfix">
		<?php echo $form->textFieldRow($model,'origin_id',array('class'=>'span5 product_id', 'autocomplete' => 'off', 'id'=>'origin')); ?>
	</div>
	<div class="span2">
		<span id="show" class="btn primary small">показать</span>
	</div>
</div>

<?php /*

*/ ?>

<div id="replace" class="clearfix">
	<?php echo $form->textFieldRow($model, 'name', array('class'=>'span5')); ?>
	<?php echo $form->dropDownListRow($model, 'store_id', $stores, array('class'=>'span7')); ?>
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
				'select'    => 'js:function(event, ui) { $("#city_id").val(ui.item.id); $("#show").trigger("click"); }',
				'change'    => 'js:function(event, ui) { if(ui.item === null) {$("#city_id").val("");}}',
			),
			'htmlOptions' => array('class' => 'span5')
		));
		?>
		<?php echo CHtml::hiddenField("city_id", 0);?>
	</div>
</div>


<div class="clearfix">
	<label>Пикча</label>
	<div class="input">
		<?php echo CHtml::image('/'.$photo->getPreviewName(MainUnit::$preview['crop_234x180']), '', array('id'=>'big_photo')); ?>
	</div>
</div>

<input type="hidden" id="x" name="img[x]" />
<input type="hidden" id="y" name="img[y]" />
<input type="hidden" id="w" name="img[w]" />
<input type="hidden" id="h" name="img[h]" />
<input type="hidden" id="photo_id" name="img[photo]" />

<div class="clearfix">
	<label>Помещения</label>
	<div class="input">
		<ul><?php foreach ($mainRooms as $room) {
			echo CHtml::openTag('li');
			$checked = isset($sRooms[$room->id]);
			echo CHtml::checkBox('MainUnitRoom['.$room->id.']', $checked, array()) . $room->name;
			echo CHtml::closeTag('li');
		} ?></ul>
	</div>
</div>

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
				'children'=>MainUnit::getTree($sCategory),
				'onSelect' => 'js:function(flag, node){
					$(node.span).find("input:checkbox").attr("checked", flag);
				}',
				'onRender' => 'js:function(node, nodeSpan){
					if (node.data.key=="_statusNode")
						return;
					var input=$("<input type=\'checkbox\' class=\'hide\'>").attr({"checked":node.data.select, name:"MainUnitCategory["+node.data.key+"]"});
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

<?php echo $form->dropDownListRow($model, 'status', MainUnit::$statusNames, array('class'=>'span5')); ?>

<div class="actions">
	<?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary')); ?>
	<?php echo CHtml::button('Отмена', array('class'=>'btn default','onclick' => "document.location = '".$this->createUrl('/catalog/admin/mainproduct/index')."'"));?>
</div>

<?php $this->endWidget(); ?>

<script type="text/javascript">
	$('#origin').keydown(function(event){
		if (event.keyCode == 13) {
			$('#show').trigger('click');
			return false;
		}
	});
	var jCrop;
	var imgWidth=0;
	var imgHeight=0;
	$('#big_photo').Jcrop({
		onSelect: updateCoords,
		aspectRatio: 1.3

	}, function(){
		jCrop=this;
	});

	function updateCoords(c)
	{
		if (imgWidth===0){
			$('#x').val(0);
			$('#w').val(0);
		} else {
			$('#x').val(c.x/imgWidth);
			$('#w').val(c.w/imgWidth);
		}

		if (imgHeight===0){
			$('#y').val(0);
			$('#h').val(0);
		} else {
			$('#y').val(c.y/imgHeight);
			$('#h').val(c.h/imgHeight);
		}
	}

	$('#replace').on('click', '.photos img', function(){
		var url = $(this).data('url');
		$('#photo_id').val( $(this).data('id') );

		var photo = $('#big_photo');
		photo.load(function(){
			$('#x').val(0);
			$('#y').val(0);
			$('#w').val(0);
			$('#h').val(0);
			jCrop.setImage(url);
		});
		var origin=new Image();
		origin.onload = function(){
			imgWidth=this.width;
			imgHeight=this.height;
			photo.attr('src', url);
		}
		origin.src=url;

		return false;
	});

	$('#show').click(function(){
		var productId = parseInt( $('#origin').val() );
		if (isNaN(productId)){
			$('#origin').parents('.clearfix').addClass('error');
			return false;
		}

		$('#origin').parents('.clearfix').removeClass('error');

		var city_id = $('#city_id').val();

		$.ajax({
			url: 	'/catalog/admin/mainproduct/axgetcontent',
			dataType: 'json',
			type: 'post',
			data: {product_id: productId, city_id: city_id},
			async: 	false,
			success:function (response) {
				if (response.success){
					$('#replace').html( response.html );
				}
			},
			error: function(response){
				window.location.reload();
			}

		});
	});

</script>