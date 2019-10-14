<?php $this->pageTitle = 'Проект: ' . $project->name . ' — ' . $project->author->name . ' — MyHome.ru'?>

<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css'); ?>

<?php $this->renderPartial('//idea/portfolio/_serviceNavigator', array('user' => $user, 'currentServiceId' => $service->id)); ?>

<?php if (Yii::app()->user->id == $project->author_id && ( $project instanceof Interior )) : ?>
	<div class="project-docs cf">
		<div class="pd-certificate">
			<a id="new_pdf" class="handler" href="/">Сформировать свидетельство PDF<i></i></a>
			<div class="c-hinter"><i></i>
				<p class="c-hinter-text">Защитите свои авторские права, сформировав свидетельство
					о размещении вашего произведения на портале MyHome.
					Подробнее узнать о защите авторских прав вы можете <a href="<?php Yii::app()->homeUrl;?>/content/deposition">здесь</a>.</p>
			</div>
			<div class="docs_list">
				<ul>
					<li><a class="new_pdf pdf_author" href="#">Для автора</a></li>
					<li><a class="new_pdf pdf_copy" href="#">Для правообладателя</a></li>
				</ul>
			</div>
		</div>

		<?php if (!empty($historyDepositionHtml)) : ?>
			<div class='pd-history'>
				<?php echo $historyDepositionHtml; ?>
			</div>
		<?php endif; ?>

		<?php
		Yii::app()->clientScript->registerScript('pd-history','
			$(".new_pdf").click(function(){
				if($("div").is(".pd-history")){
					$(".pd-history").html("<img src=\"/img/horizont-loader.gif\"/>");
				}else{
					$(".pd-certificate").after("<div class=\"pd-history\"><img src=\"/img/horizont-loader.gif\"/></div>");
				}

				//  Проверяем тип пользователя, и формируем url для генерации pdf файла
				var type = "copy";
				if ($(this).hasClass("pdf_author"))
					type = "author";

				$.post(
					"/idea/copyrightfile?intid='.$project->id.'&type="+type,
					{},
					function(response) {
						if (response.success) {
							$(".pd-history").html(response.history_html);
							document.location = "/download/pdfdeposition/"+response.copyright_id;
						}
						else
							alert("Ошибка генерации PDF-сертификата.");

					}, "json"
				);
				$(".pd-certificate").removeClass("pd-active");
				return false;
			});
		', CClientScript::POS_READY);
		?>


	</div>
<?php endif; ?>


<?php if (Yii::app()->user->id == $project->author_id) : ?>
	<div class="project_tools">
		<span class="project_tools_edit"><i></i><a href="<?php echo $project->getUpdateLink(); ?>">Редактировать</a></span>
		<span class="project_tools_del" link="<?php echo $project->getDeleteLink(); ?>"><i></i><a href="javascript:void(0)">Удалить</a></span>
	</div>

	<?php Yii::app()->clientScript->registerScript('projectDelete', '
		$(".project_tools_del").click(function(){
			$this = $(this);
			if(confirm("Вы действительно хотите удалить проект?")) {
				window.location = $this.attr("link");
			}
		});
	', CClientScript::POS_READY);?>
<?php endif; ?>


<div class="clear"></div>

<div class="project_block shadow_block corner-top">
	<div class="project_head">
		<h2>
			<?php echo $project->name; ?>

			<?php // Подключаем виджет для добавления в избранное
			$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
				'modelId' => $project->id,
				'modelName' => get_class($project),
				'cssClass' => 'inline',
			));?>
		</h2>
		<br>
		<span class="info">
			<span class="p">
				<span class="gi-comments"><?php echo $project->count_comment;?><i></i></span>
				<span class="gi-photos"><?php echo $project->count_photos;?><i></i></span>
				<?php $this->widget('application.components.widgets.WStar', array(
					'selectedStar' => $project->average_rating,
					"showNumRating" => true
				));?>
			</span>
		</span>
	</div>


	<?php if($next_work) : ?>
		<?php
		$url = $next_work->getElementLink();
		?>
		<a class="next_proj_link" href="<?php echo $url;?>">Следующий проект</a>
	<?php endif; ?>

	<?php $this->widget('application.components.widgets.WGalleryPlayer', array(
		'arrModels' => $arrImages,
		'model' => $project,
		'authorMode' => Yii::app()->user->id == $project->author_id && get_class($project) == 'Interior',
	));?>

</div>
<div class="shadow_block white padding-18 corner-none">

	<?php if($service->type == Config::INTERIOR) : ?>
		<div class="projects_options">
			<div class="project_option">
				<span>Стиль</span><br/>
				<?php
				if (isset($all_styles) && ! empty($all_styles)) {
					$all_styles = array_unique($all_styles);
					$index = 0;
					foreach($all_styles as $key => $style_name) {
						if ($index++ > 0)
							echo ', ';
						if ( $project instanceof Interiorpublic )
							$url = '/idea/interiorpublic/'.$project->getBuild()->eng_name.'-'.IdeaHeap::model()->findByPk($project->style_id)->eng_name;

						elseif ($project instanceof Interior)
							$url = '/idea/interior/'.IdeaHeap::model()->findByPk($key)->eng_name;
						else
							$url = $project->getFilterLink(array('style'=>$style_name));
						echo CHtml::link('<span class="text_block">стиль </span>'.$style_name.'<span class="text_block"> в интерьере</span>', $url);
						$this->keywords.= $style_name.' ';
					}
				}
				?>
			</div>
			<div class="project_option">
				<?php if (isset($all_colors) && ! empty($all_colors) ) : ?>
					<span>Цвета</span><br/>
					<ul class="colors_list">
					<?php foreach($all_colors as $color_class => $color) :?>
						<li class="<?php echo $color_class; ?>"><p class="hide"><?php echo $color['name']; ?></p>
							<?php echo CHtml::link($color['name'].' цвет в интерьере', '/idea/interior/'.$color['eng_name'], array('title'=>$color['name']) ); ?>
							<p class="hide"><?php echo $color['name']; ?></p>
							<div></div>
						</li>
					<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<div class="project_option last">
				<?php if ($icRooms) : ?>
					<span>Помещения</span><br/>
					<?php foreach($icRooms as $room_id=>$room) : ?>
						<?php
						if ($project instanceof Interior)
							$url = '/idea/interior/'.IdeaHeap::model()->findByAttributes(array('option_key' => 'room', 'option_value' => $room['room_name']))->eng_name;
						else
							$url = $project->getFilterLink(array('room'=>$room['room_name']));
						?>
						<?php echo CHtml::link($room['room_name'].'<span class="text_block"> дизайн интерьера</span>', $url ); ?>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if ( $project instanceof Interiorpublic ): ?>
					<span>Тип общественного интерьера</span><br/>
					<?php $url = '/idea/interiorpublic/'.$buildType->eng_name; ?>
					<?php echo CHtml::link($buildType->option_value.'<span class="text_block"> дизайн интерьера</span>', $url); ?>
				<?php endif; ?>
			</div>
			<div class="clear"></div>
		</div>
	<?php endif; ?>
	<div class="clear"></div>
	<div class="projects_description">
		<p><?php echo nl2br(CHtml::value($project, 'desc'));?></p>
	</div>
</div>
<div class="shadow_block likes_block corner-bottom">
	<span class="block_header">Поделиться идеей с друзьями</span>

	<div class="likes">
		<?php $this->widget('application.components.widgets.likes.Likes'); ?>
		<div class="visit_count">Просмотров страницы: <span><?php echo $user->profileViews; ?></span></div>
		<div class="clear"></div>
	</div>

</div>
<?php // Несколько последних комментариев
$this->widget('application.components.widgets.WComment', array(
	'model' => $project,
	'hideComments' => !$project->getCommentsVisibility(),
	'showCnt' => 0,
));?>

<div class="project_nav" style="display: block;">
        <div class="wrapper">
                <div class="proj_arrow">
                        <?php if($prev_work) :?>
                                <img src="<?php
					if ($prev_work instanceof Architecture) {
						echo $prev_work->getPreview('crop_45');
					} else {
						echo '/'.$prev_work->getPreview(Config::$preview['crop_45']);
					}
				?>" />
                                <div class="proj_prev">
					<?php
					$url = $prev_work->getElementLink();
					?>
                                        &larr; <?php echo CHtml::link('Предыдущий проект', $url);?>
					<br>
                                        <?php echo CHtml::link($prev_work->name, $url, array('class'=>'proj_arrow_name'));?>
                                </div>
                        <?php endif;?>
                </div>


                <div class="proj_arrow_up">
                        &uarr;<br/>
                        <a href="#">Наверх</a>
                </div>


                <div class="proj_arrow">
                        <?php if($next_work) :?>
                                <img src="<?php
					if ($next_work instanceof Architecture) {
						echo $next_work->getPreview('crop_45');
					} else {
						echo '/'.$next_work->getPreview(Config::$preview['crop_45']);
					}
				?>" />
                                <div class="proj_next">
					<?php
					$url = $next_work->getElementLink();
					?>
                                        <?php echo CHtml::link('Следующий проект', $url); ?>  &rarr;
					<br>
                                        <?php echo CHtml::link($next_work->name, $url, array('class'=>'proj_arrow_name'));?>
                                </div>
                        <?php endif;?>
                </div>

        </div>
</div>


