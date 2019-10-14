<?php $this->pageTitle = $user->name . ' — MyHome.ru'?>
<?php Yii::app()->clientScript->registerScriptFile('/js-new/profile.js'); ?>

<?php
// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = $user->name;
Yii::app()->openGraph->description = strip_tags($user->data->about);
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/logo-tb.png';
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$user->getPreview(User::$preview['crop_180']);

Yii::app()->openGraph->renderTags();
?>

<?php
	// Собираема список из трех услуг в строку
	$services = '';
	foreach($serviceList as $key=>$serv)
	{
		if ($key > 0)
			$services .= ', ';
		$services .= $serv['service_name'];
		if ($key++ > 2)
			break;
	}

$this->description = implode('. ', array(
	$user->name.', '.$user->city->name,
	'Оказываемые услуги: '.$services,
	'Профиль на myhome.ru: портфолио, активность, контакты'
));

$this->keywords = implode(', ', array(
	$user->name,
	$services,
	'ремонт и благоустройство, майхоум, myhome, май хоум, myhome.ru'
));
?>

<?php if(!empty($projects)) : ?>
<div class="slider">
	<div class="slider_imgs">
		<?php
		$cnt = 0;
		foreach ($projects as $project) {
			$class = $cnt==0 ? 'visible' : '';
			echo CHtml::openTag('div', array('class'=>$class, 'id'=>'img_'.$project->id)); // TODO: Добавить уникальный ID по типу проекта
			$src = ($project instanceof Architecture) ? $project->getPreview('crop_380') : '/'.$project->getPreview(Config::$preview['crop_380']);
			echo CHtml::link(
				CHtml::image($src, '', array('width'=>380, 'height'=>380) ),
				$project->getElementLink(),
				array()
			);
			echo CHtml::closeTag('div');
			$cnt++;
		}
		?>
	</div>
	<div class="slider_control">
		<?php
		$cnt = 0;
		foreach ($projects as $project) : ?>
		<div class="slide_item <?php echo $cnt==0 ? 'current' : ''; ?>"
		     data-image="<?php echo $project->id; ?>">
			<i></i>

			<div>
				<span><?php echo $project->getProjectType(); ?></span><br>
				<?php echo CHtml::link(Amputate::getLimb($project->name, 30), $project->getElementLink()); ?>
				<div class="block_item_counters">
					<span class="comments_quant"><i></i><?php echo $project->count_comment; ?></span>
					<span class="photos_quant"><i></i><?php echo $project->count_photos; ?></span>
					<span class="rating_quant"><i></i><?php echo $project->average_rating; ?></span>
				</div>
			</div>
		</div>
		<?php $cnt++; ?>
		<?php endforeach; ?>

		<div class="-inline -gutter-top -gutter-left -border-all portfolio-link">
			<a class="" href="<?php echo $this->createUrl("/users/{$user->login}/portfolio"); ?>">Перейти в портфолио</a>
		</div>

	</div>
	<div class="clear"></div>
</div>
<?php endif; ?>

<div class="user_info_conteiner">

	<div class="user_profile_block">
		<div class="-semibold -huge">
			О себе
		</div>
		<?php if (empty($user->data->about)) : ?>

		<div class="user_info_content empty">
			<?php if (Yii::app()->getUser()->getId() == $user->id) : ?>
			<span>Вы не добавили информацию о себе</span>
			<?php else : ?>
			<span>Пользователь не добавил информацию о себе</span>
			<?php endif; ?>
		</div>
		<?php else : ?>
		<div class="user_info_content" id="about">
			<p><?php echo nl2br(CHtml::value($user->data, 'about'));?></p>
			<span class="all_elements_link">
				<a href="#">Показать полностью</a><span>&darr;</span>
			</span>
		</div>
		<?php endif; ?>
	</div>

	<div class="user_profile_block">
		<?php
		if (empty($serviceList)) :
		?>
		<div class="user_info_head">
			Услуги
		</div>
		<div class="user_info_content empty">
			<?php if (Yii::app()->getUser()->getId() == $user->id) : ?>
			<span>Вы не добавили ни одной услуги</span>
			<?php else : ?>
			<span>Пользователь не добавил ни одной услуги</span>
			<?php endif; ?>
		</div>
		<?php else : ?>
		<div class="user_info_head -gutter-bottom">
			<?php echo CHtml::link('Услуги', "/users/{$user->login}/services", array('class'=> '-huge')); ?>
			<span class="count">Кол-во работ</span>
			<span class="exp">Стаж</span>
			<span>Место в рейтинге</span>
		</div>
		<div class="user_info_content">

			<?php foreach($serviceList as $key => $serv) : ?>

			<div class="servises_row">
				<p><?php
					$prepositionalCase = City::model()->findByPk($user->city_id)->prepositionalCase;
					// Используем CHtml::tag вместо CHtml::link, чтобы атрибут href стоял раньше title
					echo CHtml::tag(
						'a',
						array(
							'href' => Yii::app()->controller->createUrl('/member/specialist/list', array('service'=>$serv['service_url'], 'city'=>City::model()->findByPk($user->city_id)->eng_name)),
							'title' => ($prepositionalCase) ? 'Все специалисты по этой услуге в '.$prepositionalCase : '',
						),
						($prepositionalCase) ? $serv['service_name'].' <span class="text_block">в '.$prepositionalCase.'</span>' : $serv['service_name'],
						true
					);
				?></p>

				<?php if (empty( $serv['project_qt'] )) {
					echo CHtml::link($serv['project_qt'], "", array('class'=>'no_projects'));
				} else {
					echo CHtml::link($serv['project_qt'], "/users/{$user->login}/portfolio/service/{$serv['service_id']}");
				}?>
				<span><?php echo Config::$experienceType[$serv['experience']]; ?></span>
				<div class="servises_row_rating"><span><?php echo $serv['rating_pos']; ?></span> <span class="rating_from">из <?php echo $serv['uq']; ?></div>
			</div>


			<?php if($key+1 == 4) : ?>
			<div class="see_more">
				<a href="<?php echo $this->createUrl("/users/{$user->login}/services"); ?>">Все услуги
					специалиста</a> &rarr;
			</div>
			<?php break;?>
			<?php endif; ?>

			<?php endforeach; ?>

		</div>
		<?php endif; ?>
	</div>

	<div class="user_profile_block">
		<?php if (!$lastReviews) : ?>
		<div class="user_info_head">
			Отзывы и рекомендации
		</div>

		<div class="reviews empty">
			<?php if (Yii::app()->getUser()->getId() == $user->id) : ?>
			<span>Вы не получили ни одного отзыва.</span>
			<?php else : ?>
			<span>Пользователь не получил ни одного отзыва. <a href="<?php echo $this->createUrl('/member/review/list', array('login'=>$user->login)); ?>">Написать отзыв</a></span>
			<?php endif; ?>
		</div>
		<?php else :


		?>
			<div class="user_info_head -gutter-bottom-dbl">
				<?php echo CHtml::link('Отзывы', $this->createUrl('/member/review/list', array('login'=>$user->login)), array('class' => '-huge') ); ?>
			</div>
			<div class="reviews">
				<div class="reviews-list">
					<?php foreach($lastReviews as $lastReview) : ?>
						<?php
						$reviewAuthor = $lastReview->getAuthor();
						$imagesId = $lastReview->getImagesId();
						?>
						<div class="review-item">
							<div class="-col-wrap -gutter-right-qr"><a class="-block" href=<?php echo $reviewAuthor->getLinkProfile(); ?>>
									<?php echo CHtml::image('/'.$reviewAuthor->getPreview(User::$preview['crop_23']), '', array('class'=>'-quad-25')); ?>
								</a></div>
							<div class="-col-wrap -small">
								<?php echo CHtml::link($reviewAuthor->name, $reviewAuthor->getLinkProfile(), array('class' => '-gray'));?>
								<span class="-gutter-left -gray"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy в HH:mm', $lastReview->create_time); ?></span>
							</div>

							<div class="-gutter-top -gutter-bottom rewiew-item-rating">
								<?php for ($r = 1; $r <= $lastReview->rating; $r++) { ?>
									<i class="-icon-star-xs -icon-only -red"></i>
								<?php
								}
								$emptyRating = 6 - $r;

								for ($r = 1; $r <= $emptyRating; $r++) {
									?>
									<i class="-icon-star-empty-xs -icon-only -gray"></i>
								<?php } ?>

								<span class="-gutter-left-hf -small -gray"><?php echo $lastReview->getNameRating(); ?></span>
							</div>

							<div class="rewiew-item-post">
								<?php echo $lastReview->message; ?>
							</div>

							<div class="-gutter-top-dbl rewiew-item-photos">

								<?php if ($imagesId) : ?>
									<?php foreach ($imagesId as $idImage) : ?>
										<div class="-col-wrap -relative">
											<img class="-quad-60 -col-wrap -gutter-right-hf"
											     data-src="<?php echo '/' . $lastReview->getPreview($idImage, Review::$preview['crop_520']) ?>"
											     src="<?php echo '/' . $lastReview->getPreview($idImage, Review::$preview['crop_60']) ?>">
										</div>
									<?php endforeach; ?>
								<?php endif ?>
							</div>
						</div>
					<?php endforeach ?>


				</div>
				<script>
					profile.reviewImages();
					profile.reviewActions();
				</script>
				<?php echo CHtml::link('Все отзывы о специалисте', $this->createUrl('/member/review/list', array('login'=>$user->login)), array('class' =>'-red -pointer-right') ); ?>
			</div>
		<?php endif; ?>
	</div>


	<div class="clear"></div>
</div>
<div class="likes_block user_page">
	<div class="likes">
		<?php $this->widget('application.components.widgets.likes.Likes'); ?>
		<div class="visit_count">Просмотров профиля: <span><?php echo $user->profileViews; ?></span></div>
		<div class="clear"></div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		user.slider();
	})
</script>



