<?php
// Подключаем скрипт для смены позиций элементов в списке
Yii::app()->clientScript->registerScriptFile('/js/admin/arrowsUpDown.js');

$this->breadcrumbs=array(
	'Помощь' => array('/help/admin/help/list'),
	Help::$baseNames[$section->base_path_id] => array('/help/admin/help/list', 'base'=>$section->base_path_id),
	'Разделы' => array('/help/admin/section/list', 'base'=>$section->base_path_id),
	'Статьи',
);
?>

<h2><?php echo Help::$baseNames[$section->base_path_id]; ?> / Раздел: <?php echo CHtml::encode($section->name); ?> / </h2>
<h2>Статьи</h2>

<script type="text/javascript">
	// Инициализируем стрелки перемещения позиции
	arrowsUpDown.init("article-grid", "/help/admin/article/");
</script>
<?php echo CHtml::button(
	'Добавить',
	array(
		'onclick' => 'location = "'.$this->createUrl('update', array('section_id' => $section->id)).'"',
		'class' => 'btn primary'
	)
); ?>
<?php
$this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'		=> 'article-grid',
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
			'value' => 'CHtml::link($data->name, Yii::app()->getController()->createUrl("/help/admin/chapter/list", array("article_id"=>$data->id)))',
			'sortable' => false,
		),
		array(
			'name' => 'status',
			'value' => 'HelpArticle::$statusNames[$data->status]',
			'sortable' => false,
		),
		array(
			'name' => 'Статей',
			'value' => '$data->getChapterCount()',
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
			'updateButtonUrl' => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/article/update/", array("id" => $data->id, "section_id"=>'.$section->id.'))',
			'deleteButtonUrl' => 'Yii::app()->createUrl("/'.$this->module->id.'/admin/article/delete/", array("id" => $data->id))',
		),
	),
));
?>
