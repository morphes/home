<?php
$this->breadcrumbs=array(
	'Лента логотипов'=>array('index'),
	'Список',
);



Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('tapestore-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

/** @var $cs CustomClientScript */
$cs = Yii::app()->getClientScript();

$cs->registerScriptFile('/js/context/jquery.jeegoocontext.min.js');
$cs->registerScriptFile('/js/context/jquery.livequery.js');
$cs->registerCssFile('/js/context/skins/cm_default/style.css');

$cs->registerScript('context', "
        $('.item-status').jeegoocontext('status-list', {
                livequery: true,
		startLeftOffset: -187,
		startTopOffset: -85,
                widthOverflowOffset: 0,
                heightOverflowOffset: 1,
                submenuLeftOffset: -4,
                submenuTopOffset: -2,
                event: 'click',
                openBelowContext: false,
                onSelect: function(e, context){
			$.ajax({
                                url: '".$this->createUrl($this->id.'/ajaxStatusUpdate')."',
                                data:{id:$(this).data('id'), status:$(this).data('status')},
                                async: false,
                                success: function(){ window.location.reload();},
                                error:function(){/*window.location.reload();*/}
                        });
                },
                onShow: function(e, context){
                        $.ajax({
                                url: '".$this->createUrl($this->id.'/ajaxStatusList')."',
                                data:{id:$(context).data('id')},
                                async: false,
                                dataType:'json',
                                success: function(data){ $('#status-list').html(data.html);},
                                error:function(){window.location.reload();}
                        });
                }
            });
", CClientScript::POS_READY);

$cs->registerCss('context', '
	.item_status:hover {
		cursor: pointer;
	}
');
// Подключаем скрипт для смены позиций элементов в списке
$cs->registerScriptFile('/js/admin/arrowsUpDown.js');
?>
<script type="text/javascript">
	// Инициализируем стрелки перемещения позиции
	arrowsUpDown.init("tapestore-grid", "/catalog/admin/tapestore/");
</script>

<h1>Лента брендов</h1>

<!--  Context menu -->
<ul id="status-list" class="jeegoocontext cm_default"></ul>
<style type="text/css">
	.current-status{ color: red; font-weight: bold; }
	.item-status, .bottom-buttons span { border-bottom: 1px dashed blue; border-color: #555555; cursor: pointer; }
	.item-status:hover, .bottom-buttons span:hover { color: black; border-color: black; }
</style>

<?php echo CHtml::link('Фильтр','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<div style="margin-bottom: 10px;"></div>
<?php echo CHtml::link('Добавить', array('/catalog/admin/tapestore/create'), array('class' => 'primary btn')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'           => 'tapestore-grid',
	'dataProvider' => $model->search(),
	//'updateSelector'=> '#dummy',
	'selectionChanged' => 'js:function(){
		arrowsUpDown.showArrows();
		arrowsUpDown.moveToCursor();
	}',
	'afterAjaxUpdate' => 'js:function(){
		arrowsUpDown.selectLastElement();
	}',
	'columns'      => array(
		array(
			'name' => 'id',
			'value' => '$data->id',
			'htmlOptions' => array("class" => 'elementId')
		),
		array(
			'name' => 'image_id',
			'type' => 'raw',
			'value' => 'CHtml::image("/" . $data->getImageByConfig(Tapestore::$preview["resize_90"]), "", array("max-height"=>90, "max-width"=>90))',
		),
		array(
			'name' => 'status',
			'type' => 'raw',
			'value' => '$data->getStatusHtml()',
		),
		array(
			'name' => 'store_id',
			'type' => 'raw',
			'value' => '$data->getStore()->name',
		),
		array(
			'name' => 'start_time',
			'value' => 'date("d.m.Y H:i", $data->start_time)',
		),
		array(
			'name' => 'end_time',
			'value' => 'date("d.m.Y H:i", $data->end_time)',
		),
		array(
			'class' => 'CButtonColumn',
		),
	),
)); ?>
