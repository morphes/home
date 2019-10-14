<div id="user_menu">
	<div class="user_card_top">
		<?php if ($user->expert_type != User::EXPERT_NONE) : ?>
		<s class="expert"></s>
		<?php endif; ?>
		<div class="top_block"></div>
		<div class="avatar"><?php echo CHtml::image('/' . $user->getPreview( Config::$preview['crop_180'] ), $user->name, array('width' => 180, 'height'=>180)); ?></div>

		<?php if ( ! Yii::app()->user->isGuest && Yii::app()->user->id == $user->id) : ?>
		<div class="message_block">
			<i></i><a href="<?php echo Yii::app()->createUrl('/member/message/inbox') ?>">Мои сообщения</a>
		</div>
		<?php endif; ?>

	</div>
</div>