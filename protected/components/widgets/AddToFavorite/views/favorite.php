<a class="-icon-gray favorite-icon
	<?php echo CHtml::encode($this->cssClass); ?>
	<?php if (Yii::app()->user->getIsGuest()) echo 'guest';?>
	<?php echo ($this->isAdded()) ? '-icon-heart' : '-icon-heart-empty';?>"

	data-item-id="<?php echo $modelId;?>"
	data-item-model="<?php echo $modelName;?>"
	data-group-id="<?php echo $defaultGroupId;?>"
	data-callback="tooltip"
	data-delete-item="<?php echo $this->deleteItem;?>"></a>