<?php echo CHtml::openTag('li', array('id'=>'similar-' . $pid . '-' . $spid)); ?>
        <?php $s_product = Yii::app()->db->createCommand()->select('id, category_id')->from('cat_product')->where('id=:id', array(':id'=>(int)$spid))->queryRow(); ?>
        <?php echo CHtml::link('#'.$s_product['id'], $this->createUrl('update', array('ids'=>$s_product['id'], 'category_id'=>$s_product['category_id']))); ?>
        [<?php echo CHtml::tag('span', array('class'=>'similar-button-delete', 'style'=>'cursor:pointer; color:#0069D6;','pid'=>(int)$pid, 'spid'=>(int)$spid), 'x'); ?>]
<?php echo CHtml::closeTag('li'); ?>