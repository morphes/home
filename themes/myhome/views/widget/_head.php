<div id="logo">
	<?php
	// Получаем картинку для логотипа
	$logo_img = CHtml::image('/img/myhome-logo-ny.png', 'МайХоум: интернет-портал по ремонту и благоустройству Вашего дома');

	if ($this->getRoute() != 'site/index')
		echo CHtml::link($logo_img.'<span class="text_block">MyHome — интернет-помощник по ремонту и благоустройству Вашего дома</span>', Yii::app()->homeUrl.'/');
	else
		echo $logo_img.'<span class="text_block">MyHome — интернет-помощник по ремонту и благоустройству Вашего дома</span>';
	?>
</div>

<?php if (!Yii::app()->user->isGuest) : ?>
<div class="auth">
	<div class="user-menu">
		<div class="toggler">
			<?php // Получаем фотку текущего пользователя.
			$user_photo_src = '/'.Yii::app()->user->model->getPreview( Config::$preview['crop_23'] );?>

			<span class="image" style="background:url('<?php echo $user_photo_src;?>') 0 0 no-repeat">
				<?php echo CHtml::image($user_photo_src, '', array('width' => 23, 'height' => 23));?>
			</span>
			<a href="<?php echo Yii::app()->user->model->getLinkProfile();?>">Мой профиль</a>
			<i></i>

			<?php // Выводим количество новых сообщений
			$msg_cnt = Yii::app()->user->getFlash('msg_count');
			if ($msg_cnt) {
				$span = CHtml::tag('span', array('class' => 'messages-qnt'), $msg_cnt, true);
				$link = CHtml::link($span, '/member/message/inbox', array('class' => 'new-msg'));
				echo $link;
			}
			?>
		</div>

		<div class="user-menu-inner">
			<?php
			$this->widget('zii.widgets.CMenu',array(
				'items'=>array(
					array(
						'label'   => 'Портфолио',
						'url'     => array('/users/'.Yii::app()->user->model->login.'/portfolio'),
						'visible' => in_array(Yii::app()->user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR, User::ROLE_POWERADMIN)),
					),
					array(
						'label'   => 'Услуги',
						'url'     => array('/users/'.Yii::app()->user->model->login.'/services'),
						'visible' => in_array(Yii::app()->user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR, User::ROLE_POWERADMIN)),
					),
					array(
						'label'   => 'Сообщения',
						'url'     => array('/member/message/inbox'),
					),
					array(
						'label'   => 'Редактирование профиля',
						'url'     => array('/member/profile/settings'),
					),
					array(
						'label'   => 'Настройка уведомлений',
						'url'     => array('/member/profile/options'),
					),
			)));
			?>
		</div>
	</div>
	<?php

	echo CHtml::link('Выйти', '/site/logout', array('class' => 'button-login button button-black logout', 'onclick' => "_gaq.push(['_trackEvent','Login','Выйти']); yaCounter11382007.reachGoal('obtn'); return true;"));
	?>
</div>
<?php endif; ?>



<?php if (Yii::app()->user->isGuest) { ?>
	
	<?php 
	// Сохраняем попап для авторизации в Clip. Затем выводим его в //layout/main
	$this->beginClip('popup_auth');?>

	<?php $user = new User('login');?>

	<div class="popup popup-login" id="popup-login">
		<div class="popup-header"><div class="popup-header-wrapper">Авторизация <span class="popup-close" title="Закрыть">Закрыть</span></div></div>
		<div class="popup-body">
			<form action="/site/login" name="ajaxlogin" method="post">
				<p class="p-login-name">
					<label for="p-login-name"><strong>Электронная почта или логин</strong></label><br>
					<?php echo CHtml::activeTextField($user, 'login', array('id' => 'p-login-name', 'class' => 'textInput', 'tabindex' => '1'));?><br>
					<label for="p-login-save" class="checkbox"><?php echo CHtml::activeCheckBox($user, 'rememberMe', array('id' => 'p-login-save'));?>Запомнить меня</label>
				</p>
				<p class="p-login-pass">
					<label for="p-login-pass"><strong>Пароль</strong></label><br>
					<?php echo CHtml::activePasswordField($user, 'password', array('id' => 'p-login-pass', 'class' => 'textInput', 'tabindex' => '2'));?><br>
					<a href="/password/remember">Забыли пароль?</a>
				</p>
				<p class="p-login-submit">
					<button type="submit" class="btn_grey" tabindex="3" onclick="_gaq.push(['_trackEvent','Login','Войти']); yaCounter11382007.reachGoal('lgbtn'); return true;">Войти</button>
				</p>
				<div class="spacer"></div>
				<p class="error-title" style="display: none;">Такого пользователя не существует или пароль введен неверно</p>
				
				<div class="hr"></div>
				<p class="p-login-social">
					<span>Вход через социальные сети</span>
					<?php echo CHtml::link(CHtml::image('/img/tw.png', 'Twitter', array('title' => 'Twitter', 'onclick' => 'CCommon.oauth("/oauth/twitter", "Twitter"); return false;')), '#'); ?>
					<?php echo CHtml::link(CHtml::image('/img/fb.png', 'Facebook', array('title' => 'Facebook', 'onclick' => 'CCommon.oauth("/oauth/facebook", "Facebook"); return false;')), '#'); ?>
					<?php echo CHtml::link(CHtml::image('/img/vk.png', 'ВКонтакте', array('title' => 'ВКонтакте', 'onclick' => 'CCommon.oauth("/oauth/vkontakte", "Vkontakte"); return false;')), '#'); ?>
					<?php echo CHtml::link(CHtml::image('/img/ok.png', 'Одноклассники', array('title' => 'Одноклассники', 'onclick' => 'CCommon.oauth("/oauth/odnoklassniki", "Одноклассники"); return false;')), '#'); ?>
				</p>
			</form>
		</div>
	</div>

	<?php
	Yii::app()->clientScript->registerScript('auth', "
	$(function(){
		var popup = $('.popup-login');

		popup.find('.p-login-error').hide();

		popup.find('form').submit(function(){
			popup.find('.error-title').hide();

			$.post(
				'/site/ajaxlogin', $(this).serialize(),
				function(response){
					if (response.success) {
						window.location = window.location.href.replace( /#.*/, '');
					} else if(response.tmpPassRequired) {
                                                document.forms['ajaxlogin'].submit();
                                        }
					else {
						if (response.message)
							popup.find('.error-title').html(response.message);
						else
							popup.find('.error-title').html('Такого пользователя не существует или пароль введен неверно');
						
						popup.find('.error-title').show();
					}
				}, 'json'
			);
			return false;
		});
	})	
	");
	?>

	<?php $this->endClip(); ?>
	
<?php } // <<== if (Yii::app()->user->isGuest) ?>