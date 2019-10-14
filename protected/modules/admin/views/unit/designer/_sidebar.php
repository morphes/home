<?php Yii::app()->clientScript->registerScript('search', "
$('#settings form').submit(function(){
	$.ajax({
                url: '" . $this->createUrl($this->id.'/save_settings') . "',
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


<h4><?php echo $unit->alias ?></h4>

<ul>
        <li><?php echo CHtml::link('Список анонсов', $this->createUrl($this->id.'/index')) ?></li>
        <li><?php echo CHtml::link('Название и подпись', $this->createUrl($this->id.'/description')) ?></li>
        <li><?php echo CHtml::link('Блок услуг', '#') ?></li>
</ul>

<div class="form" id="settings">

        <?php echo CHtml::beginForm(); ?>

        <div class="row">
                <?php echo CHtml::label('Выводить крупный анонс', 'largeOutput'); ?>
                <?php echo CHtml::dropDownList('largeOutput', $settings['largeOutput'], Unit::$outputTypeLabel); ?>    
        </div>

        <div class="row">
                <?php echo CHtml::label('Выводить мелкий анонс', 'smallOutput'); ?>
                <?php echo CHtml::dropDownList('smallOutput', $settings['smallOutput'], Unit::$outputTypeLabel); ?>
        </div>

        <div class="row">
                <?php echo CHtml::SubmitButton('Изменить'); ?>
        </div>

        <?php echo Chtml::endForm(); ?>

</div>