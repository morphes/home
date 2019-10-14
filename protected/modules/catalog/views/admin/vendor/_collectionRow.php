<?php echo CHtml::openTag('li', array('id'=>'coll_row_' . $coll_id, 'data-coll_id' => $coll_id))?>
        <?php echo CHtml::textField("[$coll_id]coll_name", $coll_name, array('class'=>'coll_input_name', 'coll_id'=>$coll_id)); ?>
	&nbsp;
	<a class="btn small success coll_up">вверх &uarr;</a>
	<a class="btn small danger coll_down">&darr; вниз</a>
        <?php echo CHtml::tag('span', array('class'=>'coll_link_delete', 'coll_id'=>$coll_id, 'style'=>'cursor:pointer'), '[ x ]'); ?>
<?php CHtml::closeTag('li')?>