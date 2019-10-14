<?php
$cityId = empty($cityId) ? $data->city_id : $cityId;
$city = City::model()->findByPk($cityId);
if (is_null($city))
	return;

$eventParams = $data->params;
$flag = Country::getFlagById($city->country_id);
$countryName = Country::getNameById($city->country_id);
?>

<div class="item" id="<?php echo $data->id; ?>">
	<div class="item_image">
		<?php /** @var $data MediaEvent */
		echo CHtml::link(
			CHtml::image( '/'.$data->getPreview(MediaEvent::$preview['crop_160x110']), '', array('width'=>160, 'height'=>110) ),
			$data->getElementLink()
		);
		?>
	</div>
	<div class="descript">
		<div class="action_type">
			<?php
			echo CHtml::image('/'.$flag, $countryName, array('title'=>$countryName));
			echo $city->name .' • ' . $eventParams['typeName'];
			?>
		</div>
		<?php if ($data->is_online) : ?>
		<span class="online_label">Онлайн-мероприятие</span>
		<?php endif; ?>
		<h2><a class="item_head" href="<?php echo $data->getElementLink(); ?>"><?php echo $data->name; ?></a></h2>
		<span class="event_date"><?php echo CFormatterEx::formatDateRange($data->start_time, $data->end_time); ?></span>
		<div class="item_info">
			<div class="block_item_info">
				<?php
				$cnt = 0;
				foreach ($eventParams['theme'] as $key=>$themeName) {
					if ($cnt != 0)
						echo ', ';
					echo CHtml::link($themeName, MediaEvent::getListLink(array('theme[]'=>$key)));
					$cnt++;
				}
				?>
				<div class="block_item_counters">
					<span class="visitors_quant<?php if ($data->getIsVisit(Yii::app()->getUser()->getId())) echo ' visit'; ?>" title="<?php echo empty($data->count_visit) ? 'Пока никто не планирует посетить' : 'Планируют посетить'; ?>"><i></i><?php echo $data->count_visit; ?></span>
				</div>
			</div>

		</div>
	</div>

	<div class="clear"></div>
</div>