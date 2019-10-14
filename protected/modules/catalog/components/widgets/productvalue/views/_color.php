<?php $colors = Yii::app()->db->createCommand()->from('cat_color')->queryAll();?>

<ul class="colors_list <?php echo $errorClass; ?>">
    <?php foreach($colors as $color) : ?>
            <?php if(in_array($color['id'], $value->value)) $checked = true; else $checked = false;?>
            <li class="<?php echo $color['param']; ?>" title="<?php echo $color['name']; ?>">
                <div></div>
                <?php echo CHtml::checkBox("Value[$value->id][value][]", $checked, array('value'=>$color['id'])); ?>
            </li>
    <?php endforeach; ?>
</ul>