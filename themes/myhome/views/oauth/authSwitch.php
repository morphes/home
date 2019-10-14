<div class="oauth-wrapper">
	<div class="head">
		<img src="/img/oauth-logo.png" width="85" height="26" />
		<h1>Авторизация</h1>
	</div>
	<div class="hr"></div>
	<div class="body">
		<button class="btn_grey f-left" type="submit" onclick="location = '<?php echo $this->createUrl('', array('type' => 'register'));?>'">Я новый пользователь</button>
		<button class="btn_grey f-right" type="submit" onclick="location = '<?php echo $this->createUrl('', array('type' => 'auth'));?>'">У меня есть аккаунт на MyHome</button>
	</div>
</div>