<?php

class m130711_080229_alter_table_mail_template extends CDbMigration
{
	private $htmlUser = '
		<table cellspacing="0" cellpadding="0" border="0" style="background:#f5f5f5;font-family:arial;font-size:13px;line-height:18px !important;color:#2d2d2d">
	<tbody>
	<tr>
		<td><div style="height:20px"></div></td>
	</tr>
	<tr>
		<td width="40"></td>
		<td>
			<table width="620">
				<tbody>
				<tr>
					<td width="20"></td>
					<td width="580">
						<table width="580">
							<tr style="height:90px">
								<td vertical-align="middle" width=50%>
									<a href="http://www.myhome.ru/"><img src="http://www.myhome.ru/uploads/public/mailer/1364794189new_logo.png"/></a>
								</td>
								<td vertical-align="middle" align="right" width=50%>
									<a href="http://vkontakte.ru/myhomeru"><img src="http://www.myhome.ru/uploads/public/mailer/1364794421vk_g.png"/></a>&nbsp;
									<a href="http://facebook.com/myhome.ru"><img src="http://www.myhome.ru/uploads/public/mailer/1364794327fb_g.png"/></a>&nbsp;
									<a href="http://twitter.com/MyHomeRu"><img src="http://www.myhome.ru/uploads/public/mailer/1364794437tw_g.png"/></a>&nbsp;
									<a href="http://www.odnoklassniki.ru/myhome"><img src="http://www.myhome.ru/uploads/public/mailer/1364794377ok_g.png"/></a>&nbsp;
									<a href="http://pinme.ru/u/myhomeru/"><img src="http://www.myhome.ru/uploads/public/mailer/1364794400pm_g.png" style="" /></a>
								</td>
							</tr>
						</table>
					</td>
					<td width="20"></td>
				</tr>
				</tbody>
			</table>
			<div style="background:#ffffff">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td><img src="http://www.myhome.ru/uploads/public/mailer/1373526994mail-head.jpg"></td>
					</tr>
				</table>
				<table width="620" style="background:#ffffff" >
					<tr>
						<td width="20"></td>
						<td width="580">
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
							<p style="font-size:24px;font-family:arial;margin:0;">Прими участие в акции «iPad4 за спасибо»!</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
							<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">Вы уже нашли себе профессионала на MyHome или другом ресурсе*, который помог вам сделать то, к чему вы сами боялись подойти? У нас есть отличное предложение
								в обмен на несколько ваших предложений. Напишите отзыв о работе, проделанной
								для вас специалистом.
							</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=35 alt=""></div>
							<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">Чем больше отзывов вы оставите, тем больше вероятность того, что после 10 сентября 2013 года вы будете заходить на MyHome с новенького iPad 4! Приложите к своему отзыву фотографию, и в случае победы в дополнение к основному призу мы подарим вам фотоаппарат Nikon1 J2!
							</p>
							<div style="height:35px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
							<p style="font-size:24px;font-family:arial;margin:0;text-align:center;">Как получить iPad4 за спасибо?</p>
							<div style="height:30px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=30 alt=""></div>
							<div  style="text-align:center"><img src="http://www.myhome.ru/uploads/public/mailer/1373527095mail-steps-u.jpg" alt=""></div>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
							<table>
								<tr>
									<td width="289" align="center" valign="top">
										<p style="font-size:14px;font-family:arial;font-weight:bold;margin:0;line-height:20px;">
											Напишите один или несколько<br>
											правдивых отзывов о работе<br>
											специалиста
										</p>
									</td>
									<td width="276" align="center"  valign="top">
										<p style="font-size:14px;font-family:arial;font-weight:bold;margin:0;line-height:20px;">
											Ждите письма о том,<br>
											что вы выиграли<br>
											главный приз!
										</p>
									</td>
								</tr>
							</table>
							<div style="height:35px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=35 alt=""></div>
							<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">Отзыв можно оставить с 6 июля по 6 сентября 2013 года включительно на странице специалиста, который помог вам воплотить мечту в жизнь. Отзыв должен отвечать всего трём условиям: быть правдивым, включать исчерпывающую информацию о поставленной вами задаче, её решении и полученном результате, и при этом не содержать слов, которые нельзя напечатать на нашем портале.</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
							<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">Подробные условия участия читайте на <a style="color:#df3825" href="http://www.myhome.ru/ipad_za_spasibo">странице конкурса</a>.</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
							<p style="font-size:11px;font-family:arial;margin:0;line-height:22px;">* Вы можете <a style="color:#2d2d2d" href="http://www.myhome.ru/site/registration">пригласить к регистрации</a> на портале своих клиентов.<br>
								И они смогут принять участие в конкурсе на любом этапе.</p>
						</td>
						<td width="20"></td>
					</tr>
					<tr>
						<td><div style="height:20px"></div></td>
					</tr>
				</table>
			</div>
			<table width="620">
				<tr>
					<td><div style="height:10px"></div></td>
				</tr>
				<tr>
					<td width="20"></td>
					<td width="580">
						<table width="580">
							<tr>
								<td>
									<a style="font-family:arial;font-size:11px; color:#df3825;" href="http://myhome.ru/">MyHome.ru</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="http://myhome.ru/catalog/">Товары</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="http://myhome.ru/ideas/">Идеи</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="http://myhome.ru/specialists/">Специалисты</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="http://myhome.ru/media/">Журнал</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="http://myhome.ru/tenders/list">Заказы</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="mailto:bkv@myhome.ru">Контакты для партнеров</a>
								</td>
							</tr>
						</table>
					</td>
					<td width="20"></td>
				</tr>
				<tr>
					<td><div style="height:10px"></div></td>
				</tr>
			</table>
		</td>
		<td width="40"></td>
	</tr>

	</tbody>
</table>
</table>
		';

	private $htmlSpec = '
	<table cellspacing="0" cellpadding="0" border="0" style="background:#f5f5f5;font-family:arial;font-size:13px;line-height:18px !important;color:#2d2d2d">
	<tbody>
	<tr>
		<td><div style="height:20px"></div></td>
	</tr>
	<tr>
		<td width="40"></td>
		<td>
			<table width="620">
				<tbody>
				<tr>
					<td width="20"></td>
					<td width="580">
						<table width="580">
							<tr style="height:90px">
								<td vertical-align="middle" width=50%>
									<a href="http://www.myhome.ru/"><img src="http://www.myhome.ru/uploads/public/mailer/1364794189new_logo.png"/></a>
								</td>
								<td vertical-align="middle" align="right" width=50%>
									<a href="http://vkontakte.ru/myhomeru"><img src="http://www.myhome.ru/uploads/public/mailer/1364794421vk_g.png"/></a>&nbsp;
									<a href="http://facebook.com/myhome.ru"><img src="http://www.myhome.ru/uploads/public/mailer/1364794327fb_g.png"/></a>&nbsp;
									<a href="http://twitter.com/MyHomeRu"><img src="http://www.myhome.ru/uploads/public/mailer/1364794437tw_g.png"/></a>&nbsp;
									<a href="http://www.odnoklassniki.ru/myhome"><img src="http://www.myhome.ru/uploads/public/mailer/1364794377ok_g.png"/></a>&nbsp;
									<a href="http://pinme.ru/u/myhomeru/"><img src="http://www.myhome.ru/uploads/public/mailer/1364794400pm_g.png" style="" /></a>
								</td>
							</tr>
						</table>
					</td>
					<td width="20"></td>
				</tr>
				</tbody>
			</table>
			<div style="background:#ffffff">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td><img src="http://www.myhome.ru/uploads/public/mailer/1373526994mail-head.jpg"></td>
					</tr>
				</table>
				<table width="620" style="background:#ffffff" >
					<tr>
						<td width="20"></td>
						<td width="580">
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
							<p style="font-size:24px;font-family:arial;margin:0;">Прими участие в акции «iPad4 за спасибо»!</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
							<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">У вас уже есть довольные клиенты, которые смогут порекомендовать вас другим? Попросите ваших заказчиков оставить отзыв о проделанной работе в вашем профиле на портале MyHome. Чем больше отзывов вы соберёте, тем больше вероятность того, что после 10 сентября 2013 года вы будете заходить на MyHome с новенького iPad 4!</p>
							<div style="height:35px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=35 alt=""></div>
							<p style="font-size:24px;font-family:arial;margin:0;text-align:center;">Как получить iPad4 за спасибо?</p>
							<div style="height:45px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=30 alt=""></div>
							<div  style="text-align:center"><img src="http://www.myhome.ru/uploads/public/mailer/1373527025mail-steps.jpg" alt=""></div>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
							<table>
								<tr>
									<td width="170" align="center" valign="top">
										<p style="font-size:14px;font-family:arial;font-weight:bold;margin:0;line-height:20px;">
											Заполните свой
											профиль на портале
											MyHome
										</p>
									</td>
									<td width="210" align="center"  valign="top">
										<p style="font-size:14px;font-family:arial;font-weight:bold;margin:0;line-height:20px;">
											Попросите своих<br>
											заказчиков написать<br>
											правдивые отзывы<br>
											о вашей работе
										</p>
									</td>
									<td width="170" align="center"  valign="top">
										<p style="font-size:14px;font-family:arial;font-weight:bold;margin:0;line-height:20px;">
											Ждите письма о том,
											что вы выиграли
											главный приз!
										</p>
									</td>
								</tr>
							</table>
							<div style="height:35px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=35 alt=""></div>
							<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">Ваши заказчики могут оставить отзыв с 6 июля по 6 сентября 2013 года включительно. Отзыв должен отвечать всего трём условиям: быть правдивым, включать исчерпывающую информацию о поставленной вами задаче, её решении и полученном результате, и при этом не содержать слов, которые нельзя напечатать на нашем портале.</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
							<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">Подробные условия участия читайте на <a style="color:#df3825" href="http://www.myhome.ru/ipad_za_spasibo">странице конкурса</a>.</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
							<p style="font-size:11px;font-family:arial;margin:0;line-height:22px;">* Вы можете <a style="color:#2d2d2d" href="http://www.myhome.ru/site/registration">пригласить к регистрации</a> на портале своих специалистов.<br>
								И они смогут принять участие в конкурсе на любом этапе.</p>
						</td>
						<td width="20"></td>
					</tr>
					<tr>
						<td><div style="height:20px"></div></td>
					</tr>
				</table>
			</div>
			<table width="620">
				<tr>
					<td><div style="height:10px"></div></td>
				</tr>
				<tr>
					<td width="20"></td>
					<td width="580">
						<table width="580">
							<tr>
								<td>
									<a style="font-family:arial;font-size:11px; color:#df3825;" href="http://myhome.ru/">MyHome.ru</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="http://myhome.ru/catalog/">Товары</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="http://myhome.ru/ideas/">Идеи</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="http://myhome.ru/specialists/">Специалисты</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="http://myhome.ru/media/">Журнал</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="http://myhome.ru/tenders/list">Заказы</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="mailto:bkv@myhome.ru">Контакты для партнеров</a>
								</td>
							</tr>
						</table>
					</td>
					<td width="20"></td>
				</tr>
				<tr>
					<td><div style="height:10px"></div></td>
				</tr>
			</table>
		</td>
		<td width="40"></td>
	</tr>

	</tbody>
</table>
</table>
	';



	public function up()
	{
		$this->insert('mail_template', array(
			'key'         => 'userIpadZaSpasibo',
			'name'        => 'Уведомление об участии в конкурсе',
			'subject'     => 'iPad за спасибо - акция Myhome.ru',
			'keywords'    => '',
			'from'        => 'noreply@myhome.ru',
			'author'      => 'MyHome',
			'create_time' => time(),
			'update_time' => time(),
			'data'        => $this->htmlUser
		));

		$this->insert('mail_template', array(
			'key'         => 'specIpadZaSpasibo',
			'name'        => 'Уведомление об участии в конкурсе',
			'subject'     => 'iPad за спасибо - акция Myhome.ru',
			'keywords'    => '',
			'from'        => 'noreply@myhome.ru',
			'author'      => 'MyHome',
			'create_time' => time(),
			'update_time' => time(),
			'data'        => $this->htmlSpec
		));
	}

	public function down()
	{
		$this->delete('mail_template', '`key` = :k', array(':k' => 'userIpadZaSpasibo'));
		$this->delete('mail_template', '`key` = :k', array(':k' => 'specIpadZaSpasibo'));
	}
}