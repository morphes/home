<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/auth.css'); ?>

<?php if (Yii::app()->getUser()->getIsGuest()) { ?>

	<?php // -------- Неавторизованный пользователь -------- ?>

	<noindex>
		<?php if ($this->route == 'site/login') { ?>
			<span class="-white -small">Вход</span>
		<?php } else { ?>
			<a href="/site/login" class="-small -white -login">Вход</a>
		<?php } ?>

		<i class="-delimiter"></i>
		<a href="/site/registration" class="-small -white -registration">Регистрация</a>
	</noindex>

	<?php $user = new User('login');?>
	<?php $company = new Company();?>


	<?php $this->beginClip('popupAuth'); ?>

		<div id="popupAuth" class="auth-form">
			<div class="modal-header"></div>
			<div class="-col-10 -inset-top -gutter-top-dbl -text-align-center">
				<h3>Вход на MyHome</h3>
				<ul class="-menu-inline social-auth">
					<li><a href="/oauth/vkontakte" class="vk" onclick="CCommon.oauth(&quot;/oauth/vkontakte&quot;, &quot;Vkontakte&quot;); return false;"><span class="pill"><strong>ВКонтакте</strong></span></a></li>
					<li><a href="/oauth/facebook" class="fb" onclick="CCommon.oauth(&quot;/oauth/facebook&quot;, &quot;Facebook&quot;); return false;"><span class="pill"><strong>Facebook</strong></span></a></li>
					<li><a href="/oauth/odnoklassniki" class="ok" onclick="CCommon.oauth(&quot;/oauth/odnoklassniki&quot;, &quot;Odnoklassniki&quot;); return false;"><span class="pill"><strong>Одноклассники</strong></span></a></li>
					<!--<li><a href="#" class="mr"><span></span></a></li>-->
					<!--<li><a href="/oauth/twitter" class="tw" onclick='CCommon.oauth("/oauth/twitter", "Twitter"); return false;'><span></span></a></li>-->
				</ul>
			</div>
			<div class="-col-4 -skip-3 -pass-3 -inset-bottom -text-align-center">
				<form id="authForm" method="post" action="/site/login">
					<div><input type="text" tabindex="1" name="User[login]" placeholder="Электронная почта" class="-huge -text-align-center"></div>
					<div><input type="password" tabindex="2" name="User[password]" maxlength="32" placeholder="Пароль (от 4-х символов)" class="-huge -text-align-center"></div>
					<div class="-gutter-bottom -text-align-left">
						<label class="-checkbox">
							<?php echo CHtml::activeCheckBox($user, 'rememberMe'); ?>
							<span>Запомнить</span>
						</label>
						<span class="-push-right">
							<a href="/password/remember" class="-skyblue">Забыли пароль?</a>
						</span>
					</div>
					<button type="submit" tabindex="3" class="-button -button-skyblue -huge -semibold" onclick="_gaq.push(['_trackEvent','Login','Войти']); yaCounter11382007.reachGoal('lgbtn'); return true;">Войти</button>
				</form>
			</div>
			<div class="-col-10 -tinygray-box -inset-top-hf -inset-bottom-hf -gutter-top-dbl -text-align-center">
				<h3>
					Еще не зарегистрированы?
					<?php if ($this->route == 'site/registration') { ?>
						<a href="javascript:void(0)" onclick="$.modal.close();" class="-skyblue">Присоединяйтесь!</a>
					<?php } else { ?>
						<a href="/site/registration" class="-skyblue">Присоединяйтесь!</a>
					<?php } ?>

				</h3>
			</div>
		</div>

	<?php $this->endClip(); ?>



	<?php $this->beginClip('popupRegistration'); ?>

		<div id="popupReg" class="reg-form">
			<div class="modal-header"></div>
			<div class="reg-choise 9999">
				<div class="reg-exec">Исполнитель</div>
			    <div class="reg-cust">Заказчик</div>
			</div>
			<div class="modal-body">
				<div class="-col-10 -inset-top -inset-bottom -gutter-top-dbl -text-align-center">
					<h3>Создайте свой профиль — откройте больше возможностей</h3>
					<ul class="-menu-inline social-auth">
						<li><a href="/oauth/vkontakte" class="vk" onclick='CCommon.oauth("/oauth/vkontakte?return=promo", "Vkontakte"); return false;'><span class="pill"><strong>ВКонтакте</strong></span></a></li>
						<li><a href="/oauth/facebook" class="fb" onclick='CCommon.oauth("/oauth/facebook?return=promo", "Facebook"); return false;'><span class="pill"><strong>Facebook</strong></span></a></li>
						<li><a href="/oauth/odnoklassniki" class="ok" onclick='CCommon.oauth("/oauth/odnoklassniki?return=promo", "Odnoklassniki"); return false;'><span class="pill"><strong>Одноклассники</strong></span></a></li>
						<!--<li><a href="#" class="mr"><span></span></a></li>-->
						<!--<li><a href="/oauth/twitter" class="tw" onclick='CCommon.oauth("/oauth/twitter", "Twitter"); return false;'><span></span></a></li>-->
					</ul>
					<p class="-gutter-bottom-null -huge -normal -gray"><span>Или зарегистрируйтесь с помощью электронной почты</span></p>
				</div>
				<div class="-col-4 -skip-3 -pass-3 -inset-bottom -text-align-center">
					<form id="regForm" method="post" action="" autocomplete="off">
						<?php echo CHtml::hiddenField('User[role]', User::ROLE_USER); ?>

						<div>
							<?php echo CHtml::activeTextField($user, 'email', array('class' => '-huge -text-align-center', 'placeholder' => 'Электронная почта')); ?>
						</div>
						<div>
							<?php echo CHtml::activePasswordField($user, 'password', array('class' => '-huge -text-align-center', 'placeholder' => 'Пароль (от 4-х символов)')); ?>
						</div>

						<div class="-inset-top-hf -gutter-bottom-hf -gray">Регистрируясь, я соглашаюсь с <a href="/agreement" target="_blank" class="-skyblue">правилами</a></div>
						<button type="submit" class="-button -button-orange -huge -semibold">Зарегистрироваться</button>
					</form>
				</div>
				<div class="-col-8 -skip-1 -pass-1 -tinygray-box -inset-top-hf -inset-bottom-dbl -gutter-top-dbl -text-align-center">
					<h3>Хотите попасть в каталог специалистов — присоединяйтесь</h3>
					<a href="#popupReg" data-src="/site/registrationStepsForm" rel="modal" class="-block -gutter-bottom -button -button-skyblue -huge -semibold">Присоединиться как специалист</a>
				</div>
			</div>
		</div>

	<?php $this->endClip(); ?>

<?php } else {  ?>

	<?php // -------- Авторизованный пользователь -------- ?>

	<div class="toggle-user-popup">
		<?php
		/** @var $user User */
		$user = Yii::app()->user->model;?>

		<?php
		// Количество новых личных сообщений
		$msg_cnt = (int)Yii::app()->user->getFlash('msg_count');

		// Количество непрочитанных отзывов
		$review_cnt = (int)Yii::app()->redis->get(User::getRedisKeyUnreadReview(Yii::app()->user->id));

		// Общее количество нотификаций
		$totalNotification = $msg_cnt + $review_cnt;
		?>
		<?php
		if ($totalNotification > 0) {
			echo CHtml::link($totalNotification, '/member/message/inbox', array('class' => '-drop'));
		} ?>
		<a class="-icon-arrow-down -icon-pull-right -small -white" href="<?php echo $user->getLinkProfile();?>"><i class="-icon-user">Личный кабинет</i></a>
		<div class="user-popup">
			<div>
				<div class="-col-wrap push-left">
					<div>
						<a class="-block" href="<?php echo $user->getLinkProfile();?>"><?php echo CHtml::image('/' . $user->getPreview( Config::$preview['crop_80'] ), $user->name, array('class' => '-quad-80', 'width' => 80, 'height'=>80)); ?></a>
						<h4><a href="<?php echo $user->getLinkProfile();?>"><?php echo $user->name;?></a></h4>
						<div class="-gutter-bottom-hf -small -gray"><?php echo $user->email;?></div>
						<ul class="-menu-inline -small">
							<li>
								<a href="/member/message/inbox" class="-icon-mail -icon-medium -gray"></a><span class="-gray"><?php echo $msg_cnt;?></span>
							</li>
							<li class="-gutter-left">
								<a href="<?php echo Yii::app()->createUrl('/users', array('login' => $user->login, 'action' => 'reviews'));?>" class="-icon-bubbles-s -icon-medium -gray"></a><span class="-disabled"><?php echo $review_cnt;?></span>
							</li>
							<!--<li class="-gutter-left"><a class="-icon-reply -icon-medium -gray"></a><span class="-gray">5</span></li>-->
						</ul>
						<div class="-small -gutter-top-hf">
							<a class="-red" href="/member/profile/settings">Редактировать</a>
							<i class="-delimiter-gray"></i>
							<a class="-pointer-right -gutter-null -red" href="/site/logout">Выйти</a>
						</div>
					</div>
					<div class="-col-wrap push-right">
						<?php
						$this->widget('zii.widgets.CMenu',array(
							'htmlOptions' => array('class' => '-menu-block -small'),
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
									'label'   => 'Магазины и товары',
									'url'     => array('/catalog/profile/storeList'),
									'visible' => in_array(Yii::app()->user->role, array(
										User::ROLE_STORES_ADMIN,
										User::ROLE_STORES_MODERATOR
									))
								),
								array(
									'label'   => 'Добавить товар',
									'url'     => array('/catalog/profile/list'),
									'visible' => in_array(Yii::app()->user->role, array(
										User::ROLE_STORES_ADMIN,
										User::ROLE_STORES_MODERATOR
									))
								),
								array(
									'label'   => 'Сообщения',
									'url'     => array('/member/message/inbox'),
								),
								array(
									'label'   => 'Уведомления',
									'url'     => array('/member/profile/options'),
								),
							)));
						?>
					</div>
				</div>
			</div>
		</div>
	</div>


	<?php $this->beginClip('popupRegistration'); ?>

		<div id="popupReg" class="reg-form">
			<?php
			$this->renderPartial('//site/registrationStepsForm', array(
				'hideRegInfo' => true,
				'user'        => new User()
			));
			?>
		</div>

	<?php $this->endClip(); ?>

<?php } ?>

<noindex>
<div class="pill-buttons -button-group">
	<?php // Подключаем виджет для добавления в избранное
	$this->widget('application.components.widgets.ShowFavoriteLink.ShowFavoriteLink');?>
	<a class="-button -button-skyblue -icon-3d" href="/planner" title="Онлайн-планировщик"></a>
</div>
</noindex>