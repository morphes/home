<?php
Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
$('.search-form').toggle();
return false;
});
$('.search-form form').submit(function(){
$.fn.yiiGridView.update('spam-grid', {
data: $(this).serialize()
});
return false;
});
");
?>


<?php
Yii::app()->clientScript->registerScript(
	'myHideEffect',
	'$(".flash-success").animate({opacity: 1.0}, 3000).fadeOut("slow");',
	CClientScript::POS_READY
);
?>

<?php Yii::app()->clientScript->registerScriptFile('/js/context/jquery.jeegoocontext.min.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/context/jquery.livequery.js'); ?>


<link href="/js/context/skins/cm_default/style.css" rel="Stylesheet" type="text/css" />


<?php
Yii::app()->clientScript->registerScript('context', "
        $('.advquestion-status').jeegoocontext('status-list', {
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
                                url: '".$this->createUrl($this->id.'/ajax_status_update')."/uid/'+$(this).attr('user-id')+'/status/'+$(this).attr('status-id'),
                                async: false,
                                success: function(){
                                        document.location = document.location;
                                }
                        });
                },
                onShow: function(e, context){
                        $.ajax({
                                url: '".$this->createUrl($this->id.'/ajax_status_list')."/uid/'+$(context).attr('user-id'),
                                async: false,
                                success: function(data){
                                        var response=$.parseJSON(data);
                                        $('#status-list').html(response.html);
                                }
                        });
                }
            });
");
?>

<!--  Context menu -->
<ul id="status-list" class="jeegoocontext cm_default">

</ul>

<style type="text/css">
	.current-status{ color: red; font-weight: bold; }
	.advquestion-status, .bottom-buttons span { border-bottom: 1px dashed blue; border-color: #555555; cursor: pointer; }
	.advquestion-status:hover, .bottom-buttons span:hover { color: black; border-color: black; }
</style>

<h1>Вопросы с раздела рекламы</h1>

<?php
$this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'advquestion-grid',
	'dataProvider'=>$dataProvider,
	'columns'=> array(
		array('name'=> 'id',
		      'value'=> '$data->id'),

		array('name'=>'author_name',
		      'value'=>'$data->author_name'),

		array('name'=>'email',
		      'value'=>'$data->email'),

		array('name'=>'question',
		      'value'=>'$data->question'),
		array('name'=>'status',
		      'value'=>'"<span class=\"advquestion-status\" user-id=\"".$data->id."\" id=\"advquestion-status-".$data->id."\">".AdvQuestion::$statusLabels[$data->status]."</span>"',
		      'type'=>'raw',
		      'cssClassExpression'=>'"cell-status-user-".$data->id',

		),

		array('name'=>'update_time',
		      'value'=>'date("H:i d.m.Y", $data->update_time)'),

		array('name'=>'update_time',
		      'value'=>'date("H:i d.m.Y", $data->update_time)'),

		array(
			'class'=>'CButtonColumn',
			'template'=>'{update}'
		),

))); ?>