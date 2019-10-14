<?php $this->pageTitle = 'Восстановление пароля — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>

<div class="-grid-wrapper page-content">
	<div class="-grid auth-form">

		<div class="-col-12 -gutter-top-dbl -inset-top-dbl -text-align-center">
			<span class="-vast -gutter-bottom-dbl -inline">Забыли пароль?</span>
			<p class="-huge">Введите адрес электронной почты, указанный при регистрации</p>
			<form class="-col-4">
				<?php echo CHtml::activeTextField($model, 'email', array('placeholder' => 'my@email.ru'));?>
				<?php echo CHtml::error($model, 'email', array('class' => '-error'));?>
				<button class="-gutter-top -vast -button -button-skyblue">Продолжить</button>
			</form>
		</div>

	</div>
</div>