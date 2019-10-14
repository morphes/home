<?php

$result = Yii::app()->cache->get('IDEA:LIST:ARCHITECTURE:' . $data->id);

if (!$result) {
	$htmlOut = '';
	$class = '';

	// Начинаем буферизацию
	ob_start();
	?>
		<div class="item_photo">
			<a href="<?php echo $this->createUrl('/idea/architecture/' . $data->id);?>">
				<img align="left" width="210" height="210" alt="<?php echo $data->name;?>" src="<?php echo $data->getPreview('crop_210');?>">
			</a>
		</div>
		<div class="item_descript">
			<div class="item_icons">
				<span class="item_info_comments"><i></i><?php echo $data->count_comment;?></span>
				<span class="item_info_photos"><i></i><?php echo $data->count_photos;?></span>
				<span class="item_info_rating"><i></i><?php echo round($data->average_rating, 1);?></span>
			</div>


			<?php if (isset(Config::$rolesAdmin[$data->author->role])) { ?>

				<span class="item_autor">Редакция MyHome</span>

			<?php } else { ?>

				<a class="item_autor" href="<?php echo $this->createUrl("/users/{$data->author->login}");?>">
					<?php echo Amputate::getLimb($data->author->name, 35);?>
				</a>
			<?php } ?>

			<a class="item_name" href="<?php echo $this->createUrl('/idea/architecture/' . $data->id);?>"><?php echo $data->name;?></a>

			<?php if ($data->getObject()->option_value != "Дом, коттедж, особняк") { ?>
				<span class="build_type"><?php echo $data->getBuild()->option_value;?></span>
			<?php } ?>
		</div>
	<?php
	// Получаем код из буфера и очищаем его.
	$htmlOut = ob_get_clean();

	// Получаем дополнительный класс для Карточки товара
	$optionValue = $data->getObject()->option_value;
	$addClass = ($optionValue == 'Хозяйственные постройки' || $optionValue == 'Общественные здания')
		? 'commerce'
		: '';

	$result = array(
		'html'  => $htmlOut,
		'class' => $addClass
	);

	Yii::app()->cache->set('IDEA:LIST:ARCHITECTURE:' . $data->id, $result, Cache::DURATION_HOUR);
}


?>
<div class="item <?php echo $result['class']; ?>">

	<?php echo $result['html']; ?>

	<?php // Подключаем виджет для добавления в избранное
	$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
		'modelId'   => $data->id,
		'modelName' => 'Architecture',
		'cssClass'  => 'idea'
	));?>
</div>