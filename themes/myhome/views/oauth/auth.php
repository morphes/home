<div class="oauth-wrapper popup-login">
	<div class="head">
		<img src="/img/oauth-logo.png" width="85" height="26" />
		<h1>Авторизация</h1>
	</div>
	<div class="hr"></div>
	<div class="popup-body" style="text-align: left; padding: 25px 0 0 40px;">
		<form action="" method="post">
			<?php echo CHtml::hiddenField('authorize', true); ?>
			<p class="p-login-name">
				<label for="p-login-name"><strong>Логин</strong></label><br>
				<?php echo CHtml::activeTextField($user, 'login', array('id' => 'p-login-name', 'class' => 'textInput', 'tabindex' => '1'));?><br>
				<label for="p-login-save" class="checkbox"><?php echo CHtml::activeCheckBox($user, 'rememberMe', array('id' => 'p-login-save'));?>Запомнить меня</label>
			</p>
			<p class="p-login-pass">
				<label for="p-login-pass"><strong>Пароль</strong></label><br>
				<?php echo CHtml::activePasswordField($user, 'password', array('id' => 'p-login-pass', 'class' => 'textInput', 'tabindex' => '2'));?><br>
				<?php /*<a href="/password/remember">Забыли пароль?</a>*/?>
			</p>
			<p class="p-login-submit" style="margin-right: 40px;">
				<button type="submit" class="btn_grey" tabindex="3">Войти</button>
			</p>
			<div class="spacer"></div>
			<?php
				echo CHtml::error($user, 'password', array('class' => 'p-login-error'));
			?>
		</form>
	</div>
</div>