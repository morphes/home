<?php 
	$cs = Yii::app()->clientScript;
	$cs->registerCoreScript('jquery');
	$cs->registerScriptFile('/js/simple.lightbox.admin.js', CClientScript::POS_HEAD);
?>

<?php
$this->breadcrumbs=array(
	'Идеи'=>array('list'),
	'Интерьеры'=>array('list'),
        'Просмотр интерьера',
);
?>
<?php Yii::app()->clientScript->registerScript('journal', "
$('.journal-button').click(function(){
	$('.journal').toggle();
	return false;
});
");?>

<?php
// ----- Подключение скрипта для связки товаров с фотками -----

Yii::app()->clientScript->registerScriptFile('/js/admin/CBindProducts.js');
Yii::app()->clientScript->registerScript('journal', "
	bindProducts.initPopup('Interior', '{$interior->id}');
", CClientScript::POS_READY);
?>


<h1>Интерьер #<?php echo $interior->id;?> - "<?php echo $interior->name;?>"</h1>

<?php $this->rightbar = $this->renderPartial('application.modules.idea.views.admin.create._journal', array('journal'=>$journal, 'interior'=>$interior), true); ?>

<?php
$this->widget('ext.bootstrap.widgets.BootDetailView', array(
    'data'=>$interior,
    'attributes'=>array(
        array(
            'label'=>'Название',
            'type'=>'html',
            'value'=>"<b>".$interior->name."</b>",
        ),
        array(
            'label'=>'Автор',
            'type'=>'html',
            'value'=>CHtml::link($interior->author->login.' ('.$interior->author->name.')', $this->createUrl('/member/profile/user/', array('id' => $interior->author->id))),
        ),
        array(
            'label'=>'Дата создания',
            'type'=>'raw',
            'value'=>date('d.m.Y H:i', $interior->create_time),
        ),
        array(
            'label'=>'Дата обновления',
            'type'=>'raw',
            'value'=>date('d.m.Y H:i', $interior->update_time),
        ),
        array(
            'label'=>'Тип объекта',
            'type'=>'raw',
            'value'=>!empty($objects[$interior->object_id]) ? $objects[$interior->object_id] : '',
        ),
        'desc',
        array(
            'label'=>'Статус',
            'type'=>'html',
            'value'=>"<span class='label success'>".Interior::getStatusName($interior->status)."</span>",
        ),
        array(
            'label'=>'Соавторы',
            'type'=>'html',
            'value'=>Coauthor::coauthorFormatter($coauthors),
        ),
        array(
            'label'=>'Обложка интерьера',
            'type'=>'raw',
            'value'=>Interior::imageFormater($interiorImage),
        ),
        array(
            'label'=>'Планировки',
            'type'=>'raw',
            'value'=>Interior::imageFormater($layouts),
        ),
    ),
));
?>


<?php $this->widget('application.components.widgets.InteriorContentView', array('interior' => $interior, 'errors'=>$errors)); ?>


		
<div class="actions">

        <?php echo CHtml::button('Редактировать', array('class'=>'primary btn',
                'onclick' => "document.location='{$this->createUrl('admin/create/interior', array('id' => $interior->id))}'"
        )); ?>
        <?php echo CHtml::button('Удалить', array('class'=>'danger btn','onclick' => 'if (!confirm("Удалить")) { return false; } else { document.location="/idea/admin/create/delete/id/'.$interior->id.'" }')); ?>

	<?php if (in_array(Yii::app()->user->role, array( User::ROLE_POWERADMIN, User::ROLE_ADMIN, User::ROLE_MODERATOR, User::ROLE_JUNIORMODERATOR, User::ROLE_SENIORMODERATOR ))) : ?>
		<?php echo CHtml::button('Мигрировать в архитектуру', array('class'=>'success btn', 'onclick' => 'if (!confirm("Мигрировать")) {return false;} else {document.location="/idea/admin/interior/migrate/interior_id/'.$interior->id.'"}')); ?>
	<?php endif; ?>
</div>
