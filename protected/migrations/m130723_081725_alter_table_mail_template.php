<?php

class m130723_081725_alter_table_mail_template extends CDbMigration
{
	private  $html =
		'
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
						<td width="580">
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
							<p style="font-size:24px;font-family:arial;margin:0;">Уважаемый :spec_name:!</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
							<p style="font-size:14px;font-family:arial;margin:0;line-height:20px;">Спешим сообщить, что мы обновили систему расчёта рейтинга специалистов. В этом письме мы расскажем вам о том, какие именно факторы влияют на ваш рейтинг, и как вы сможете его повысить.
							</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
							<p style="font-size:14px;font-family:arial;line-height:20px;font-weight:bold;margin-bottom:8px;">Зачем вам нужен рейтинг?</p>
							<p style="font-size:14px;font-family:arial;line-height:20px;margin:0;">Чем выше располагается ваш профиль в списке специалистов, тем больше посетителей портала увидят его и, соответственно, смогут сделать правильный выбор в вашу пользу.
							Но высокий рейтинг — это не только новые заказчики! Это создание имиджа высококлассного специалиста — серьёзное вложение в своё профессиональное будущее.</p>
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
							<p style="font-size:14px;font-family:arial;line-height:20px;font-weight:bold;margin-bottom:8px;">Какие факторы положительно влияют на ваш рейтинг:</p>
							<p style="font-size:14px;font-family:arial;margin:0;line-height:25px;color:#2d2d2d;">
								<span style="color:#808080;margin-right:8px;">•</span> Ваша фотография <span style="margin-left:15px;color:#df3825;font-weight:bold;">+1%</span><br>
								<span style="color:#808080;margin-right:8px;">•</span> Информация о себе <span style="margin-left:15px;color:#df3825;font-weight:bold;">+5%</span><br>
								<span style="color:#808080;margin-right:8px;">•</span> Наполненность по каждой услуге <span style="margin-left:15px;color:#df3825;font-weight:bold;">+10%</span><br>
								<span style="color:#808080;margin-right:8px;">•</span> Количество проектов в портфолио <span style="margin-left:15px;color:#df3825;font-weight:bold;">+42%</span><br>
								<span style="color:#808080;margin-right:8px;">•</span> Отзывы заказчиков и оценки посетителей <span style="margin-left:15px;color:#df3825;font-weight:bold;">+42%</span><br>
								<span style="color:#808080;margin-right:8px;">•</span> Ваши работы среди избранных — <span style="color:#df3825;">Приятный бонус</span><br>
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
			<div style="background:#ececec">
				<img src="http://www.myhome.ru/uploads/public/mailer/1371441743arrow.jpg" />
				<table width="620" style="background:#ececec" >
					<tr>
						<td width="20"></td>
						<td width="580" align="center">
							<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=20 alt=""></div>
							<p style="font-size:14px;font-family:arial;margin:0;line-height:20px;">
								<a style="color:#dd3724" href="http://www.myhome.ru/users/:spec_profile:">
									<img src="http://www.myhome.ru/uploads/public/mailer/1374554181200-mail-rating.jpg">
								</a>
							</p>
							<div style="height:15px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=15 alt=""></div>
							<a href="http://www.myhome.ru/pros/rating" style="font-size:11px;color:#2d2d2d;">Подробнее о рейтинге</a>
							<div style="height:10px"><img src="http://www.myhome.ru/uploads/public/mailer/1371439850separator.png" height=10 alt=""></div>
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
			'key'         => 'specialistChangeRules',
			'name'        => 'Изменение рейтинга специалистов',
			'subject'     => 'Обновление системы рейтинга на MyHome.ru',
			'keywords'    => ':spec_name: :spec_profile:',
			'from'        => 'noreply@myhome.ru',
			'author'      => 'MyHome',
			'create_time' => time(),
			'update_time' => time(),
			'data'        => $this->html
		));
	}

	public function down()
	{
		$this->delete('mail_template', '`key` = :k', array(':k' => 'specialistChangeRules'));
	}



}