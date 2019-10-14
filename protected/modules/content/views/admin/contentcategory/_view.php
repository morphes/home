<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('left_key')); ?>:</b>
	<?php echo CHtml::encode($data->left_key); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('right_key')); ?>:</b>
	<?php echo CHtml::encode($data->right_key); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('level')); ?>:</b>
	<?php echo CHtml::encode($data->level); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('author_id')); ?>:</b>
	<?php echo CHtml::encode($data->author_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('status')); ?>:</b>
	<?php echo CHtml::encode($data->status); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('title')); ?>:</b>
	<?php echo CHtml::encode($data->title); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('alias')); ?>:</b>
	<?php echo CHtml::encode($data->alias); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('desc')); ?>:</b>
	<?php echo CHtml::encode($data->desc); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('create_time')); ?>:</b>
	<?php echo CHtml::encode($data->create_time); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('update_time')); ?>:</b>
	<?php echo CHtml::encode($data->update_time); ?>
	<br />

	*/ ?>

</div>