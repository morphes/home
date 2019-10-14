<?php
$this->breadcrumbs=array(
	'Управление меню' => array('index'),
	Menu::$menuNames[$type_id]
);
?>

<h1>Меню</h1>

<form action="/<?php echo $this->getRoute(); ?>" class="form-filter">
	Тип меню:
	<?php echo CHtml::dropDownList('type_id', $type_id, Menu::$menuNames, array('class' => 'menu_type'));?>
</form>


<?php echo CHtml::button(
	'Добавить',
	array(
		'onclick' => 'location = "'.$this->createUrl('createmain', array('type_id' => (!is_null($type_id) ? $type_id : 0) )).'"',
		'class' => 'btn primary'
	)
); ?>


<?php // Подключаем скрипт для смены позиций элементов в списке
Yii::app()->clientScript->registerScriptFile('/js/admin/arrowsUpDown.js'); ?>

<script type="text/javascript">
	// Инициализируем стрелки перемещения позиции
	arrowsUpDown.init("menu-grid", "/admin/menu/");
</script>

<?php
$this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'		=> 'menu-grid',
	'ajaxUpdate'	=> 'menu-grid',
	'dataProvider'	=> $dataProvider,
	'selectionChanged' => 'js:function(event){ 
		arrowsUpDown.showArrows();
		arrowsUpDown.moveToCursor();
	}',
	'afterAjaxUpdate' => 'js:function(){
		arrowsUpDown.selectLastElement();
	}',

	'columns' => array(
		array(
			'name' => 'id',
			'value' => '$data->id',
			'htmlOptions' => array("class" => 'elementId')
		),
		array(
			'name' => 'key',
			'value' => '$data->key',
		),
		array(
			'name' => 'label',
			'value' => '$data->label',
		),
		array(
			'name' => 'label_hidden',
			'value' => '$data->label_hidden',
		),
		array(
			'name' => 'status',
			'type' => 'raw',
			'value' => '($data->status == Menu::STATUS_ACTIVE) ? "<span class=\"label success\">".Menu::$statusNames[$data->status]."</span>" : Menu::$statusNames[$data->status]',
		),
		array(
			'name' => 'url',
			'value' => '$data->url',
		),
		array(
			'name' => 'create_time',
			'value' => 'date("d.m.Y", $data->create_time)',
		),
		array(
			'name' => 'update_time',
			'value' => 'date("d.m.Y", $data->update_time)',
		),
		array(
			'name' => 'submenu',
			'value' => 'CHtml::link("Подменю", Yii::app()->controller->createUrl("/admin/menu/submenu", array("parent_id" => $data->id)))',
			'type' => 'raw',
		),
		array(
			'class' => 'CButtonColumn',
			'template' => '{update} {delete}',

			'htmlOptions' => array(),
			'headerHtmlOptions' => array(),
		),
		
	),
));
?>

<?php Yii::app()->clientScript->registerScript('search', "
$(function(){
	$('select.menu_type').change(function(){
		$('.form-filter').submit();
	});
});
"
	
);?>