<?php
/**
 * Представление для отображения кнопки "В избранное" карточки товара
 */
?>
<a href="javascript:void(0)"
   class="-pseudolink -red favorite-icon <?php echo ($this->isAdded())
	   ? '-icon-heart' : '-icon-heart-empty'; ?> -gutter-left-hf
	<?php if (Yii::app()->user->getIsGuest())
	   echo 'guest'; ?>"
   data-item-id="<?php echo $modelId; ?>"
   data-item-model="<?php echo $modelName; ?>"
   data-group-id="<?php echo $defaultGroupId; ?>"
   data-callback="text">
	<i class="-acronym"><?php echo ($this->isAdded()) ? 'Из избранного'
			: 'В избранное'; ?></i>
</a>

