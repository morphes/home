<?php Yii::app()->clientScript->registerScript('search', "
$('.filter-form form').submit(function(){
	$.fn.yiiGridView.update('user-grid', {
		data: $(this).serialize()
	});
	return false;
});
");?>

<link href="/js/context/skins/cm_default/style.css" rel="Stylesheet" type="text/css" />

    <!--  Context menu -->
    <ul id="status-list" class="jeegoocontext cm_default">

    </ul>    


<style type="text/css">
.current-status{
        color: red;
        font-weight: bold;
}
.user-status, .user-type, .bottom-buttons span {
        border-bottom: 1px dashed blue;
        cursor: pointer;
        border-color: #555555;
}
</style>

<div class="container">
        <div class="span-6 last">
		<div id="sidebar" style="padding-left: 10px; background-color: #ececec; margin-right: 10px;">
                        
                        <h4 style="color: slategray;">Фильтр</h4>
                        
                        <div class="filter-form">
                        <?php echo CHtml::beginForm('', 'get'); ?>
                        
                                <div class="row">
                                        <?php echo CHtml::label('Модератор', 'user_id'); ?>
                                        <?php echo CHtml::dropDownList('user_id', $userId, array('0'=>'Все')+CHtml::listData($moderators, 'id', 'login')); ?>
                                </div>
				
				<div class="row">
                                        <br />
                                        <?php echo CHtml::label('Раздел', 'part')?><br />
                                        <?php echo CHtml::dropDownList('part', '0', array('0'=>'Все')+ModeratorLog::$classNames); ?>
                                </div>

                                <div class="row">
                                        <br />
                                        <?php echo CHtml::label('Операции', 'operation')?><br />
                                        <?php echo CHtml::dropDownList('operation', '0', array('0'=>'Все')+ModeratorLog::$operationNames); ?>
                                </div>

                                <div class="row">
                                        <br />
                                        <?php echo CHtml::label('Период работы', 'time')?><br />

                                        <?php echo CHtml::label('с &nbsp', 'time_from')?>
                                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                            'name'=>'time_from',
                                            'options'=>array('dateFormat'=>'dd.mm.yy'),
                                            'htmlOptions'=>array(
                                                'style'=>'width:150px;'
                                            ),
                                        ));?>
                                        <br />
                                        <?php echo CHtml::label('по', 'time_to')?>
                                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                            'name'=>'time_to',
                                            'options'=>array('dateFormat'=>'dd.mm.yy'),
                                            'htmlOptions'=>array(
                                                'style'=>'width:150px;'
                                            ),
                                        ));?>
                                </div>

                                <div class="row">
                                        <br />
                                        <?php echo CHtml::submitButton('Показать')?>
                                </div>
                        
                        <?php echo CHtml::endForm();?>
                        </div>
                </div>
	</div>
	<div id="table" class="span-18">	
                <div>Всего записей: <?php echo $dataProvider->getTotalItemCount(); ?></div>
		<?php $this->widget('zii.widgets.grid.CGridView', array(
                    'dataProvider'=>$dataProvider,
		    'ajaxUpdate' => 'table',
                    'id'=>'user-grid',
                    'ajaxUrl'=>$this->createUrl($this->id.'/'.$this->action->id),
                    'htmlOptions'=>array('style'=>'width:690px'),
                    'selectableRows'=>2,
                    'columns'=>array(
                        'id',   
                        array(            
                                'name'=>'user_id',
                                'type'=>'raw',
                                'value' => '$data->getUserName()',
                        ),
                        array(           
                                'name'=>'class_id',
                                'value'=>'ModeratorLog::$classNames[$data->class_id]',
                        ),
                        array(            
                                'name'=>'record_id',
                                'type'=>'raw',
                                'value' => '$data->getItemUrl()',
                        ),
			array(            
                                'name'=>'crud_id',
				'type'=>'raw',
                                'value'=>'ModeratorLog::$operationNames[$data->crud_id]',
                        ),
                        array(          
                                'name'=>'create_time',
                                'value'=>'date("d.m.Y H-m-s", $data->create_time)',
                        ),
                    ),
                ));?>
                
	</div>
 
</div>