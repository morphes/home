<?php
// Подключаем скрипт для смены позиций элементов в списке
Yii::app()->clientScript->registerScriptFile('/js/admin/arrowsUpDown.js');

$this->breadcrumbs=array(
	'Помощь' => array('/help/admin/help/list'),
	Help::$baseNames[$baseId] => array('/help/admin/help/list', 'base'=>$baseId),
	'Разделы'

);
?>
<h2><?php echo Help::$baseNames[$baseId]; ?> / </h2>
<h2>Разделы</h2>

<script type="text/javascript">
	// Инициализируем стрелки перемещения позиции
	arrowsUpDown.init("section-grid", "/help/admin/section/");
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
	'id'		=> 'section-grid',
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
			'name'=>'name',
			'type'=>'raw',
			'value' => 'CHtml::link($data->name, Yii::app()->getController()->createUrl("/help/admin/article/list", array("section_id"=>$data->id)))',
			'sortable' => false,
		),
		/*array(
			'name'=>'author',
			'type'=>'raw',
			'value' => 'CHtml::link(CHtml::encode($data->user->login), Yii::app()->createUrl("/admin/user/view/", array("id"=>$data->author_id)))',
		),*/
		array(
			'name' => 'status',
			'value' => 'HelpSection::$statusNames[$data->status]',
			'sortable' => false,
		),
		array(
			'name' => 'Статей',
			'value' => '$data->getArticleCount()',
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
			'updateButtonUrl' => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/section/update/", array("id" => $data->id, "base"=>'.$baseId.'))',
			'deleteButtonUrl' => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/section/delete/", array("id" => $data->id))',
		),
	),
));
?>
