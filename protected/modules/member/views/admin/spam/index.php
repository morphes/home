<?php
$this->breadcrumbs=array(
	'Spams'=>array('index'),
	'Manage',
);

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
        $('.spam-status').jeegoocontext('status-list', {
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
	.spam-status, .bottom-buttons span { border-bottom: 1px dashed blue; border-color: #555555; cursor: pointer; }
	.spam-status:hover, .bottom-buttons span:hover { color: black; border-color: black; }
</style>


<h1>Пометки о спаме</h1>

<?php echo CHtml::link('Фильтр','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php

switch ($messageFilterFlag)
{
	case 'allMessage':
		$columns = array(
			array(
				'name'=>'authorName',
				'value'=>'User::model()->findByPk($data->author_id)->login;',
			),
			array(
				'name'=>'message',
				'value'=>'Amputate::getLimb($data->message, 100)',
			),

			array(
				'name'=>'create_time',
				'value'=>'date("H:i d.m.Y", $data->create_time)',
			),
			array(
				'class'=>'CButtonColumn',
				'template'=>'{view}',
			),

		);


		break;
	case 'countMessage':
		$columns = array(
			array(
				'name'=>'authorName',
				'value'=>'User::model()->findByPk($data->author_id)->login;',
			),
			array(
				'name' => 'Количество',
				'value' => '$data->num',
			),

		);
		break;
	case 'searchString':
		$columns = array(
			array(
				'name'=>'authorName',
				'value'=>'User::model()->findByPk($data->author_id)->login;',
			),
			array(
				'name'=>'message',
				'value'=>'Amputate::getLimb($data->message, 100)',
			),
			array(
				'name'=>'create_time',
				'value'=>'date("H:i d.m.Y", $data->create_time)',
			),
			array(
				'class'=>'CButtonColumn',
				'template'=>'{view}',
			),
		);
		break;
	default:
		$columns = array(
			'id',
			array(
				'name'=>'recipientName',
				'value'=>'User::model()->findByPk($data->recipient_id)->login;',
			),
			array(
				'name'=>'authorName',
				'value'=>'User::model()->findByPk($data->author_id)->login;',
			),
			array(
				'name'=>'message',
				'value'=>'Amputate::getLimb($data->message, 30)',
			),
			array(
				'name'=>'status',
				'value'=>'"<span class=\"spam-status\" user-id=\"".$data->id."\" id=\"spam-status-".$data->id."\">".Spam::$statusLabels[$data->status]."</span>"',
				'type'=>'raw',
				'cssClassExpression'=>'"cell-status-user-".$data->id',
			),
			array(
				'name'=>'create_time',
				'value'=>'date("H:i d.m.Y", $data->create_time)',
			),
			array(
				'class'=>'CButtonColumn',
				'template'=>'{update} {delete}'
			),
		);
		break;

}



 $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'spam-grid',
	'dataProvider'=>$model->search(),
	'columns'=> $columns
)); ?>
