<?php
$flag = Country::getFlagById($event['country_id']);
?>
<div class="block_item">
	<div class="action_type">
		<?php
		if (!empty($flag)) {
			$countryName = Country::getNameById($event['country_id']);
			echo CHtml::image('/'.$flag, $countryName, array('title'=>$countryName));
		}
		echo $event['city_name']; ?> • <?php echo $event['type_name']; ?>
	</div>
	<?php echo CHtml::link($event['event_name'], Yii::app()->controller->createUrl('/journal/events/'.$event['id']), array('class'=>'block_item_info') ); ?>
	<div class="block_item_info">
		<?php echo CFormatterEx::formatDateRange($event['start_time'], $event['end_time'], '—', false); ?>
	</div>
	<div class="clear"></div>
</div>



