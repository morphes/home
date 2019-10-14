<?php
Yii::app()->clientScript->registerScript(
   'myHideEffect',
   '$(".flash-success").animate({opacity: 1.0}, 3000).fadeOut("slow");',
   CClientScript::POS_READY
);
?>

<?php Yii::app()->clientScript->registerScript('switch-status', "
$('.switch-status').live('click', function(){
        $.ajax({
                url: '" . $this->createUrl($this->id.'/switchstatus/') . "/id/'+$(this).attr('data_id'),
                type: 'POST',
                async: false,
                success: function(){
                        $.fn.yiiGridView.update('designer-unit-grid');
                }
        });
	return false;
});
"); ?>

<style>
        .switch-status, .group-operations {
                color: #0066CC;
                text-decoration: underline;
                cursor: pointer;
        } 
</style>

<?php if(Yii::app()->user->hasFlash('design_unit_success')):?>
    <div class="flash-success">
        <?php echo Yii::app()->user->getFlash('design_unit_success'); ?>
    </div>
<?php endif; ?>

<div class="container">
        <div class="span-6 last">
                <div id="sidebar" style="padding-left: 10px; background-color: #ececec; margin-right: 10px;">

                        <?php $this->renderPartial('_sidebar', array('unit'=>$unit, 'settings'=>$settings)); ?>

                </div>
        </div>
        <div class="span-18">	
                <?php echo CHtml::button('+ Новый анонс', array('onclick'=>'document.location = \''.$this->createUrl($this->id.'/create').'\''))?>
                <?php
                $this->widget('zii.widgets.grid.CGridView', array(
                    'dataProvider' => $provider,
                    'id' => 'designer-unit-grid',
                    'htmlOptions' => array('style' => 'width:690px'),
                    'selectableRows' => 2,
                    'columns' => array(
                        array(
                                'class'=>'CCheckBoxColumn',
                        ),
                        array(
                            'name' => 'ID',
                            'type' => 'raw',
                            'value' => '$data->id',
                        ),
                        array(
                            'name' => 'ФИО',
                            'type' => 'raw',
                            'value' => '$data->name',
                        ),
                        array(
                            'name' => 'Статус',
                            'type' => 'raw',
                            'value' => '"<span data_id=\"{$data->id}\" class=\"switch-status\">".Unit::$statusLabelForDesigner[$data->status]."</span>"',
                        ),
			array(
				'name' => 'Услуга',
				'type' => 'raw',
				'value' => '($m = Service::model()->findByPk(isset($data->service_id) ? $data->service_id : 0 )) ? $m->name : ""'
			),
                        array(
                            'name' => 'Создан',
                            'type' => 'raw',
                            'value' => 'date("d.m.Y", $data->create_time)',
                        ),
                        array(
                            'name' => 'Изменен',
                            'type' => 'raw',
                            'value' => 'date("d.m.Y", $data->update_time)',
                        ),
                        array(
                            'class' => 'CButtonColumn',
                            'deleteButtonUrl' => 'Yii::app()->createUrl("/admin/unit/designer/delete", array("id"=>$data->id))',
                            'viewButtonUrl' => 'Yii::app()->createUrl("/admin/unit/designer/view", array("id"=>$data->id))',
                            'updateButtonUrl' => 'Yii::app()->createUrl("/admin/unit/designer/update", array("id"=>$data->id))',
                        ),
                    ),
                ));
                ?>

                <script type="text/javascript">
                        function update(action){
                                ids = $.fn.yiiGridView.getSelection("designer-unit-grid");
                                $.ajax({
                                        url: '<?php echo $this->createUrl($this->id.'/group_action')?>',
                                        type: 'POST',
                                        data: {action:action, ids:ids},
                                        async: false,
                                        success: function(){
                                                $.fn.yiiGridView.update('designer-unit-grid');
                                        }
                                });
                        }
                </script>
                
                <div class="group-operations ">
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

        </div>

</div>