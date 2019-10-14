<div class="item">
	<?php
	$link = $this->createUrl('/users', array('login' => $data->login));
	$name = Amputate::getLimb($data->name, 30);
	echo CHtml::link(CHtml::image('/' . $data->getPreview( Config::$preview['crop_150'] ), $name, array('width' => 120, 'height' => 120, 'style' => 'margin-right:20px;', 'align' => 'left') ), $link);
	?>
	<span class="info">
		<?php echo CHtml::link($name, $link); ?>

		<br/>
		<span class="p si-city"><?php
			$city = $data->getCityObj();
			if ( ! is_null($city))
				echo CHtml::tag('span', array(), $city->name);
			?></span>

		<p>
			<span class="spec_option">Проекты:</span>
			<span class="spec_option_value"><?php echo $data->data->project_quantity; ?></span>
		</p>
	</span>

	<?php // Подключаем виджет для добавления в избранное
	$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
		'modelId' => $data->id,
		'modelName' => 'User',
		'cssClass' => 'specialist',
		'deleteItem' => true
	));?>
</div>


