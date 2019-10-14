<?php $this->pageTitle = $user->name . ' — MyHome.ru'?>



<div class="-grid">
	<div class="-col-2 -gray">Зарегистрирована:</div>
	<div class="-col-7"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy',$user->create_time);?></div>

	<div class="-col-2 -gray">Была на сайте:</div>
	<div class="-col-7"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy',$user->enter_time);?></div>

	<hr class="-col-9 -gutter-top -gutter-bottom-dbl">

	<?php if ($user->city_id) { ?>
		<div class="-col-2 -gray">Город:</div>
		<div class="-col-7"><?php echo $user->city->name . ', ' . $user->city->country->name;?></div>
	<?php } ?>

	<?php // Т.к. дата хранится в виде строки, делаем ручную проверку на корректность заполнения.
	$arr = explode('.', $user->data->birthday);
	if ((int)$arr[0] != 0 && (int)$arr[1] != 0 && (int)$arr[2] != 0) { ?>
		<div class="-col-2 -gray">День рождения:</div>
		<div class="-col-7"><?php echo Yii::app()->getDateFormatter()->format('d MMMM',$user->data->birthday);?></div>
	<?php } ?>

	<?php if ($user->data->skype) { ?>
		<div class="-col-2 -gray">Skype:</div>
		<div class="-col-7"><?php echo CHtml::value($user->data, 'skype');?></div>
	<?php } ?>

	<?php if ($user->data->icq) { ?>
		<div class="-col-2 -gray">ICQ:</div>
		<div class="-col-7"><?php echo CHtml::value($user->data, 'icq');?></div>
	<?php } ?>





	<?php // -- ССЫЛКИ НА СОЦИАЛЬНЫЕ СЕТИ --

	$fb = Oauth::checkBindFacebook($user->id);
	$vk = Oauth::checkBindVkontakte($user->id);
	$odkl = Oauth::checkBindODKL($user->id);

	if ($fb || $vk || $odkl)
	{
		?>
		<div class="-col-2 -gray">Профили в соц. сетях:</div>
		<div class="-col-7">
			<ul class="-menu-block">
		<?php
		if ($vk) {
			$link = 'http://vkontakte.ru/id'.$vk->account_name;
			$social_name = ($vk->social_name) ?$vk->social_name : $link;
			?>
			<li class="-gutter-bottom-hf"><a href="<?php echo $link;?>" class="-icon-vkontakte -icon-vk-color"><?php echo $social_name;?></a></li>
			<?php
		}

		if ($fb) {
			$link = 'http://www.facebook.com/profile.php?id='.$fb->account_name;
			$social_name = ($fb->social_name) ?$fb->social_name : $link;
			?>
			<li class="-gutter-bottom-hf"><a href="<?php echo $link;?>" class="-icon-facebook -icon-fb-color"><?php echo $social_name;?></a></li>
			<?php
		}

		if ($odkl) {
			$link = 'http://odnoklassniki.ru/profile/'.$odkl->account_name;
			?>
			<li class="-gutter-bottom-hf"><a href="<?php echo $link;?>" class="-icon-odnoklassniki -icon-odkl-color"><?php echo $odkl->social_name;?></a></li>
			<?php
		}

		?>
			</ul>
		</div>
		<?php
	}
	?>

	<?php
	// Выводим ссылки на профили других сайтов
	if ($social) {
		echo CHtml::tag('span', array('class'=>'contact_item_name'), 'Профили на других сайтах', true);
		?>
		<div class="-col-2 -gray">Профили на других сайтах:</div>
		<div class="-col-7">
			<ul class="-menu-block">
			<?php
			foreach($social as $item) {
				$link = CHtml::value($item, 'url');
				?>
				<li class="-gutter-bottom-hf"><?php echo CHtml::link($link, $link, array('class' => '-skyblue', 'target' => '_blank'));?></li>
				<?php
			}
			?>
			</ul>
		</div>
		<?php
	}
	?>
</div>



<?php if ($owner) { ?>

	<?php if ($user->role == User::ROLE_USER) { ?>
		<div class="-tinygray-box usercard-spec-invite -hidden">
			<h3 class="-huge -semibold">Хотите попасть в каталог специалистов — присоединяйтесь</h3>
			<a href="#" class="-button -button-skyblue -large -strong -registration">Присоединиться как специалист</a>
			<div><span class="-icon-cross-xs -pseudolink -small -gray" onclick="Common.hideBlock('.usercard-spec-invite', event); if (window.localStorage) { localStorage.setItem('usercard-spec-invite', true); }"><i>Спасибо, не надо</i></span></div>
		</div>

		<script>
			if (window.localStorage) {
				if (!localStorage.getItem('usercard-spec-invite')) {
					$('.usercard-spec-invite').removeClass('-hidden');
				}
			}
		</script>
	<?php } ?>

<?php } else { ?>

	<?php // Если смотрим чужой профиль, то может ему отправить сообщение ?>
	<div class="shadow_block padding-18">
		<div class="pm_conteiner">
		<?php
		if ( !Yii::app()->user->isGuest ) { // FIXME: Fixed message url in form ?>

			<form action="<?php echo $this->createUrl("/users/{$user->login}/");?>" method="post">
				<div class="input_conteiner">
					<label>Отправить сообщение</label>
					<textarea class="textInput" id="profile-sendmessage" name="message"></textarea>
				</div>
				<div class="clear"></div>
				<div class="btn_conteiner">
					<input type="submit" value="Отправить сообщение" class="btn_grey" onclick="_gaq.push(['_trackEvent','Message','Отправить']); yaCounter11382007.reachGoal('grbtmsend'); return true;" />
				</div>
			</form>

		<?php } else { ?>
			<form class="login" action="#">
				<p class="lc-not">Чтобы отправить сообщение, <a href="#" class="-login">авторизуйтесь</a> или <a href="/site/registration">зарегистрируйтесь</a></p>
			</form>
		<?php } ?>
		</div>
		<div class="spacer-10"></div>
	</div>

<?php } ?>

