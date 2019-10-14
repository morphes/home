<?php $this->pageTitle = $user->name . ' — MyHome.ru'?>

<div class="user_profile shadow_block white padding-18">
	<div class="contact_list">
		<?php if ($user->city_id) { ?>
		<div class="contact_item">
			<span class="contact_item_name">Город</span>
			<p><?php echo $user->city->name . ', ' . $user->city->country->name;?></p>
		</div>
		<?php } ?>
		<?php if ($user->address) { ?>
		<div class="contact_item">
			<span class="contact_item_name">Адрес</span>
			<p><?php echo $user->address; ?></p>
		</div>
		<?php } ?>

		<?php // Т.к. дата хранится в виде строки, делаем ручную проверку на корректность заполнения.
		$arr = explode('.', $user->data->birthday);
		if ((int)$arr[0] != 0 && (int)$arr[1] != 0 && (int)$arr[2] != 0) { ?>
		<div class="contact_item">
			<span class="contact_item_name">День рождения</span>
			<p><?php echo Yii::app()->getDateFormatter()->format('d MMMM',$user->data->birthday);?></p>
		</div>
		<?php } ?>
		<?php if ($user->data->skype) { ?>
		<div class="contact_item">
			<span class="contact_item_name"><?php echo $user->data->getAttributeLabel('skype');?></span>
			<p><span class="ico"><img src="/img/skype.jpg"></span><?php echo CHtml::value($user->data, 'skype');?></p>
		</div>
		<?php } ?>

		<?php if ($user->data->icq) { ?>
		<div class="contact_item">
			<span class="contact_item_name"><?php echo $user->data->getAttributeLabel('icq');?></span>
			<p><span class="ico"><img src="/img/icq.png"></span><?php echo CHtml::value($user->data, 'icq');?></p>
		</div>
		<?php } ?>

		<div class="contact_item">
			<span class="contact_item_name">Зарегистрирован</span>
			<p><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy',$user->create_time);?></p>
		</div>
		<div class="contact_item">
			<span class="contact_item_name">Был на сайте</span>
			<p><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy',$user->enter_time);?></p>
		</div>

		<span class="profile_visit">Просмотров профиля: <b><?php echo $user->getProfileViews(); ?></b></span>
	</div>

	<div class="contact_list  social_network">
	<?php // -- ССЫЛКИ НА СОЦИАЛЬНЫЕ СЕТИ --

	$tw = Oauth::checkBindTwitter($user->id);
	$fb = Oauth::checkBindFacebook($user->id);
	$vk = Oauth::checkBindVkontakte($user->id);
	$odkl = Oauth::checkBindODKL($user->id);

	if ($tw || $fb || $vk || $odkl)
	{
		echo CHtml::openTag('div', array('class'=>'contact_item'));
		echo CHtml::tag('span', array('class'=>'contact_item_name'),'Профили в социальных сетях',true);

		if ($vk) {
			$link = 'http://vkontakte.ru/id'.$vk->account_name;
			$social_name = ($vk->social_name) ?$vk->social_name : $link;

			echo CHtml::openTag('p');
				echo CHtml::tag('span', array('class'=>'ico'), CHtml::image('/img/vk.png', '', array('width' => 16, 'height' => '16')));
				echo CHtml::link($social_name, $link);
			echo CHtml::closeTag('p');
		}

		if ($fb) {
			$link = 'http://www.facebook.com/profile.php?id='.$fb->account_name;
			$social_name = ($fb->social_name) ?$fb->social_name : $link;

			echo CHtml::openTag('p');
				echo CHtml::tag('span', array('class'=>'ico'), CHtml::image('/img/fb.png', '', array('width' => 16, 'height' => '16')));
				echo CHtml::link($social_name, $link);
			echo CHtml::closeTag('p');
		}

		if ($tw) {
			$link = 'http://twitter.com/#!/'.$tw->account_name;
			$social_name = ($tw->social_name) ?$tw->social_name : $link;
			echo CHtml::openTag('p');
				echo CHtml::tag('span', array('class'=>'ico'), CHtml::image('/img/tw.png', '', array('width' => 16, 'height' => '16')));
				echo CHtml::link($social_name, $link);
			echo CHtml::closeTag('p');
		}

		if ($odkl) {
			$link = 'http://odnoklassniki.ru/profile/'.$odkl->account_name;
			echo CHtml::openTag('p');
			echo CHtml::tag('span', array('class'=>'ico'), CHtml::image('/img/ok.png', '', array('width' => 16, 'height' => '16')));
			echo CHtml::link($odkl->social_name, $link);
			echo CHtml::closeTag('p');
		}

		echo CHtml::closeTag('div');
	}

	?>
		<div class="contact_item">
		<?php
		// Выводим ссылки на профили других сайтов
		if ($social) {
			echo CHtml::tag('span', array('class'=>'contact_item_name'), 'Профили на других сайтах', true);
			foreach($social as $item) {
				$link = CHtml::value($item, 'url');
				echo CHtml::openTag('p');
					echo CHtml::link($link, $link, array('target' => '_blank'));
				echo CHtml::closeTag('p');
			}
		}
		?>
		</div>
	</div>


	<div class="clear"></div>
</div>
<div class="spacer-18"></div>
<?php // Если смотрим чужой профиль, то может ему отправить сообщение
if (!$owner) { ?>
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

