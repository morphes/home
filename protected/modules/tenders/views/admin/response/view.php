<?php 
	$cs = Yii::app()->clientScript;
	$cs->registerCoreScript('jquery');
?>

<?php
$this->breadcrumbs=array(
	'Заказы'=>array('/tenders/admin/tender/list'),
	'Список откликов'=>array('/tenders/admin/response/list'),
        'Просмотр отклика',
);
?>

<h1>Отклик #<?php echo $response->id;?> </h1>

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
	array(
	    'label'=>'Описание',
	    'type'=>'html',
	    'value'=>$response->content,
	),
    ),
));
?>

<div class="actions">

        <?php echo CHtml::button('Редактировать', array('class'=>'primary btn',
                'onclick' => "document.location='{$this->createUrl('/tenders/admin/response/update', array('id' => $response->id))}'"
        )); ?>
</div>
