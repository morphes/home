<?php

class m130716_035538_alter_template extends CDbMigration
{
	private $html = '
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
				<table width="620" style="background:#ffffff" >
					<tr>
						<td width="20"></td>
						<td width="580" align="center">
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
							<p style="font-size:24px;font-family:arial;margin:0;">Добрый день!</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
							<p style="font-size:14px;font-family:arial;margin:0;line-height:20px;">:referrer_name: хочет оставить отзыв о вас на <a style="color:#dd3724" href="http://myhome.ru">MyHome</a> и приглашает<br>
								к участию в акции <a style="color:#dd3724" href="http://myhome.ru/ipad_za_spasibo">iPad4 за спасибо!</a> Для этого вам необходимо<br>
								совершить 3 простых шага, это займет у вас не более 10 минут:
							</p>
							<div style="height:40px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=40 alt=""></div>
							<div><img src="http://www.myhome.ru/uploads/public/mailer/1371440063steps.png" /></div>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
							<table>
								<tr>
									<td width="200" align="center">
										<p style="font-size:14px;font-family:arial;font-weight:bold;margin:0;line-height:20px;">
											<a href="http://myhome.ru/site/registration#/designer" style="color:#000;">Зарегистрируйтесь</a><br>на MyHome<br>
											<span style="font-size:11px;font-family:arial;color:#808080;line-height:20px;">1 минута</span>
										</p>
									</td>
									<td width="200" align="center">
										<p style="font-size:14px;font-family:arial;font-weight:bold;margin:0;line-height:20px;">
											Заполните профиль<br>  и контактные данные <br>
											<span style="font-size:11px;font-family:arial;color:#808080;line-height:20px;">3 минуты</span>
										</p>
									</td>
									<td width="200" align="center">
										<p style="font-size:14px;font-family:arial;font-weight:bold;margin:0;line-height:20px;">
											Добавьте фотографии	своих работ <br>
											<span style="font-size:11px;font-family:arial;color:#808080;line-height:20px;">6 минут</span>
										</p>
									</td>
								</tr>
							</table>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
						</td>
						<td width="20"></td>
					</tr>
					<tr>
						<td><div style="height:20px"></div></td>
					</tr>
				</table>
			</div>
			<div style="background:#ececec">
				<img src="http://www.myhome.ru/uploads/public/mailer/1371441743arrow.jpg" />
				<table width="620" style="background:#ececec" >
					<tr>
						<td width="20"></td>
						<td width="580" align="center">
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
							<p style="font-size:24px;font-family:arial;margin:0;">Что вы получите</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
							<p style="font-size:14px;font-family:arial;margin:0;line-height:25px;color:#2d2d2d;">
								<span style="color:#808080">•</span> Возможность <a href="http://myhome.ru/ipad_za_spasibo" style="color:#2d2d2d;font-weight:bold;">принять участие в акции и выиграть iPad4</a>!<br>
								<span style="color:#808080">•</span> Бесплатное участие в <a href="http://myhome.ru/tenders/list" class="color:#2d2d2d">тендерах</a> на дизайн интерьера и ремонтные работы.<br>
								<span style="color:#808080">•</span> Постоянный трафик потенциальных клиентов — более 800 000 ежемесячно!<br>
								<span style="color:#808080">•</span> Лучший источник вдохновения — <a href="http://myhome.ru/idea" style="color:#2d2d2d">45 000 идей</a> для интерьера!<br>
								<span style="color:#808080">•</span> Возможность общаться и обмениваться опытом с коллегами по цеху.<br>
								<span style="color:#808080">•</span> Возможность общаться с потенциальными клиентами на <a href="http://myhome.ru/forum" style="color:#2d2d2d">форуме</a>.<br>
							</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>

							<p style="font-size:14px;font-family:arial;margin:0;line-height:20px;">
								<a style="color:#dd3724" href="http://myhome.ru/site/registration#/designer">
									<img src="http://www.myhome.ru/uploads/public/mailer/137394404216072003button.jpg">
								</a>
							</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
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
									<a style="font-family:arial;font-size:11px; color:#2d2d2d;" href="http://myhome.ru/tenders/list">Заказы</a>
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
';



	public function up()
	{
		$this->insert('mail_template', array(
			'key'         => 'inviteUserSpec',
			'name'        => 'Username приглашает вас зарегистрировать на Myhome.ru',
			'subject'     => 'Username приглашает вас зарегистрировать на Myhome.ru',
			'keywords'    => 'referrer_name',
			'from'        => 'noreply@myhome.ru',
			'author'      => 'MyHome',
			'create_time' => time(),
			'update_time' => time(),
			'data'        => $this->html
		));
	}

	public function down()
	{
		$this->delete('mail_template', '`key` = :k', array(':k' => 'inviteUserSpec'));
	}
}