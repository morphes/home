<?php
// Подключаем скрипт для смены позиций элементов в списке
Yii::app()->clientScript->registerScriptFile('/js/admin/arrowsUpDown.js');

$this->breadcrumbs=array(
	'Помощь' => array('/help/admin/help/list'),
	Help::$baseNames[$baseId] => array('/help/admin/help/list', 'base'=>$baseId),
	'Популярные вопросы'

);
?>
<h1>Популярные вопросы</h1>

<script type="text/javascript">
	// Инициализируем стрелки перемещения позиции
	arrowsUpDown.init("faq-grid", "/help/admin/faq/");
</script>
<?php echo CHtml::button(
	'Добавить',
	array(
		'onclick' => 'location = "'.$this->createUrl('update', array('base' => $baseId)).'"',
		'class' => 'btn primary'
	)
); ?>
<?php
$this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'		=> 'faq-grid',
        'updateSelector'=> '#dummy', // Устанавливаем пустым, чтобы без ajax'а была постраничка
	'dataProvider'	=> $dataProvider,
	'selectableRows'=> 2, // Multiple selection
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
			'sortable' => false,
			'htmlOptions' => array("class" => 'elementId')
		),
		array(
			'name' => 'status',
			'value' => 'HelpFaq::$statusNames[$data->status]',
			'sortable' => false,
		),
		array(
			'name' => 'question',
			'value' => 'CHtml::encode($data->question)',
			'sortable' => false,
		),
		array(
			'name'	=> 'Добавлен',
			'value' => 'date("d.m.Y", $data->create_time)',
			'sortable' => false
		),
		array(
			'name'	=> 'Обновлен',
			'value' => 'date("d.m.Y", $data->update_time)',
			'sortable' => false
		),
		array(
			'class' => 'CButtonColumn',
			'template' => '{update} {delete}',
			'updateButtonUrl' => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/faq/update/", array("id" => $data->id, "base"=>'.$baseId.'))',
			'deleteButtonUrl' => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/faq/delete/", array("id" => $data->id))',
		),
	),
));
?>
