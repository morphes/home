<?php
$this->breadcrumbs=array(
	'Управление меню' => array('index'),
	Menu::$menuNames[$parentModel->type_id] => $this->createUrl('index', array('type_id' => $parentModel->type_id)),
	$parentModel->label
);
?>

<h1><?php echo $parentModel->label;?></h1>


<?php
echo CHtml::button(
	'Добавить', array(
		'onclick' => 'location = "' . $this->createUrl('create', array('parent_id' => $parentModel->id)) . '"',
		'class' => 'btn primary'
	)
);
?>

<?php // Подключаем скрипт для смены позиций элементов в списке
Yii::app()->clientScript->registerScriptFile('/js/admin/arrowsUpDown.js'); ?>

<script type="text/javascript">
	// Инициализируем стрелки перемещения позиции
	arrowsUpDown.init("menu-grid", "/admin/menu/");
</script>

<?php
$this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id' => 'menu-grid',
	'ajaxUpdate' => 'menu-grid',
	'dataProvider' => $dataProvider,
	'selectionChanged' => 'js:function(){ 
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
			'class' => 'CButtonColumn',
			'template' => '{update} {delete}',
			
			'htmlOptions' => array(),
			'headerHtmlOptions' => array(),
		),
	),
));
?>