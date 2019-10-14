<?php $countModels = count($models); ?>

<?php if ($countModels > 0): ?>
	<div class="favorite_item">
		<h3>Интерьеры<span class="items_count quant_f"><?php echo $countModels;?></span></h3>
		<span class="hide_items"><i></i><a href="#">Свернуть</a></span>

		<div class="favorite_list_conteiner">
			<div class="gallery-210">

				<?php foreach($models as $proj) : ?>
				<div class="item">

					<div class="item_photo">
						<?php
							// Если работа добавлена редакцией MyHome, то ссылку делаем на каталог идей.
							// Иначе ссылаемся на работу в портфолио автора.
							if (isset(Config::$rolesAdmin[$proj->author->role]))
								$projectUrl = Yii::app()->createUrl("/idea/", array('interior' => $proj->id));
							else
								$projectUrl = Yii::app()->createUrl("/users/{$proj->author->login}/project/{$proj->service_id}/{$proj->id}?t=1");
						?>
						<a href="<?php echo $projectUrl; ?>">
							<img align="left" width="210" height="210" alt="<?php $proj->name; ?>" src="<?php echo '/'.$proj->getPreview(Config::$preview['crop_210']) ?>">
						</a>
					</div>
					<div class="item_descript">


						<div class="item_icons">
							<span class="item_info_comments"><i></i><?php echo $proj->count_comment; ?></span>
							<span class="item_info_photos"><i></i><?php echo $proj->count_photos; ?></span>
							<span class="item_info_rating"><i></i><?php echo round($proj->average_rating, 1); ?></span>
						</div>

						<?php if(isset(Config::$rolesAdmin[$proj->author->role])) : ?>
							<span class="item_autor">Редакция MyHome</span>
						<?php else : ?>
							<a class="item_autor" href="<?php echo $this->createUrl("/users/{$proj->author->login}"); ?>">
								<?php echo Amputate::getLimb($proj->author->name, 35); ?>
							</a>
						<?php endif; ?>

						<a class="item_name" href="<?php echo Yii::app()->createUrl("/users/{$proj->author->login}/project/{$proj->service_id}/{$proj->id}?t=1"); ?>">
							<?php echo $proj->name; ?>
						</a>
					</div>

					<?php // Подключаем виджет для добавления в избранное
					$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
						'modelId' => $proj->id,
						'modelName' => 'Interior',
						'cssClass' => 'idea',
						'deleteItem' => true
					));?>
				</div>
				<?php endforeach; ?>

				<div class="clear"></div>
			</div>
		</div>
	</div>
<?php endif; ?>