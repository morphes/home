<?php $this->pageTitle = 'Активность — ' . $user->name . ' — MyHome.ru'?>

<div class="reviews_page">
	<div class="menu_level2">
		<ul>
			<li data-value="1" class="<?php if ($view == 'all') echo 'current'; ?>">
				<?php echo CHtml::link('Все действия', $this->createUrl('/member/profile/activity', array('login'=>$user->login))); ?>
			</li>
			<li data-value="2" class="<?php if ($view == 'review') echo 'current'; ?>">
				<?php echo CHtml::link('Мои отзывы ', $this->createUrl('/member/profile/activity', array('login'=>$user->login, 'view'=>'review'))); ?>
				<!--<span class="good_review">+<?php /*echo $statistic['plus']; */?></span>/<span class="bad_review">-<?php /*echo $statistic['minus']; */?></span>-->
			</li>
			<li data-value="3" class="<?php if ($view == 'comment') echo 'current'; ?>">
				<?php echo CHtml::link('Комментарии', $this->createUrl('/member/profile/activity', array('login'=>$user->login, 'view'=>'comment'))); ?>
			</li>
		</ul>

	</div>
	<div class="clear"></div>
	<div class="reviews">
	<?php foreach ($dataProvider->getData() as $activity) {
		$object = $activity->getObject();
		if (empty($object))
			continue;
		echo $object->renderActivityItem($user);
	}
	?>
	</div>
</div>
