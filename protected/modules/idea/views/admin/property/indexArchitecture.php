<?php
$this->breadcrumbs=array(
	'Идеи'=>array('/idea/admin/interior/list'),
	'Свойства «'.Config::$ideaTypesName[$idea_type_id].'»',
);
?>

<h1>Свойства «<?php echo Config::$ideaTypesName[$idea_type_id];?>»</h1>


<div class="row">
	<form action="/<?php echo $this->getRoute();?>" class="form-stacked" name="form_type">

		<label>Тип идеи:</label>
		<?php echo CHtml::dropDownList('idea_type_id', $idea_type_id, Config::$ideaTypesName, array('class' => 'idea_type', 'onchange' => "document.forms['form_type'].submit();"));?>
	</form>
</div>

<?php echo CHtml::link('Добавить свойство', $this->createUrl($this->id.'/createmain/', array('idea_type_id' => (!is_null($idea_type_id) ? $idea_type_id : 0) )), array('class' => 'primary btn')); ?>


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
			'name' => 'Ключ',
			'value' => '$data->option_key',
		),
		array(
			'name' => 'Название',
			'value' => '$data->option_value',
			'type' => 'html'
		),
		array(
			'name' => 'Тип объекта',
			'value' => '"<a href=\"/'.$this->module->id.'/'.$this->id.'/prop/id/".$data->id."/key/building_type\">Тип объекта</a>: ".$data->building_type_cnt',
			'type' => 'raw'
		),
		array(
			'name' => 'Архитектурный стиль',
			'value' => '($data->style_cnt) ? "<a href=\"/'.$this->module->id.'/'.$this->id.'/prop/id/".$data->id."/key/style\">Архитектурный стиль</a>: ".$data->style_cnt : \'\'',
			'type' => 'raw'
		),
		array(
			'name' => 'Материал',
			'value' => '($data->material_cnt > 0) ? "<a href=\"/'.$this->module->id.'/'.$this->id.'/prop/id/".$data->id."/key/material\">Материал</a>: ".$data->material_cnt : \'\'',
			'type' => 'raw'
		),
		array(
			'name' => 'Этажность',
			'value' => '($data->floor_cnt > 0) ? "<a href=\"/'.$this->module->id.'/'.$this->id.'/prop/id/".$data->id."/key/floor\">Этажность</a>: ".$data->floor_cnt : \'\'',
			'type' => 'raw'
		),
		array(
			'name' => 'Цвет',
			'value' => '($data->color_cnt > 0) ? "<a href=\"/'.$this->module->id.'/'.$this->id.'/prop/id/".$data->id."/key/color\">Цвет</a>: ".$data->color_cnt : \'\'',
			'type' => 'raw'
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