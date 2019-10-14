<?php if ($user->expert_type != User::EXPERT_NONE) : ?>
	<div class="expert-icon"></div>
<?php endif; ?>
<div class="usercard-photo">
	<?php echo CHtml::image('/' . $user->getPreview(Config::$preview['crop_180']), $user->name, array('class' => '-quad-180 -gutter-bottom')); ?>
</div>
<hr class="-groove">
<div class="-gutter-left usercard-statistics">
	<div class="-col-wrap -small -gray -gutter-bottom-hf">
		<div class="user-rating">
			<?php for($r = 1; $r <= $user->data->average_rating; $r++) { ?>
				<i class="-icon-star-xs -icon-only -red"></i>
			<?php  }
			$emptyRating = 6-$r;

			for($r = 1; $r <= $emptyRating; $r++) { ?>
				<i class="-icon-star-empty-xs -icon-only -gray"></i>
			<?php } ?>
		</div>
	</div>
	<div class="-col-wrap -small -strong">
		<?php echo CFormatterEx::formatNumeral($user->data->review_count, array('отзыв', 'отзыва', 'отзывов'));  ?>
	</div>
	<div class="-col-wrap -small -gray">Проекты</div>
	<div class="-col-wrap -small -strong"><?php echo $user->data->project_quantity; ?></div>
</div>

<?php if (Yii::app()->user->id != $user->id) : ?>
	<div class="-gutter-left -gutter-right -gutter-top">
		<?php
		// --- Формирование ссылка на «Написать сообщение» ---
		$options = array();
		$options['class'] = (Yii::app()->user->getIsGuest())
			? '-guest -button -button-skyblue -block write-message'
			: '-button -button-skyblue -block write-message';
		$options['id'] = 'new_message';
		$options['onclick'] = '_gaq.push(["_trackEvent","Message","Написать"]); return true;';
		echo Chtml::tag('span', $options, 'Связаться со мной');?>
	</div>

	<?php if (Yii::app()->user->getIsGuest()) { ?>
		<div class="-hidden">

			<div class="popup popup-message-guest"
			     id="popup-message-guest">
				<div class="popup-header">
					<div class="popup-header-wrapper">
						Отправка личного сообщения
					</div>
				</div>
				<div class="popup-body">
					Чтобы отправить сообщение, <a href="#"
								      class="-login">авторизуйтесь</a>
					или <a href="/site/registration">зарегистрируйтесь</a>.
				</div>
			</div>

		</div>

		<script>
			CCommon.userMessage();
		</script>

	<?php } elseif ($user->id != Yii::app()->user->id) { ?>
		<?php // Рендерим попап отправки личного сообщения
		$this->renderPartial('//member/message/_newMessage', array('controllerId' => $this->id, 'userName' => $user->name, 'userId' => $user->id));?>
	<?php } ?>

<?php endif; ?>
<div class="-gutter-left -gutter-right -gutter-top -border-all -text-align-center -inset-top-hf -inset-bottom-hf -white-bg">
	<?php if (Yii::app()->user->id != $user->id) : ?>
		<?php if (Yii::app()->getUser()->getIsGuest() || !Review::hasReview($user->id, Yii::app()->getUser()->getId()) ) :
			 echo CHtml::link('Написать отзыв', $this->createUrl('/member/review/list', array('login'=>$user->login)), array("class"=>'-gray') );
		 else :
		 	echo CHtml::link('Ваш отзыв', $this->createUrl('/member/review/list', array('login'=>$user->login)), array('class'=>'-gray'));
		endif;
	else :
		echo CHTML::link('Мои сообщения',$this->createUrl('/member/message/inbox'), array('class'=>'-gray'));
	endif;?>
</div>

<hr class="-groove">
<div class="-gutter-left -gutter-right">

	<?php
	$address = array();
	if ($user->city_id)
		$address[] = $user->getCity();
	if ($user->address)
		$address[] = $user->address;
	if (!empty($address)) : ?>
		<span class="-icon-location-s -block -gutter-bottom -semibold"><?php echo implode(', ', $address); ?></span>
	<?php endif; ?>

</div>

<?php if (!empty($user->data->service_city_list)) : ?>
<hr class="-groove">
<div class="-gutter-left -gutter-right">
	<span class="-block -gutter-bottom-hf -semibold">Услуги в городах</span>
	<span class="-block"><?php echo $user->data->service_city_list; ?></span>
</div>
<?php endif; ?>