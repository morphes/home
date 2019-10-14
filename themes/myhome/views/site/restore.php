<?php $this->pageTitle = 'Восстановление пароля — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>


<div class="-grid-wrapper page-content">
	<div class="-grid auth-form">

		<div class="-col-12 -gutter-top-dbl -inset-top-dbl -text-align-center">
			<span class="-vast -gutter-bottom-dbl -inline">Придумайте новый пароль</span>
			<div class="">
				<form class="-col-4" method="POST">
					<?php echo CHtml::activePasswordField($model, 'password', array('placeholder' => 'Пароль (от 4-ех символов)')); ?>
					<input type="text" name="password_2" placeholder="Повторить пароль">

					<div class="-text-align-left -error-list -hidden -gutter-bottom-dbl" style="display: block;">
						<i class="-icon-alert"></i>

						<ol>
							<?php if ($model->getErrors()) { ?>
								<?php foreach ($model->getErrors() as $key=>$value) { ?>
									<li><?php echo $value[0];?></li>
								<?php } ?>
							<?php } ?>
						</ol>
					</div>

					<button class="-gutter-top -vast -button -button-skyblue">Продолжить</button>
				</form>
			</div>
		</div>

	</div>
</div>
