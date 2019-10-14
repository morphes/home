<?php
$cityId = empty($cityId) ? $data->city_id : $cityId;
$city = City::model()->findByPk($cityId);
if (is_null($city))
	return;

/** @var $data MediaEvent */
$eventParams = $data->params;
$flag = Country::getFlagById($city->country_id);
$countryName = Country::getNameById($city->country_id);
?>

<div class="item" id="<?php echo $data->id; ?>">
	<div class="item_image">
		<div class="action_date">
			<span class="date">
				<?php
				echo CHtml::tag('b', array(), Yii::app()->getDateFormatter()->format('d', $data->start_time) )
					. Yii::app()->getDateFormatter()->format('MMMM', $data->start_time)
					. CHtml::tag('span', array(), Yii::app()->getDateFormatter()->format('y', $data->start_time) );
				?>
			</span>
			<?php if (!empty($data->end_time)) : ?>
			<span>—</span>
			<span class="date">
						<?php
				echo CHtml::tag('b', array(), Yii::app()->getDateFormatter()->format('d', $data->end_time) )
					. Yii::app()->getDateFormatter()->format('MMMM', $data->end_time)
					. CHtml::tag('span', array(), Yii::app()->getDateFormatter()->format('y', $data->end_time) );
				?>
			</span>
			<?php endif; ?>
		</div>
		<?php echo CHtml::image( '/'.$data->getPreview(MediaEvent::$preview['crop_300x213']), '', array('width'=>300, 'height'=>213) ); ?>
	</div>
	<div class="descript">
		<div class="action_type">
			<?php
			echo CHtml::image('/'.$flag, $countryName, array('title'=>$countryName));
			echo $city->name .' • ' . $eventParams['typeName'];
			?>
		</div>
		<h2><a class="item_head" href="<?php echo $data->getElementLink(); ?>"><?php echo $data->name; ?></a></h2>
		<?php if ($data->is_online) : ?>
		<span class="online_label">Онлайн-мероприятие</span>
		<?php endif; ?>
		<div class="item_info">
			<div class="block_item_info">
				<?php
				$cnt = 0;
				foreach ($eventParams['theme'] as $key=>$themeName) {
					if ($cnt != 0)
						echo ', ';
					echo CHtml::link($themeName);
					$cnt++;
				}
				?>
			</div>
			<div class="block_item_counters">
				<span class="visitors_quant"><i></i><?php echo $data->count_visit; ?></span>
			</div>
		</div>
	</div>

	<div class="clear"></div>
</div>