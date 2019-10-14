<p class="signature"><img src="img/tmp/signature.png" /></p>

<div class="top_space"></div>
<div id="wrapper">
	<div id="evidence_descript">
		<h1>Свидетельство</h1>
		<h2>о размещении информации о произведении на портале myhome.ru</h2>
		<table class="table">
			<tr>
				<td style="width: 420px;">Данное свидетельство подтверждает факт размещения на портале myhome.ru произведения в указанное время (дата добавления произведения) указанным субъектом авторского права.</td>
				<td>
					Дата формирования свидетельства: <strong>(MSK, UTC+4) <?php echo $date;?></strong><br>
					Номер свидетельства в базе данных MyHome: <strong>№<?php echo $nextNumber;?></strong>
				</td>
			</tr>
		</table>
		
	</div>	

	<div id="evidence_info">
		<h2>Субъект авторского права</h2>
		<table class="table">
			<tr>
				<td class="l-col">Профиль на портале myhome.ru</td>
				<td class="bold"><?php
					$link = Yii::app()->homeUrl.$user->getLinkProfile();
					echo CHtml::link($link, $link);
				?></td>
			</tr>
			<tr>
				<td>ФИО / Название компании, указанное на портале myhome.ru</td>
				<td class="bold"><?php
				if ($user->role == User::ROLE_SPEC_JUR)
					echo $user->firstname;
				else
					echo $user->lastname.' '.$user->firstname.' '.$user->secondname;
				?></td>
			</tr>
			<tr>
				<td>Статус</td>
				<td class="bold"><?php
					if ($type == 'author')
						echo 'Автор';
					else
						echo 'Правообладатель';
				?></td>
			</tr>
			<tr>
				<td>Логин на портале myhome.ru</td>
				<td class="bold"><?php echo $user->login;?></td>
			</tr>
			<tr>
				<td>Электронный адрес </td>
				<td class="bold"><?php echo $user->email;?></td>
			</tr>
		</table>

		<h2>Объект авторского права</h2>
		<table class="table">
			<tr>
				<td class="l-col">Объект</td>
				<td class="bold">Произведение искусства</td>
			</tr>
			<tr>
				<td>Объект относится к рубрике</td>
				<td  class="bold">Дизайн-проект интерьера</td>
			</tr>
			<tr>
				<td>Состав объекта</td>
				<td  class="bold">Изображения, иллюстрирующие проект на портале</td>
			</tr>
			<tr>
				<td>Расположение объекта</td>
				<td  class="bold"><?php
					$link = Yii::app()->homeUrl.'/member/profile/interior/id/'.$architecture->id;
					echo CHtml::link($link, $link);
				?></td>
			</tr>
			<tr>
				<td>Дата добавления проекта</td>
				<td  class="bold">(MSK, UTC+4) <?php echo date('d/m/Y в H:i:s', $architecture->create_time);?></td>
			</tr>
			<tr>
				<td>Дата последнего редактирования</td>
				<td  class="bold">(MSK, UTC+4) <?php echo date('d/m/Y в H:i:s', $architecture->update_time);?></td>
			</tr>
			<tr>
				<td>Название объекта</td>
				<td  class="bold"><?php echo $architecture->name;?></td>
			</tr>
		</table>
	</div>
</div>