<?php Yii::app()->clientScript->registerScript('search', "
$('#settings form').submit(function(){
	$.ajax({
                url: '" . $this->createUrl('save_settings') . "',
                data: $(this).serialize(),
                type: 'POST',
                async: false,
                success: function(){
                        alert('Настройки сохранены');
                }
        });
	return false;
});
");?>


<h4><?php echo $unit->alias; ?></h4>

<ul>
	<li><?php echo CHtml::link('Список анонсов', $this->createUrl('index')) ?></li>
        <li><?php echo CHtml::link('Название и подпись', $this->createUrl('description')); ?></li>
</ul>

<div class="form" id="settings">
	
	<?php echo CHtml::beginForm($this->createUrl('save_settings'), 'post'); ?>

	<div class="row">
		<?php echo CHtml::label('Выводить по', 'output'); ?>
		<?php echo CHtml::dropDownList('output', $settings['output'], Unit::$outputTypeLabel); ?>    
	</div>

	<?php echo CHtml::tag('br'); ?>

	<div class="row">
		<?php echo CHtml::SubmitButton('Изменить'); ?>
	</div>

	<?php echo Chtml::endForm(); ?>

</div>