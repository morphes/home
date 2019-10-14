<?php echo CHtml::openTag('li', array('style'=>'padding-top: 5px;', 'id'=>'category-option-value-'.$model->id)); ?>
<?php echo CHtml::activeTextField($model, "[$model->id]value", array('style'=>'width: 135px; height:')); ?>
&nbsp;[<span class="category-value-delete" style="cursor: pointer;" value="<?php echo $model->id; ?>">x</span>]
<?php echo CHtml::error($model, "[$model->id]value")?>
<?php echo CHtml::closeTag('li'); ?>