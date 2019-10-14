<?php Yii::app()->clientScript->registerCssFile('/css/user.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css'); ?>


<div class="-grid-wrapper page-title">
	<div class="-grid">
		<div class="-col-12">
			<ul class="-menu-inline -breadcrumbs">
				<li><a href="<?php echo Yii::app()->homeUrl;?>">Главная</a></li>
			</ul>
		</div>
		<div class="-col-12"><h1 class="-inline"><?php echo (!empty($user->firstname) || !empty($user->lasname)) ? $user->name : $user->login; ?></h1>
			<?php if(Yii::app()->user->id == $user->id) : ?>
			<a href="/member/profile/settings" class="-icon-pencil-xs -gutter-left -gray -small">Редактировать профиль</a>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="-grid-wrapper page-content">
	<div class="-grid">

		<div class="-col-3 profile-sidebar">
			<div class="usercard-photo">
				<?php echo CHtml::image('/' . $user->getPreview( Config::$preview['crop_180'] ), $user->name, array('class' => '-quad-180')); ?>
			</div>
			<div class="usercard-statistics">
				<div class="-gutter-bottom -text-align-center"><span class="-gray -inset-right-hf">Просмотров профиля:</span><?php echo $user->getProfileViews();?></div>
			</div>
			<div class="usercard-message-btn">
				<a class="-icon-mail -icon-softgray --red" href="<?php echo Yii::app()->createUrl('/member/message/inbox') ?>"><i>Мои сообщения</i></a>
			</div>
		</div>


		<div class="-col-9">

			<?php $this->renderPartial('//member/profile/user/_menu', array('user' => $user)); ?>


			<?php echo $content; ?>
		</div>


	</div>
</div>