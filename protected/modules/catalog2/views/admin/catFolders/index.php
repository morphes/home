<?php
$this->breadcrumbs = array(
	'Папки и миры' => array('index'),
	'Управление',
);

$this->menu = array(
	array('label' => 'Create CatFolders', 'url' => array('create')),
	array('label' => 'Manage CatFolders', 'url' => array('admin')),
);
?>

<?php
Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('folder-grid', {
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


<link href="/js/context/skins/cm_default/style.css"
      rel="Stylesheet"
      type="text/css"/>


<?php

Yii::app()->clientScript->registerScript('context', "
        $('.folder-status').jeegoocontext('status-list', {
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
                                url: '" . $this->createUrl($this->id . '/ajax_status_update') . "/id/'+$(this).attr('folder-id')+'/status/'+$(this).attr('status-id'),
                                async: false,
                                success: function(){
                                        document.location = document.location;
                                }
                        });
                },
                onShow: function(e, context){
                        $.ajax({
                                url: '" . $this->createUrl($this->id . '/ajax_status_list') . "/id/'+$(context).attr('folder-id'),
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
<ul id="status-list"
    class="jeegoocontext cm_default">

</ul>

<style type="text/css">
	.current-status {
		color: red;
		font-weight: bold;
	}

	.folder-status, .bottom-buttons span {
		border-bottom: 1px dashed blue;
		border-color: #555555;
		cursor: pointer;
	}

	.folder-status:hover, .bottom-buttons span:hover {
		color: black;
		border-color: black;
	}
</style>

<h1>Папки и миры</h1>

<?php echo CHtml::link('Фильтр', '#', array('class' => 'search-button btn')); ?>

<div class="search-form"
     style="display:none">
	<?php $this->renderPartial('_search', array(
		'model' => $model,
	)); ?>
</div><!-- search-form -->

<?php $this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'           => 'folder-grid',
	'dataProvider' => $model->search(),
	'columns'      =>
	array(
		'id',

		array(
			'name'  => 'name',
			'value' => '$data->name;',
		),


		array(
			'name'  => 'description',
			'value' => '$data->description;',
		),

		array(
			'name'  => 'userName',
			'value' => 'User::model()->findByPk($data->user_id)->login;',
		),

		array(
			'name'  => 'user_id',
			'value' => '$data->user_id;',
		),

		array(
			'name'  => 'mall',
			'value' => '$data->mall ? $data->mall->name : "Не привязан торговый центр"'
		),

		array(
			'name'               => 'status',
			'value'              => '"<span class=\"folder-status\" folder-id=\"".$data->id."\" id=\"folder-status-".$data->id."\">".CatFolders::$statusLabels[$data->status]."</span>"',
			'type'               => 'raw',
			'cssClassExpression' => '"cell-status-folder-".$data->id',
		),

		array(
			'name'  => 'count',
			'value' => '$data->count'
		),

		array(
			'class'    => 'CButtonColumn',
			'template' => '{view} {update}',
		),


	)
)); ?>



