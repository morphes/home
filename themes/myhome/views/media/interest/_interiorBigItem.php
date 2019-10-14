<div class="-col-6">
	<a class="-block" href="<?php echo $model->getIdeaLink(); ?>" onclick = "_gaq.push(['_trackEvent','interest','click']);return true;">
		<img src="<?php echo '/'.$model->getPreview(Config::$preview['crop_460x365']); ?>" class="-rect-460-365">
		<span><?php echo $model->name; ?></span>
	</a>
	<?php if (in_array($model->author->role, array(User::ROLE_ADMIN, User::ROLE_MODERATOR, User::ROLE_JUNIORMODERATOR, User::ROLE_POWERADMIN, User::ROLE_SALEMANAGER, User::ROLE_SENIORMODERATOR))) { ?>
		<span class="-icon-user-s -gray -large -small">Редакция Myhome</span>
	<?php } else{ ?>
	<span class="-icon-user-s -gray -large -small"><?php echo $model->author->name; ?></span>
	<?php } ?>
</div>