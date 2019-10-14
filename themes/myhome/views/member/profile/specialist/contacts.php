<?php $this->pageTitle = 'Контактные данные — ' . $user->name . ' — MyHome.ru'?>

<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>

<div class="contact_list">
	<?php if ($user->city_id) { ?>
		<div class="contact_item">
			<span class="contact_item_name">Город</span>
			<p><?php echo $user->city->name . ', ' . $user->city->country->name;?></p>
		</div>
	<?php } ?>
	<?php if ($user->address) { ?>
		<div class="contact_item">
			<span class="contact_item_name"><?php echo $user->getAttributeLabel('address');?></span>
			<p><?php echo CHtml::value($user, 'address');?></p>
		</div>
	<?php } ?>
	<?php if ($user->phone && !$user->data->hide_phone) { ?>
		<div class="contact_item">
			<span class="contact_item_name">Телефон:</span>
			<p><?php echo CHtml::value($user, 'phone');?></p>
		</div>
	<?php } ?>
	<?php if ($user->data->site) { ?>
		<div class="contact_item">
			<span class="contact_item_name"><?php echo $user->data->getAttributeLabel('site');?></span>

			<?php $url = Amputate::absoluteUrl( CHtml::value($user->data, 'site') );
			$favArr = parse_url($user->data->site);

			$fav_url = isset($favArr['host']) ? 'http://'.$favArr['host'].'/favicon.ico' : '';
			?>
			<p><span class="ico">
				<?php // Если фавикон существует, то сработает onload для картинки и покажет нам иконку ?>
				<img src="<?php echo $fav_url;?>" width="16" height="16" onload="$(this).show();" class="hide">
			</span><noindex><?php echo CHtml::link($url, $url, array('target' => '_blank', 'rel' => 'nofollow')); ?></noindex></p>
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
</div>
<div class="contact_list  social_network">
	<?php // -- ССЫЛКИ НА СОЦИАЛЬНЫЕ СЕТИ --
	echo CHtml::openTag('div', array('class'=>'contact_item'));
	$tw = Oauth::checkBindTwitter($user->id);
	$fb = Oauth::checkBindFacebook($user->id);
	$vk = Oauth::checkBindVkontakte($user->id);
	if ($tw || $fb || $vk)
	{

		echo CHtml::tag('span', array('class'=>'contact_item_name'),'Профили в социальных сетях');

		if ($vk) {
			$link = 'http://vkontakte.ru/id'.$vk->account_name;
			$social_name = ($vk->social_name) ?$vk->social_name : $link;
			echo CHtml::openTag('p');
				echo CHtml::tag('span', array('class'=>'ico'), CHtml::image('/img/vk.png', '', array('width' => 16, 'height' => '16')));
			?>
			<noindex>
			<?php
			echo CHtml::link($social_name, $link, array('target' => '_blank', 'rel' => 'nofollow'));
			?>
			</noindex>
			<?php
			echo CHtml::closeTag('p');
		}

		if ($fb) {
			$link = 'http://www.facebook.com/profile.php?id='.$fb->account_name;
			$social_name = ($fb->social_name) ?$fb->social_name : $link;
			echo CHtml::openTag('p');
				echo CHtml::tag('span', array('class'=>'ico'), CHtml::image('/img/fb.png', '', array('width' => 16, 'height' => '16')));
				echo CHtml::link($social_name, $link, array('target' => '_blank'));
			echo CHtml::closeTag('p');
		}

		if ($tw) {
			$link = 'http://twitter.com/#!/'.$tw->account_name;
			$social_name = ($tw->social_name) ?$tw->social_name : $link;
			echo CHtml::openTag('p');
				echo CHtml::tag('span', array('class'=>'ico'), CHtml::image('/img/tw.png', '', array('width' => 16, 'height' => '16')));
				echo CHtml::link($social_name, $link, array('target' => '_blank'));
			echo CHtml::closeTag('p');
		}

	}
	echo CHtml::closeTag('div');
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

<?php // Если смотрим чужой профиль, то может ему отправить сообщение
if (!$owner) { ?>
	<div class="shadow_block padding-18">
		<div class="pm_conteiner">
			<?php
			if ( !Yii::app()->user->isGuest ) { // FIXME: Fixed message url in form ?>

				<form action="<?php echo $this->createUrl("/users/{$user->login}/contacts");?>" method="post">
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
		<?php if (!empty($msgError)) {
			echo CHtml::tag('p', array('class'=>'error-title', 'style'=>'display: none;'), $msgError);
		} else if (!empty($msgSuccess)) {
			echo CHtml::tag('p', array('class'=>'good-title', 'style'=>'display: none;'), $msgSuccess);
		}
		?>
	</div>
<?php } ?>
