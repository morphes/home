<?php Yii::app()->getClientScript()->registerCssFile('/css-new/generated/favorites.css');?>
<?php $this->pageTitle = $groupName . ' — MyHome.ru'?>
<?php Yii::app()->getClientScript()->registerScriptFile('/js/functions.js');?>

<!-- Page title widget //-->
<div class="-grid-wrapper page-title">
	<div class="-grid">
		<div class="-col-12">
			<ul class="-menu-inline -breadcrumbs">
				<li><a href="/">Главная</a></li>
			</ul>
		</div>
		<div class="-col-8 -pass-4 -gutter-bottom-dbl -inset-bottom"><h1><?php echo $groupName; ?></h1><span class="-gray -small">Избранное пользователя </span><a class="-small -strong" href="<?php echo $user->getLinkProfile(); ?>"><?php echo $user->name; ?></a></div>
	</div>
</div>
<!-- EOF Page title widget //-->


<div class="-grid-wrapper page-content">
	<div class="-grid">
		<?php foreach($items as $it) : ?>

			<?php $class = $it->model; $item = $class::model()->findByPk($it->model_id); ?>


			<?php if ( $it->model == 'Interior' ): ?>
				<?php
					if (isset(Config::$rolesAdmin[$item->author->role]))
						$url = Yii::app()->createUrl("/idea/", array('interior' => $item->id));
					else
						$url = Yii::app()->createUrl("/users/{$item->author->login}/project/{$item->service_id}/{$item->id}?t=1");
				?>
				<?php $type = 'Идеи'; ?>
				<?php $img = '/'.$item->getPreview(Config::$preview['crop_220']);?>
				<?php $name = $item->name; ?>
			<?php endif; ?>


			<?php if ( $it->model == 'Interiorpublic' ): ?>
				<?php
					if (isset(Config::$rolesAdmin[$item->author->role]))
						$url = Yii::app()->createUrl("/idea/", array('interiorpublic' => $item->id));
					else
						$url = Yii::app()->createUrl("/users/{$item->author->login}/project/{$item->service_id}/{$item->id}?t=2");
				?>
				<?php $type = 'Идеи'; ?>
				<?php $img = '/'.$item->getPreview(Config::$preview['crop_220']);?>
				<?php $name = $item->name; ?>
			<?php endif; ?>


			<?php if ( $it->model == 'Architecture' ): ?>
				<?php
					// Если работа добавлена редакцией MyHome, то ссылку делаем на каталог идей.
					// Иначе ссылаемся на работу в портфолио автора.
					if (isset(Config::$rolesAdmin[$item->author->role]))
						$url = Yii::app()->createUrl("/idea/", array('architecture' => $item->id));
					else
						$url = Yii::app()->createUrl("/users/{$item->author->login}/project/{$item->service_id}/{$item->id}");
				?>
				<?php $type = 'Идеи'; ?>
				<?php $img = '/'.$item->getPreview(Config::$preview['crop_220']);?>
				<?php $name = $item->name; ?>
			<?php endif; ?>

			<?php if ( $it->model == 'User' ): ?>
				<?php $url = $this->createUrl('/users', array('login' => $item->login)); ?>
				<?php $type = 'Специалисты'; ?>
				<?php $img = '/'.$item->getPreview(Config::$preview['crop_220']);?>
				<?php $name = $item->name; ?>
			<?php endif; ?>

			<?php if ( $it->model == 'Product' ): ?>
				<?php $url = Product::getLink($item->id, null, $item->category_id); ?>
				<?php $type = 'Товары'; ?>
				<?php $img = '/' . $item->cover->getPreviewName(Config::$preview['crop_220']);?>
				<?php $name = $item->name; ?>
			<?php endif; ?>

			<?php if ( $it->model == 'Portfolio' ): ?>
				<?php $url = Yii::app()->createUrl("/users/{$item->author->login}/project/{$item->service_id}/{$item->id}"); ?>
				<?php $type = 'Портфолио'; ?>
				<?php $img = '/'.$item->getPreview(Config::$preview['crop_220']);?>
				<?php $name = $item->name; ?>
			<?php endif; ?>

			<?php if ( $it->model == 'MediaKnowledge' ): ?>
				<?php $url = $item->getElementLink(); ?>
				<?php $type = 'Знания'; ?>
				<?php $img = '/' . $item->preview->getPreviewName(Config::$preview['crop_220']);?>
				<?php $name = $item->title; ?>
			<?php endif; ?>

			<?php if ( $it->model == 'MediaNew' ): ?>
				<?php $url = $item->getElementLink(); ?>
				<?php $type = 'Новости'; ?>
				<?php $img = '/' . $item->preview->getPreviewName(Config::$preview['crop_220']);?>
				<?php $name = $item->title; ?>
			<?php endif; ?>

			<?php if ( $it->model == 'UploadedFile' ): ?>
				<?php $url = $it->getParentObject() ? $it->getParentObject()->getIdeaLink() . '#p_' . $it->model_id : ''; ?>
				<?php $type = 'Изображения'; ?>
				<?php $img = $it->getFavoriteObject() ? '/' . $it->getFavoriteObject()->getPreviewName(Config::$preview['crop_210']) : '';?>
				<?php $name = $it->getParentObject() ? $it->getParentObject()->name : ''; ?>
			<?php endif; ?>


			<div class="-col-3">
				<a href="<?php echo $url; ?>" class="-block"><img src="<?php echo $img; ?>" alt="" class="-quad-220"></a>
				<div class="-gray"><span class="-small"><?php echo $type; ?></span>
					<?php if ($user->id != Yii::app()->user->id) : ?>
						<?php $this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
							'modelId' => $it->model_id,
							'modelName' => $it->model,
							'cssClass' => '-icon-only',
							'deleteItem' => true
						));?>
					<?php endif; ?>
				</div>

				<a href="<?php echo $url; ?>"><?php echo Amputate::getLimb($name, 44); ?></a>
			</div>

		<?php endforeach; ?>
	</div>
</div>