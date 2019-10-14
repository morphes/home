<?php $this->pageTitle = 'Регистрация — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>


<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/auth.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/scroll.js'); ?>


<div class="-grid-wrapper page-content">
	<div class="-grid reg-form">


		<div class="-col-10 -skip-1 -pass-1 -inset-top -inset-bottom -gutter-top-dbl -text-align-center">
			<h3>Создайте свой профиль — откройте больше возможностей</h3>
			<ul class="-menu-inline social-auth">
				<li><a href="/oauth/vkontakte" class="vk" onclick='CCommon.oauth("/oauth/vkontakte", "Vkontakte"); return false;'><span class="pill"><strong>ВКонтакте</strong></span></a></li>
				<li><a href="/oauth/facebook" class="fb" onclick='CCommon.oauth("/oauth/facebook", "Facebook"); return false;'><span class="pill"><strong>Facebook</strong></span></a></li>
				<li><a href="/oauth/odnoklassniki" class="ok" onclick='CCommon.oauth("/oauth/odnoklassniki", "Odnoklassniki"); return false;'><span class="pill"><strong>Одноклассники</strong></span></a></li>
				<!--<li><a href="#" class="mr"><span></span></a></li>-->
				<!--<li><a href="/oauth/twitter" class="tw" onclick='CCommon.oauth("/oauth/twitter", "Twitter"); return false;'><span></span></a></li>-->
			</ul>
			<p class="-gutter-bottom-null -huge -normal -gray"><span>Или зарегистрируйтесь с помощью электронной почты</span></p>
		</div>
		<div class="-col-4 -skip-4 -pass-4 -inset-bottom -text-align-center">
			<form id="authForm" method="post" action="" autocomplete="off">

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
		<div class="-col-8 -skip-2 -pass-2 -tinygray-box -inset-top-hf -inset-bottom-dbl -gutter-top-dbl -text-align-center">
			<h3>Хотите попасть в каталог специалистов — присоединяйтесь</h3>
			<a href=".reg-form" data-src="/site/registrationStepsForm" rel="gethtml" class="-block -button -button-skyblue -huge -semibold">Присоединиться как специалист</a>
		</div>


	</div>
</div>