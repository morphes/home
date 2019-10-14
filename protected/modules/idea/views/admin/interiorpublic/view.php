<?php
	$cs = Yii::app()->clientScript;
	$cs->registerCoreScript('jquery');
	$cs->registerScriptFile('/js/simple.lightbox.admin.js', CClientScript::POS_HEAD);
?>

<?php
$this->breadcrumbs=array(
	'Идеи'=>array('list'),
	'Общественные интерьеры'=>array('list'),
        'Просмотр интерьера',
);
?>
<?php Yii::app()->clientScript->registerScript('journal', "
$('.journal-button').click(function(){
	$('.journal').toggle();
	return false;
});
");?>

<h1>Общественный интерьер #<?php echo $model->id;?> - "<?php echo $model->name;?>"</h1>

<?php //$this->rightbar = $this->renderPartial('application.modules.idea.views.admin.create._journal', array('journal'=>$journal, 'interior'=>$model), true); ?>

<?php
$this->widget('ext.bootstrap.widgets.BootDetailView', array(
    'data'=>$model,
    'attributes'=>array(
        array(
            'label'=>'Название',
            'type'=>'html',
            'value'=>"<b>".$model->name."</b>",
        ),
        array(
            'label'=>'Автор',
            'type'=>'html',
            'value'=>CHtml::link($model->author->login.' ('.$model->author->name.')', $this->createUrl('/member/profile/user/', array('id' => $model->author->id))),
        ),
        array(
            'label'=>'Дата создания',
            'type'=>'raw',
            'value'=>date('d.m.Y H:i', $model->create_time),
        ),
        array(
            'label'=>'Дата обновления',
            'type'=>'raw',
            'value'=>date('d.m.Y H:i', $model->update_time),
        ),
        array(
            'label'=>'Тип объекта',
            'type'=>'raw',
            'value'=>$object.' / '.$buildingType,
        ),
        'desc',
        array(
            'label'=>'Статус',
            'type'=>'html',
            'value'=>"<span class='label success'>".Interiorpublic::getStatusName($model->status)."</span>",
        ),
        array(
            'label'=>'Соавторы',
            'type'=>'html',
            'value'=>Coauthor::coauthorFormatter($coauthors),
        ),
        array(
            'label'=>'Обложка интерьера',
            'type'=>'raw',
            'value'=>Interior::imageFormater($model->image),
        ),
    ),
));
?>




<div class="actions">

        <?php echo CHtml::button('Редактировать', array('class'=>'primary btn',
                'onclick' => "document.location='{$this->createUrl('admin/interiorpublic/update', array('id' => $model->id))}'"
        )); ?>
        <?php echo CHtml::button('Удалить', array('class'=>'danger btn','onclick' => 'if (!confirm("Удалить?")) { return false; } else { document.location="/idea/admin/interiorpublic/delete/id/'.$model->id.'" }')); ?>
</div>
