<div class="review">
	<div class="-col-wrap">
		<?php echo CHtml::image('/' . $data->author->getPreview(Config::$preview['crop_25']), '', array('class' => '-quad-25')); ?>
	</div>
	<div class="-col-wrap -small name"><?php echo $data->author->name; ?></div>
	<div class="-col-wrap -gray -small time"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy в HH:mm:ss', $data->create_time); ?></div>
	<div class="rating">
		<?php


		 $this->widget('application.components.widgets.WStar', array(
			'selectedStar' => $data->mark,
			'useNewRealisation' => true,
			'labels'=>array(1=>'Ужасная модель', 2=>'Плохая модель', 3=>'Обычная модель', 4=>'Хорошая модель', 5=>'Отличная модель'),
		));


		?>
	</div>
	<div class="text">
		<span class="-block -gutter-bottom"><strong>В
							    общем:</strong><?php echo $data->message; ?></span>
		<span class="-block -gutter-bottom"><strong>Достоинства:</strong><?php echo $data->merits; ?></span>
		<span class="-block -gutter-bottom"><strong>Недостатки:</strong><?php echo $data->limitations; ?></span>
	</div>
</div>