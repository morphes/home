<h3>Пожалуйста, укажите адрес вашей электронной почты</h3>

<form action="" method="POST">

	<?php echo CHtml::errorSummary($user, null, null, array('style' => 'color: red;')); ?>


	<input type="text" name="email" value="<?php echo $user->email;?>" style="width: 250px;">
	<p style="font-size: small; color: gray;">С его помощью Вы сможете восстановить пароль в случае утери</p>

	<input type="hidden" name="return" value="<?php echo $return;?>">

	<button type="submit">Сохранить</button>
</form>


