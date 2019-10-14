<?php
$this->breadcrumbs=array(
	'Лента брендов'=>array('index'),
	'Список',
);



Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('index-product-brand-grid', {
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
        $('.item_status').jeegoocontext('status-list', {
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
                                data: {
                                	id: $(this).data('id'),
                                	status: $(this).data('status_id')
                                },
                                async: false,
                                success: function(){
                                	window.location.reload();
                                },
                                error:function(){
                                	//window.location.reload();
                                }
                        });
                },
                onShow: function(e, context){
                        $.ajax({
                                url: '".$this->createUrl($this->id.'/ajaxStatusList')."',
                                data: { id:$(context).data('id') },
                                async: false,
                                dataType:'json',
                                success: function(data){
                                	$('#status-list').html(data.html);
                                },
                                error:function(){
                                	//window.location.reload();
                                }
                        });
                }
            });
", CClientScript::POS_READY);

$cs->registerCss('context', '
	.item_status:hover {
		cursor: pointer;
	}
');

?>

<h1>Лента брендов</h1>

<!--  Context menu -->
<ul id="status-list" class="jeegoocontext cm_default"></ul>


<?php echo CHtml::link('Фильтр','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<div style="margin-bottom: 10px;"></div>
<?php echo CHtml::link('Добавить', array('/admin/indexProductBrand/create'), array('class' => 'primary btn')); ?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'           => 'index-product-brand-grid',
	'dataProvider' => $model->search(),
	'columns'      => array(
		'id',
		array(
			'name' => 'type',
			'type' => 'raw',
			'value' => '(isset(IndexProductBrand::$typeName[$data->type])) ? IndexProductBrand::$typeName[$data->type] : "N/A"',
		),
		array(
			'name' => 'image_id',
			'type' => 'raw',
			'value' => 'CHtml::image("/" . $data->uploadedFile->getPreviewName(IndexProductBrand::$preview["resize_90"]))'
		),
		array(
			'name' => 'status',
			'type' => 'raw',
			'value' => '$data->getStatusHtml()'
		),
		'name',
		array(
			'name' => 'update_time',
			'value' => 'date("d.m.Y H:i", $data->update_time)',
		),
		array(
			'class' => 'CButtonColumn',
		),
	),
)); ?>
