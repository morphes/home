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
    ($user->city ? $user->name . ', ' . $user->city->name : $user->name),
	'Оказываемые услуги: '.$services,
	'Профиль на myhome.ru: портфолио, активность, контакты'
));

$this->keywords = implode(', ', array(
	$user->name,
	$services,
	'ремонт и благоустройство, майхоум, myhome, май хоум, myhome.ru'
));
?>

<?php
/* -----------------------------------------------------------------------------
 *  Слайдер
 * -----------------------------------------------------------------------------
 */
?>
<?php if (empty($projects)) { ?>

	<?php if ($owner) { ?>
		<div class="-grid -force-middle -inset-bottom">
			<div class="-col-4">
				<img src="/img-new/reg-promo-7.jpg">
			</div>
			<div class="-col-5">
				<h2 class="-giant -gutter-null">Создать портфолио на MyHome</h2>
				<p class="-large -gutter-top">— это лучший способ рассказать о себе<br>как о профессионале и привлечь новых клиентов</p>
				<a href="<?php echo Yii::app()->createUrl('/users', array('login' => $user->login, 'action' => 'portfolio'));?>" class="-button -button-orange -huge -semibold">Добавить новый проект</a>
			</div>
		</div>
		<hr>
	<?php } ?>

<?php } else { ?>

	<div class="-grid slider">
		<div class="-col-wrap slider-images">
		<?php foreach ($projects as $index=>$project) { ?>
			<?php $src = ($project instanceof Architecture) ? $project->getPreview('crop_380') : '/'.$project->getPreview(Config::$preview['crop_380']); ?>
			<div class="<?php if ($index == 0) echo 'visible';?>"><img src="<?php echo $src;?>" class="-quad-380"></div>
		<?php } ?>
		</div>

		<div class="-col-wrap slider-controls">
			<?php foreach ($projects as $index=>$project) { ?>
				<?php $src = ($project instanceof Architecture) ? $project->getPreview('crop_380') : '/'.$project->getPreview(Config::$preview['crop_380']); ?>
				<div class="<?php if ($index == 0) echo 'current';?>">
					<i></i>
					<span><?php echo $project->getProjectType(); ?></span>
					<?php echo CHtml::link(Amputate::getLimb($project->name, 30), $project->getElementLink()); ?>
					<p>
						<span class="-icon-bubble-s -gray"><a class="-small"><?php echo $project->count_comment; ?></a></span>
						<span class="-icon-camera-s -gutter-left -gray"><b class="-small"><?php echo $project->count_photos; ?></b></span>
						<span class="-icon-star-s -gutter-left -gray"><b class="-small"><?php echo $project->average_rating; ?></b></span>
					</p>
				</div>
			<?php } ?>

			<?php if ($owner) { ?>
				<a href="<?php echo $this->createUrl("/users/{$user->login}/portfolio"); ?>"
				   class="-icon-pencil-xs -skyblue">Обновить
								    портфолио</a>
			<?php } else { ?>
				<a href="<?php echo $this->createUrl("/users/{$user->login}/portfolio"); ?>" class="-skyblue">Перейти в портфолио</a>
			<?php } ?>
		</div>
	</div>

<?php } ?>


<div class="-grid usercard-specs">

	<?php
	/* -----------------------------------------------------------------------------
	 *  О компании
	 * -----------------------------------------------------------------------------
	 */
	?>

	<?php if (empty($user->data->about)) { ?>

		<?php if ($owner) { ?>
			<div class="-col-2 disabled">
				<h2>О компании</h2>
				<?php if ($owner) { ?>
				<a href="/member/profile/settings" class="-icon-pencil-xs -gray -small">Редактировать</a>
				<?php } ?>
			</div>
			<div class="-col-7">
				<p class="-large -gutter-null"><img src="/img-new/tmp/profile_big.png" class="-push-left -gutter-right">Каждому специалисту необходимо умело презентовать свои услуги — это повышает уровень доверия клиента.<br><a href="/member/profile/settings" class="-skyblue">Расскажите всем о своем профессионализме!</a></p>
			</div>
		<?php } ?>

	<?php } else { ?>

		<div class="-col-2">
			<h2>О компании</h2>
			<?php if ($owner) { ?>
			<a href="/member/profile/settings" class="-icon-pencil-xs -gray -small">Редактировать</a>
			<?php } ?>
		</div>
		<div class="-col-7">
			<p class="desc">
				<?php
				$userAbout = strip_tags($user->data->about);
				$visibleDesc = Amputate::getLimb($userAbout, 500, '', 'UTF-8', true);
				$descLength = mb_strlen($userAbout, 'utf-8');
				$visibleLength = mb_strlen($visibleDesc, 'utf-8');
				$hiddenDesc = mb_substr($userAbout, $visibleLength, 2000, 'utf-8');
				?>
				<span class="visible"><?php echo nl2br($visibleDesc);?></span>
				<span class="hidden"><?php echo nl2br($hiddenDesc);?></span>
			</p>
			<?php if ($descLength > 500) { ?>
				<span class="-acronym -gray" data-toggle="desc" data-alt="Свернуть">Показать полностью</span>
			<?php } ?>
		</div>

	<?php } ?>

	<?php
	/* ---------------------------------------------------------------------
	 *  Услуги
	 * ---------------------------------------------------------------------
	 */
	?>
	<?php if (!empty($serviceList)) { ?>

		<div class="-col-2">
			<h2>Услуги<a href="<?php echo $this->createUrl("/users/{$user->login}/services"); ?>" class="-gray"><?php echo count($serviceList);?></a></h2>
		</div>
		<div class="-col-7 services">
			<?php foreach($serviceList as $key => $serv) { ?>
				<h4 class="-huge -semibold">
					<?php echo $serv['service_name'];?>
					<?php if ($serv['experience'] != '0') { ?>
						(<?php echo Config::$experienceType[$serv['experience']]; ?>)
					<?php } ?>
				</h4>
				<?php echo CHtml::link(
					CFormatterEx::formatNumeral($serv['project_qt'], array('проект', 'проекта', 'проектов')),
					"/users/{$user->login}/portfolio/service/{$serv['service_id']}",
					array('class' => '-gray')
				);?>

				<?php if($key+1 == 4) { ?>
					<div><a href="<?php echo $this->createUrl("/users/{$user->login}/services"); ?>" class="-pointer-right -gray">Все услуги</a></div>
					<?php break;?>
				<?php } ?>
			<?php } ?>
		</div>

	<?php } ?>


	<?php
	/* ---------------------------------------------------------------------
	 *  Отзывы
	 * ---------------------------------------------------------------------
	 */
	?>
	<div class="-col-2 disabled">
		<h2>Отзывы</h2>
	</div>
	<div class="-col-7 reviews">

		<?php if (!$lastReviews) { ?>

			<?php if (Yii::app()->getUser()->getId() == $user->id) : ?>

				<p class="-large">У вас пока нет ни одного отзыва.
					<br> Вы можете найти новых клиентов в разделе <a href="/tenders/list" class="-skyblue">«Заказы»</a>
						  и получить заслуженные отзывы о вашей работе.</p>

			<?php else : ?>

				<p class="-gutter-bottom-hf">Пока нет ни одного отзыва о специалисте. Вы можете стать первым!</p>
				<a href="<?php echo $this->createUrl('/member/review/list', array('login'=>$user->login)); ?>" class="-icon-bubbles -skyblue">Написать отзыв</a>

			<?php endif; ?>

		<?php } else { ?>

			<?php foreach($lastReviews as $lastReview) { ?>
				<?php
				$reviewAuthor = $lastReview->getAuthor();
				$imagesId = $lastReview->getImagesId();
				?>

				<div class="review">
					<div class="-col-wrap">
						<?php echo CHtml::image('/'.$reviewAuthor->getPreview(User::$preview['crop_23']), '', array('class'=>'-quad-25')); ?>
					</div>
					<div class="-col-wrap -small name"><?php echo $reviewAuthor->name;?></div>
					<div class="-col-wrap -gray -small time"><?php echo CFormatterEx::formatDateToday($lastReview->create_time, true);?></div>
					<div class="rating">
						<?php for ($r = 1; $r <= $lastReview->rating; $r++) { ?>
							<i class="-icon-star-xs -icon-only -red"></i>
						<?php
						}
						$emptyRating = 6 - $r;

						for ($r = 1; $r <= $emptyRating; $r++) {
							?>
							<i class="-icon-star-empty-xs -icon-only -gray"></i>
						<?php } ?>

						<span class="-gray -small -gutter-left-hf"><?php echo $lastReview->getNameRating(); ?></span>
					</div>
					<div class="text"><?php echo $lastReview->message; ?></div>
				</div>
			<?php } ?>


			<?php echo CHtml::link(
				'Все отзывы',
				$this->createUrl('/member/review/list', array('login'=>$user->login)),
				array('class' =>'-gray -gutter-right')
			); ?>

		<?php } ?>

	</div>
</div>

<hr>
<div class="-grid">
	<div class="-col-7 -skip-2">
		<?php
		/* -------------------------------------------------------------
		 *  Лайки соцсетей.
		 * -------------------------------------------------------------
		 */
		?>
		<?php $this->widget('application.components.widgets.likes.Likes'); ?>
	</div>
	<div class="-col-9">
        <?php Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_item_under'); ?>
	</div>
</div>
