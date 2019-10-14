<?php
	$cs = Yii::app()->clientScript;
	$cs->registerCoreScript('jquery');
	//$cs->registerScriptFile('/js/jquery-ui-1.8.18.custom.min.js');
	//$cs->registerScriptFile('/js/admin.js', CClientScript::POS_HEAD);
	//$cs->registerCssFile('/css/jquery-ui-1.8.18.custom.css');
?>

<?php
$this->breadcrumbs=array(
	'Заказы'=>array('/tenders/admin/tender/list'),
	'Список откликов'=>array('/tenders/admin/response/list'),
        'Редактирование отклика',
);
?>

<h1>Редактирование отклика #<?php echo $response->id; ?>

<?php
$this->widget('ext.bootstrap.widgets.BootDetailView', array(
    'data'=>$response,
    'attributes'=>array(
        array(
            'label'=>'Заказ',
            'type'=>'html',
            'value'=>"<b>".$response->tender->name."</b>",
        ),
        array(
            'label'=>'Автор',
            'type'=>'html',
            'value'=>CHtml::link(CHtml::encode($user->login), Yii::app()->createUrl("/admin/user/view/", array("id"=>$user->id))),
        ),
	array(
	    'label'=>'Стоимость',
	    'type'=>'html',
	    'value'=>$response->cost,
	),
        array(
            'label'=>'Добавлен',
            'type'=>'raw',
            'value'=>date('d.m.Y H:i', $response->create_time),
        ),
    ),
));
?>


<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'tender-response-form',
	'enableAjaxValidation'=>false,
        'htmlOptions' => array('class' => 'form-project-add'),
        'stacked'=>true,
)); ?>

        <?php echo $form->errorSummary($response); ?>

                <div class="well" style="background-color: #F9F9F9;">
                        <?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Общие настройки', true); ?>
			
                        <?php echo $form->textAreaRow($response, 'content', array('class'=>'span12', 'style'=>'min-height:150px;')); ?>

                </div>

	<?php echo CHtml::tag('hr', array('style' => 'height: 2px; background-color: black;')); ?>
	
        <div class="actions">
                <?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary large')); ?>
                <?php echo CHtml::button('Отмена', array('class'=>'btn default large','onclick' => "document.location = '".$this->createUrl('/tenders/admin/response/view', array('id' => $response->id))."'"));?>
        </div>

<?php $this->endWidget(); ?>