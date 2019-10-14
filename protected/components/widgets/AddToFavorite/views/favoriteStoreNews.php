<?php
/**
 * Представление для отображения кнопки "В избранное" для раздела медиа (журнал)
 */
?>

<span href="#" class="-pseudolink -red favorite-icon
	<?php echo ($this->isAdded()) ? '-icon-heart' : '-icon-heart-empty';?>
	<?php if (Yii::app()->user->getIsGuest()) echo 'guest';?>"
	data-item-id="<?php echo $modelId;?>"
	data-item-model="<?php echo $modelName;?>"
	data-group-id="<?php echo $defaultGroupId;?>"
	data-callback="text">
		<i><?php echo ($this->isAdded()) ? 'Из избранного' : 'В избранное';?></i>
</span>