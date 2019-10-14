<?php
$parentName = Config::$ideaTypesName[$parent_model->idea_type_id];
$parentLink = $this->createUrl($this->id . '/index', array('idea_type_id' => $parent_model->idea_type_id));

$this->breadcrumbs = array(
	'Идеи' => array('/idea/admin/interior/list'),
	'Свойства «'.$parentName.'»' => $parentLink,
	'Добавление свойства'
);
?>

<?php echo CHtml::tag('h1', array(), $parent_model->option_value, true);?>
<div class="row">
	<div class="span6">
		<?php echo CHtml::link('&larr; назад в '.$parentName, $parentLink); ?>
	</div>
</div>


<br><br>

<?php echo CHtml::link('Добавить', $this->createUrl($this->id.'/create/',array('id' => $parent_model->id, 'key' => $key)), array('class' => 'btn primary'));?>


<?php // Подключаем скрипт для смены позиций элементов в списке
Yii::app()->clientScript->registerScriptFile('/js/admin/arrowsUpDown.js'); ?>

<script type="text/javascript">
	// Инициализируем стрелки перемещения позиции
	arrowsUpDown.init("interior-grid", "/idea/admin/property/");
</script>

<?php
$this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'		=> 'interior-grid',
	'ajaxUpdate'	=> 'interior-grid',
	'dataProvider'	=> $dataProvider,
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
			'name' => 'option_key',
			'value' => '$data->option_key',
		),
		array(
			'name' => 'option_value',
			'value' => '$data->option_value',
		),
		array(
			'name' => 'desc',
			'value' => '$data->desc',
		),
		array(
			'name' => 'param',
			'value' => '$data->param',
		),
		array(
			'class' => 'CButtonColumn',
			'template' => '{update}',
			
			'htmlOptions' => array(),
			'headerHtmlOptions' => array(),
		),
		
	),
));
?>