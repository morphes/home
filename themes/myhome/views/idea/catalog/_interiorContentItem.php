<?php
/**
 * @var $data InteriorContent
 */
?>

<div class="item">
	<?php if ($this->beginCache('IDEA:LIST:INTERIOR:' . $data->id, array(
		//'duration'   => 3600,
		'dependency' => array(
			'class' => 'system.caching.dependencies.CDbCacheDependency',
			'sql'   => 'SELECT update_time FROM interior_content WHERE id = ' . intval($data->id)
		)
	))) { ?>
		<div class="item_photo">
			<a href="<?php echo $data->getIdeaLink(); ?>">
				<img align="left" width="210" height="210" alt="<?php echo $data->interior->name; ?>" src="<?php echo '/'.$data->getPreview(Config::$preview['crop_210']); ?>">
			</a>
		</div>
		<div class="item_descript">
			<div class="item_icons">
				<span class="item_info_comments"><i></i><?php echo $data->interior->count_comment; ?></span>
				<span class="item_info_photos"><i></i><?php echo $data->interior->count_photos; ?></span>
				<span class="item_info_rating"><i></i><?php echo round($data->interior->average_rating, 1); ?></span>
			</div>

			<?php if(isset(Config::$rolesAdmin[$data->interior->author->role])) : ?>
				<span class="item_autor">Редакция MyHome</span>
			<?php else : ?>
				<a class="item_autor" href="<?php echo $this->createUrl("/users/{$data->interior->author->login}"); ?>">
					<?php echo $data->interior->author->name; ?>
				</a>
			<?php endif; ?>
			<a class="item_name" href="<?php echo $data->getIdeaLink(); ?>"><?php echo $data->interior->name; ?></a>
		</div>
	<?php $this->endCache(); } ?>

	<?php // Подключаем виджет для добавления в избранное
	$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
		'modelId'   => $data->interior_id,
		'modelName' => 'Interior',
		'cssClass'  => 'idea'
	));?>
</div>
