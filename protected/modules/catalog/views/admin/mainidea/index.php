<?php
/**
 * @var $model MainUnit
 */
$this->breadcrumbs=array(
	'Каталог товаров'=>array('/catalog/admin/catgory/index'),
	'Главная товаров, список идей',
);

/** @var $cs CClientScript */
$cs = Yii::app()->getClientScript();
$cs->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
");

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
                                url: '".$this->createUrl($this->id.'/axStatusUpdate')."',
                                data:{id:$(this).data('id'), status:$(this).attr('status-id')},
                                async: false,
                                success: function(){ window.location.reload();},
                                error:function(){window.location.reload();}
                        });
                },
                onShow: function(e, context){
                        $.ajax({
                                url: '".$this->createUrl($this->id.'/axStatusList')."',
                                data:{id:$(context).data('id')},
                                async: false,
                                dataType:'json',
                                success: function(data){ $('#status-list').html(data.html);},
                                error:function(){window.location.reload();}
                        });
                }
            });
", CClientScript::POS_READY);

// Подключаем скрипт для смены позиций элементов в списке
Yii::app()->getClientScript()->registerScriptFile('/js/admin/arrowsUpDown.js'); ?>

<script type="text/javascript">
	// Инициализируем стрелки перемещения позиции
	arrowsUpDown.init("main-idea-grid", "/catalog/admin/mainidea/");
</script>

<h1>Главная товаров, список идей</h1>
<!--  Context menu -->
<ul id="status-list" class="jeegoocontext cm_default"></ul>
<style type="text/css">
	.current-status{ color: red; font-weight: bold; }
	.item-status, .bottom-buttons span { border-bottom: 1px dashed blue; border-color: #555555; cursor: pointer; }
	.item-status:hover, .bottom-buttons span:hover { color: black; border-color: black; }
</style>

<?php echo CHtml::link('Расширенный поиск','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<div>
	<?php echo CHtml::button('Добавить предложение', array('class'=>'primary btn','style'=>'float:right; margin-left: 10px;', 'onclick'=>'document.location = \''.$this->createUrl('/catalog/admin/mainidea/create/').'\''))?>
</div>

<?php
$dataProvider = $model->search();
$criteria = $dataProvider->getCriteria();
$criteria->compare('type_id', MainUnit::TYPE_IDEA);
$dataProvider->setCriteria($criteria);


$this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'main-idea-grid',
	'dataProvider'=>$dataProvider,
	'updateSelector'=> '#dummy',
	'selectionChanged' => 'js:function(){
		arrowsUpDown.showArrows();
		arrowsUpDown.moveToCursor();
	}',
	'afterAjaxUpdate' => 'js:function(){
		arrowsUpDown.selectLastElement();
	}',
	'columns'=>array(
		array(
			'name' => 'id',
			'value' => '$data->id',
			'htmlOptions' => array("class" => 'elementId')
		),
		array(
			'name'=>'status',
			'value'=>'"<span class=\"item-status\" data-id=\"".$data->id."\" id=\"item-status-".$data->id."\">".MainUnit::$statusNames[$data->status]."</span>"',
			'type'=>'raw',
			'cssClassExpression'=>'"cell-status-user-".$data->id',
		),
		array(
			'name'=>'name',
			'type'=>'raw',
			'value'=>'$data->name;',
		),
		array(
			'name'=>'create_time',
			'type'=>'raw',
			'value'=>'date("d.m.Y H:i", $data->create_time);',
			'sortable'=>false,
		),
		array(
			'name'=>'update_time',
			'type'=>'raw',
			'value'=>'date("d.m.Y H:i", $data->update_time);',
			'sortable'=>false,
		),
		array(
			'class'=>'CButtonColumn',
			'template'=>'{update} {delete}',
		),
	),
)); ?>
