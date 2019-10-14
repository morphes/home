<?php
$this->widget('application.components.widgets.CAjaxAutoComplete', array(
        'name'=>'Value_color_' . $value->id,
        'value'=>'',
        'sourceUrl'=>'/catalog/admin/color/acColor',
        'options'=>array(
                'minLength'=>'1',
                'showAnim'=>'fold',
                'select'=>'js:function(event, ui) {$("#Value_' . $value->id . '_value").val(ui.item.id).keyup();}',
                'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Value_' . $value->id . '_value").val("");}}',
        ),
        'htmlOptions'=>array('style'=>'width:65px;'),
));
?>
<?php echo CHtml::hiddenField('Value_'.$value->id.'_value', ''); ?>

<?php echo ' ' . CHtml::button('+', array('class'=>'btn color-value-add', 'value_id'=>$value->id, 'style'=>'padding-left:6px; padding-right:6px;')); ?>

<?php echo CHtml::openTag('ul', array('id'=>'color-value-list-'.$value->id)); ?>

<?php foreach($value->value as $val) : ?>
        <?php $color_name = Yii::app()->db->createCommand()->select('name')->from('cat_color')->where('id=:id', array(':id'=>(int)$val))->limit(1)->queryScalar(); ?>
        <?php echo CHtml::openTag('li'); ?>
                <?php echo $color_name; ?>
                [<?php echo CHtml::tag('span', array('class'=>'color-value-delete', 'style'=>'cursor:pointer; color:#0069D6;', 'value_id'=>$value->id, 'value'=>$val), 'x'); ?>]
                <?php echo CHtml::hiddenField('Value['.$value->id.'][value][]', $val); ?>
        <?php CHtml::closeTag('li'); ?>
<?php endforeach; ?>

<?php echo CHtml::closeTag('ul'); ?>