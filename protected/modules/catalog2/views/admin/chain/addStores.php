<?php
$this->breadcrumbs=array(
        'Сети магазинов'=>array('index'),
        'Добавление магазинов',
);
?>

<h1>Добавление магазинов</h1>

<form id="chain-store-form">
        <div class="clearfix">
            <?php echo CHtml::label('Сеть магазинов', 'Chain'); ?>
            <div class="input">
                    <?php
                    $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                            'name'=>'Chain',
                            'value'=>'',
                            'sourceUrl'=>'/catalog2/admin/chain/acChain',
                            'options'=>array(
                                    'minLength'=>'1',
                                    'showAnim'=>'fold',
                                    'select'=>'js:function(event, ui) {$("#chain_id").val(ui.item.id).keyup();$("#sd").prop("disabled", false);}',
                                    'change'=>'js:function(event, ui) {if(ui.item === null) {$("#chain_id").val("");$("#sd").prop("disabled", true);}}',
                            ),
                            'htmlOptions'=>array('onkeydown'=>"if(event.keyCode==13){return false;}"),
                    ));
                    ?>
                    <?php echo CHtml::hiddenField('chain_id',  '');?>
                    <?php echo CHtml::hiddenField('stores_ids', Yii::app()->request->getParam('stores_ids')); ?>
                    <?php echo CHtml::button('Прикрепить магазины к сети', array('class'=>'btn', 'id'=>'sd','disabled'=>'disabled')); ?>
            </div>
        </div>
</form>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
        'id'=>'store-grid',
        'dataProvider'=>$dataProvider,
        'columns'=>array(
                'id',
                'city_id',
                array(
                        'type'=>'raw',
                        'value'=>'CHtml::link($data->name, Yii::app()->createUrl("/catalog2/admin/store/update/", array("id" => $data->id)));',
                ),
                array(
                        'name'=> 'create_time',
                        'value'=>'date("d.m.Y", $data->create_time)',
                ),
        ),
)); ?>


<?php Yii::app()->clientScript->registerScript('submiter', '
        $("#sd").click(function(){
                var chain_id = $("#chain_id").val();
                var chain_name = $("#Chain").val();

                if(chain_id > 0 && chain_name == "") {
                        alert("Некорректно указана сеть");
                        return false;
                }

                if(confirm("Вы действительно хотите выбранные магазины к сети " + chain_name + "?")){
                        $("#chain-store-form").submit();
                }
        })
', CClientScript::POS_READY); ?>