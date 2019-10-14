<div class="item commerce">

	<?php if ($this->beginCache('IDEA:LIST:INTERIORPUBLIC:' . $data->id, array('duration' => 3600))) { ?>

		<div class="item_photo">
			<a href="<?php echo $this->createUrl('/idea/interiorpublic/'.$data->id); ?>">
				<img align="left" width="210" height="210" alt="<?php echo $data->name; ?>" src="<?php echo '/'.$data->getPreview(Config::$preview['crop_210']); ?>">
			</a>
		</div>
		<div class="item_descript">
			<div class="item_icons">
				<span class="item_info_comments"><i></i><?php echo $data->count_comment; ?></span>
				<span class="item_info_photos"><i></i><?php echo $data->count_photos; ?></span>
				<span class="item_info_rating"><i></i><?php echo round($data->average_rating, 1); ?></span>
			</div>

			<?php if(isset(Config::$rolesAdmin[$data->author->role])) : ?>
				<span class="item_autor">Редакция MyHome</span>
			<?php else : ?>
				<a class="item_autor" href="<?php echo $this->createUrl("/users/{$data->author->login}"); ?>">
					<?php echo Amputate::getLimb($data->author->name, 35); ?>
				</a>
			<?php endif; ?>
			<a class="item_name" href="<?php echo $this->createUrl('/idea/interiorpublic/'.$data->id); ?>" title="<?php echo $data->name;?>"><?php echo Amputate::getLimb($data->name, 37); ?></a>

			<?php
			$build = $data->getBuild();
			if ($build->id != $oneBuild)
				$nameBuild = CHtml::link($build->option_value, '/idea/interiorpublic/'.$build->eng_name);
			else
				$nameBuild = $build->option_value;
			?>
			<span class="build_type"><?php echo $nameBuild;?></span>
		</div>

	<?php $this->endCache(); } ?>


	<?php // Подключаем виджет для добавления в избранное
	$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
		'modelId' => $data->id,
		'modelName' => 'Interiorpublic',
		'cssClass' => 'idea'
	));?>
</div>