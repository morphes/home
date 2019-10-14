<?php
$this->breadcrumbs=array(
	'Управление пользователями'=>array('userlist'),
	'Список администраторов'
);
?>

<?php
Yii::app()->clientScript->registerScript(
   'myHideEffect',
   '$(".flash-success").animate({opacity: 1.0}, 3000).fadeOut("slow");',
   CClientScript::POS_READY
);
?>


<?php if(Yii::app()->user->hasFlash('user-create-success')):?>
    <div class="flash-success">
        <?php echo Yii::app()->user->getFlash('user-create-success'); ?>
    </div>
<?php endif; ?>

<?php Yii::app()->clientScript->registerScriptFile('/js/context/jquery.jeegoocontext.min.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/context/jquery.livequery.js'); ?>

<link href="/js/context/skins/cm_default/style.css" rel="Stylesheet" type="text/css" />

<?php Yii::app()->clientScript->registerScript('context', "
        $('.user-status').jeegoocontext('status-list', {
                livequery: true,
		startLeftOffset: -215,
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
                                        $('#status-list').html(data);
                                }
                        });
                }

                
            });
");?>

<!--  Context menu -->
<ul id="status-list" class="jeegoocontext cm_default">

</ul>    


<style type="text/css">
	.current-status{ color: red; font-weight: bold; }
	.user-status, .bottom-buttons span { border-bottom: 1px dashed blue; border-color: #555555; cursor: pointer; }
	.user-status:hover, .bottom-buttons span:hover { color: black; border-color: black; }
</style>



<?php Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
");?>

<h1>Список администраторов</h1>

<div style="margin-bottom: 15px;"><?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?></div>

<div class="search-form" style="display:none">
	<?php $this->renderPartial('_searchAdmin',array(
                
	)); ?>
</div><!-- search-form -->


        
<?php echo CHtml::button('Новый пользователь', array('onclick'=>'document.location = \''.$this->createUrl($this->id.'/create').'\'', 'class' => 'btn primary'))?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView', array(
	'dataProvider'	=> $dataProvider,
	'id'		=> 'user-grid',
        'updateSelector'=> '#dummy',
	'ajaxUrl'	=> $this->createUrl($this->id.'/'.$this->action->id),
	'itemsCssClass' => 'condensed-table',
	'selectableRows'=> 2,
	'columns'=>array(
	array(
		'class'=>'CCheckBoxColumn',
	),
	'id',   
	array(            
		'name'=>'login',
		'type'=>'raw',
		'value' => 'CHtml::link(CHtml::encode($data->login), "#")',
	),
	array(           
		'name'=>'group',
		'value'=>'Config::$rolesAdmin[$data->role]',
	),
	array(      
		'name'=>'status',
		'value'=>'"<span class=\"user-status\" user-id=\"".$data->id."\" id=\"user-status-".$data->id."\">".Config::$userStatus[$data->status]."</span>"',
		'type'=>'raw',
		'cssClassExpression'=>'"cell-status-user-".$data->id',
	),
	array(            
		'name'=>'email',
		'type'=>'raw',
		'value' => 'CHtml::link(CHtml::encode($data->email), "mailto:".CHtml::encode($data->email))',
	),
	array(          
		'name'=>'create_time',
		'value'=>'date("d.m.Y", $data->create_time)',
	),
	array(            
		'name'=>'update_time',
		'value'=>'date("d.m.Y", $data->update_time)',
	),
	array(
		'class'=>'CButtonColumn',
	),
	),
));?>



<script type="text/javascript">
	function update(action){
		users = $.fn.yiiGridView.getSelection("user-grid");
		$.ajax({
			url: '<?php echo $this->createUrl($this->id.'/group_action')?>/action/'+action+'/users/'+users,
			type: 'POST',
			async: false,
			success: function(){
				$('#filter-form').find('form').submit();
			}
		});
	}
</script>

<div class="bottom-buttons">
	<?php echo CHtml::openTag('span', array('onclick'=>'update("disable");'))?>
	Отключить отмеченные
	<?php echo CHtml::closeTag('span')?>

	<?php echo CHtml::tag('br')?>

	<?php echo CHtml::openTag('span', array('onclick'=>'update("enable");'))?>
	Включить отмеченные
	<?php echo CHtml::closeTag('span')?>

	<?php echo CHtml::tag('br')?>

	<?php echo CHtml::openTag('span', array('onclick'=>'update("delete");'))?>
	Удалить отмеченные
	<?php echo CHtml::closeTag('span')?>
</div>

