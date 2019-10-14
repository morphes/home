<?php
$this->breadcrumbs=array(
	'Управление пользователями'=>array('userlist'),
	'Список пользователей'
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


<?php
Yii::app()->clientScript->registerScript('context', "
        $('.user-status').jeegoocontext('status-list', {
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
                                        $('#status-list').html(data);
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
	.user-status, .bottom-buttons span { border-bottom: 1px dashed blue; border-color: #555555; cursor: pointer; }
	.user-status:hover, .bottom-buttons span:hover { color: black; border-color: black; }
</style>


<?php Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggleClass('hide');

	if ( ! $('.change-reg-form').hasClass('hide'))
		$('.change-reg-form').addClass('hide');

	return false;
});
$('.change-reg-button').click(function(){
	$('.change-reg-form').toggleClass('hide');

	if ( ! $('.search-form').hasClass('hide'))
		$('.search-form').addClass('hide');
	return false;
});
");

if (Yii::app()->request->getParam('action') == 'change-reg') {
	Yii::app()->clientScript->registerScript('change-reg',"
		$('.change-reg-button').click();
	");
}
?>


<h1>Список пользователей</h1>

<div style="margin-bottom: 15px;">
	<?php echo CHtml::link('Фильтр поиска','#',array('class'=>'btn search-button')); ?>
	<?php echo CHtml::link('Смена даты регистрации','#',array('class'=>'btn change-reg-button')); ?>
</div>

<div class="search-form hide">
	<?php $this->renderPartial('_searchUser',array(
                'promocodes'	=> $promocodes,
		'model'		=> $model,
		'search_role'	=> $search_role,
		'reg_from'	=> $reg_from,
		'reg_to'	=> $reg_to,
		'service_id'	=> $service_id,
	)); ?>
</div><!-- search-form -->

<div class="change-reg-form hide">
	<?php $this->renderPartial('_changeReg', array(
		'model'      => $model,
		'reg_from_2' => $reg_from_2,
		'reg_to_2'   => $reg_to_2,
		'reg_new'    => $reg_new,
		'qtForChange'=> $qtForChange
	));?>
</div>




<?php echo CHtml::button('Новый пользователь', array('onclick'=>'document.location = \''.$this->createUrl($this->id.'/create').'\'', 'class' => 'btn primary'))?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView', array(
	'dataProvider'	=> $dataProvider,
        'id'		=> 'user-grid',
        'ajaxUrl'	=> $this->createUrl($this->id.'/'.$this->action->id),
        'updateSelector'=> '#dummy',
        'summaryText'   => "Пользователей: {count}, проектов: {$projects_qt}.",
	'itemsCssClass' => 'condensed-table',
        'selectableRows'=> 2,
	'columns'=>array(
		array(
			'class'=>'CCheckBoxColumn',
		),
		array(
			'name'=>'id',
			'value' => '$data->id'
		),
		array(            
			'name'=>'login',
			'type'=>'raw',
			'value' => 'CHtml::link(CHtml::encode($data->login), Yii::app()->createUrl("/admin/user/view/", array("id"=>$data->id)))',
		),
		array(            
			'name'=>'name',
			'type'=>'raw',
			'value' => 'CHtml::encode($data->name)',
		),
		array(           
			'name'=>'group',
			'value'=>'Config::$rolesUserReg[$data->role]',
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
			'name'=>'referrer',
			'type'=>'raw',
			'value' => '!is_null($data->referrer) ? CHtml::link($data->referrer->login, Yii::app()->createUrl("/admin/user/view/", array("id"=>$data->referrer->id))) : ""',
                ),
		array(
			'name' => 'project_quantity',
			'value' => '$data->data->project_quantity'
		),
		array(          
			'name'=> 'create_time',
			'value'=>'date("d.m.Y", $data->create_time)',
		),
		array(
			'class'=>'CButtonColumn',
			'template' => '{stat} {view} {update} {delete} {makeShopAdmin}',
			'buttons' => array(
				'stat' => array(
					'label'=>'Статистика',
					'imageUrl' => '/img/admin/small/statistics.png',
					'url'=>'Yii::app()->createUrl("/member/admin/statSpecialist/statistic", array("id"=>$data->id))',
				),
				'makeShopAdmin' => array(
					'label'    => 'Сделать пользователя «Администратором магазина»',
					'url'      => '$data->id',
					'imageUrl' => '/img/admin/small/user_male_go.png',
					'click'    => 'function(){

						if(confirm("Сделать «Администратором магазина»?")) {
							$.ajax({
								url: "/admin/user/ajaxMakeAdminStore/user_id/"+$(this).attr("href"),
								success: function(data){
									document.location.reload();
								},
								error: function(data){
									alert(data.responseText);

								},
								dataType: "json"
							});
						}

						return false;
					}',
					'visible'  => '$data->role != User::ROLE_STORES_ADMIN',
				),
			),
			'htmlOptions' => array('style' => 'width: 80px;')


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
				$('#filter-form form').submit();
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

 