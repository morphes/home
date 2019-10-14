<?php
/**
 * Представление для отображения кнопки "В избранное" для раздела медиа (журнал)
 */
?>
<div class="sidebar-tools" style="background: none repeat scroll 0 0 white; border: none; text-align: left; margin-top: 10px;">
	<span class="-red favorite-icon
		<?php echo ($this->isAdded()) ? '-icon-heart' : '-icon-heart-empty';?>
	      	<?php if (Yii::app()->user->getIsGuest()) echo 'guest';?>"

	      data-item-id="<?php echo $modelId;?>"
	      data-item-model="<?php echo $modelName;?>"
	      data-group-id="<?php echo $defaultGroupId;?>"
	      data-data='<?php echo json_encode($data, JSON_NUMERIC_CHECK); ?>'
	      data-callback="text">

		<i class="-acronym"><?php echo ($this->isAdded()) ? 'Из избранного' : 'В избранное';?></i>
	</span>
</div>