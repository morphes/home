<?php
Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
    $('.search-form').toggle();
    return false;
});
$('.search-form form').submit(function(){
    $.fn.yiiGridView.update('log-user-activation-grid', {
        data: $(this).serialize()
    });
    return false;
});
");
?>

<h1>Журнал активации пользователей</h1>


<?php echo CHtml::link('Фильтр','#',array('class'=>'search-button btn')); ?>
<div class="search-form" style="display:none">
        <?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
            'action'=>Yii::app()->createUrl($this->route),
            'method'=>'get',
        )); ?>

            <?php echo $form->dropDownListRow($model,'referrer_id', array(''=>'Все', '-1'=>'Нет')+CHtml::listData(User::getUsersByRoles(array(User::ROLE_SALEMANAGER, User::ROLE_MODERATOR)), 'id', 'login'),array('class'=>'span5')); ?>

                <div class="clearfix">
                        <?php echo CHtml::label('Активация от', 'reg')?>

                        <div class="input">
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                'name'=>'activate_from',
                                'value'=> $activate_from,
                                'options'=>array('dateFormat'=>'dd.mm.yy'),
                                'htmlOptions'=>array(
                                'style'=>'width:150px;'
                                ),
                        ));?>
                        </div>
                </div>

                <div class="cliearfix">
                        <?php echo CHtml::label('Активация до', 'reg')?>

                        <div class="input">
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                'name'=>'activate_to',
                                'value' => $activate_to,
                                'options'=>array('dateFormat'=>'dd.mm.yy'),
                                'htmlOptions'=>array(
                                'style'=>'width:150px;'
                                ),
                        ));?>
                        </div>
                </div>

            <div class="actions">
                <?php echo CHtml::submitButton('Фильтровать',array('class'=>'btn primary')); ?>
            </div>

        <?php $this->endWidget(); ?>
</div><!-- search-form -->

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
    'id'=>'log-user-activation-grid',
    'dataProvider'=>$dataProvider,
    'columns'=>array(
        'id',
        array(
            'type'=>'raw',
            'name'=>$model->getAttributeLabel('user_id'),
            'value' => 'CHtml::link(CHtml::encode($data->user->login), Yii::app()->createUrl("/admin/user/view/", array("id"=>$data->user_id)))',
        ),
	array(
	     'type'=>'raw',
             'name'=>$model->getAttributeLabel('role'),
             'value' => 'User::$roleNames[$data->user->role]',
	    ),
        array(
            'type'=>'raw',
            'name'=>$model->getAttributeLabel('referrer_id'),
            'value'=>'"<strong>" . (empty($data->referrer) ? "Нет" : $data->referrer->login) . "</strong>"'
        ),
        array(
            'type'=>'raw',
            'name'=>$model->getAttributeLabel('create_time'),
            'value'=>'date("d.m.Y H:i", $data->create_time)'
        ),
        array(
            'type'=>'raw',
            'name'=>$model->getAttributeLabel('activate_time'),
            'value'=>'date("d.m.Y H:i", $data->activate_time)'
        ),
    ),
)); ?> 