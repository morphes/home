<div class="gallery-210 <?php echo $galleryAdditionalClass?>">
	<?php /** @var $proj IProject */
	foreach($projects as $proj) : ?>
		<div class="item" data-id="<?php echo $proj->id; ?>" data-type="<?php echo $proj->getTypeId(); ?>">

			<?php if($proj->author_id == Yii::app()->user->id) : ?>
				<div class="autor_functions" style="opacity: 0.5;">
					<div class="edit_this_project">
						<a href="<?php echo $proj->getUpdateLink(); ?>"><span>редактировать</span></a>
					</div>
					<div class="del_this_project">
						<a href="#" data-link="<?php echo $proj->getDeleteLink();?>"><span>удалить</span></a>
					</div>
				</div>
			<?php endif; ?>

			<div class="item_photo">
				<?php
				$url = $proj->getElementLink();
				?>
				<a href="<?php echo $url; ?>">
					<img align="left" width="210" height="210" alt="<?php $proj->name; ?>" src="<?php
						if ($proj instanceof Architecture) { // TODO: удалить после внедрения
							echo $proj->getPreview('crop_210');
						} else {
							echo '/'.$proj->getPreview(Config::$preview['crop_210']);
						}
				?>"></a>
			</div>
			<div class="item_descript">

				<?php if($renderCounters) : ?>
					<div class="item_icons">
						<span class="item_info_comments"><i></i><?php echo $proj->count_comment; ?></span>
						<span class="item_info_photos"><i></i><?php echo $proj->count_photos; ?></span>
						<span class="item_info_rating"><i></i><?php echo round($proj->average_rating, 1); ?></span>
					</div>
				<?php else : ?>
					<a class="item_autor" href="<?php echo Yii::app()->createUrl("/users/{$proj->author->login}/portfolio/service/{$proj->service_id}"); ?>">
						<?php echo $proj->service->name; ?>
					</a>
				<?php endif; ?>

				<a class="item_name" href="<?php echo $url; ?>" title="<?php echo $proj->name;?>">
					<?php echo trim(mb_substr($proj->name, 0, 45, 'UTF-8')).((mb_strlen($proj->name, 'UTF-8') > 45)?'...':''); ?>
				</a>
			</div>

			<?php // Подключаем виджет для добавления в избранное
			$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
				'modelId' => $proj->id,
				'modelName' => get_class($proj),
				'cssClass' => 'idea'
			));?>
		</div>
	<?php endforeach; ?>
</div>

<?php if(isset($proj)) : ?>
	<?php echo CHtml::hiddenField('lasttime', $proj->create_time, array('id'=>'lasttime'))?>
<?php endif; ?>