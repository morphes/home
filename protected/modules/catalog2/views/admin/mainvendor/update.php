<?php
$this->breadcrumbs=array(
	'Каталог товаров'=>array('/catalog2/admin/catgory/index'),
	'Главная товаров, список брендов'=>array('/catalog2/admin/mainvendor/index'),
	'Редактирование бренда',
);

?>

<h1>Редактирование бренда #<?php echo $model->id; ?></h1>

<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'main-unit-form',
	'enableAjaxValidation'=>false,
)); ?>

<?php $class = $model->hasErrors('origin_id') ? ' error' : ''; ?>
<div class="clearfix<?php echo $class; ?>">
	<label>Производитель</label>
	<div class="input">
		<?php
		$this->widget('application.components.widgets.EAutoComplete', array(
			'valueName'	=> $model->getVendorName(),
			'sourceUrl'	=>  '/admin/utility/acvendor',
			'value'		=> $model->origin_id,
			'options'	=> array(
				'showAnim'	=>'fold',
				'open' => 'js:function(){}',
				'minLength' => 3
			),
			'htmlOptions'	=> array('id'=>'origin', 'name'=>'MainUnit[origin_id]', 'class' => 'span5'),
			'cssFile' => null,
		));
		?>
	</div>
</div>

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
		'ajaxController' => '/catalog2/admin/mainvendor/',
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
	<?php echo CHtml::button('Отмена', array('class'=>'btn default','onclick' => "document.location = '".$this->createUrl('/catalog2/admin/mainvendor/index')."'"));?>
</div>

<?php $this->endWidget(); ?>