<?php

class m130710_063630_alter_table_mail_template extends CDbMigration

{
	private $html = '
		<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
		<p style="font-size:24px;font-family:arial;margin:0;">Поздравляем!</p>
		<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
		<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">Вы стали участником акции «<a style="color:#dd3724" href="http://www.myhome.ru/competition/specialists/2013/july">iPad за спасибо!</a>» и, возможно, уже совсем скоро станете обладателем новенького планшета. Вы можете написать несколько отзывов
			в период с 1 июля по 1 сентября 2013 года,  чтобы повысить шансы выиграть главный приз.</p>
		<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
		<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">Если вы станете победителем, 10 сентября 2013 года вам будет отправлено письмо,
			на которое необходимо ответить в течение 48 часов, чтобы подтвердить свой выигрыш, иначе нам придётся выбирать нового победителя. Имя победителя будет опубликовано на странице MyHome не позднее 10 сентября 2013 года.</p>
		<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
		<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">Напоминаем, что участвовать в акции могут только зарегистрированные пользователи MyHome, являющиеся гражданами РФ и достигшие 18 лет. </p>
		<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
		<p style="font-size:14px;font-family:arial;margin:0;line-height:22px;">Подробные условия участия читайте на <a style="color:#dd3724" href="http://www.myhome.ru/competition/specialists/2013/july">странице акции</a>.</p>
		<div style="height:20px"><img src="http://www.myhome.ru/uploads/public/mailer/1370580060separator.jpg" height=20 alt=""></div>
';



	public function up()
	{
		$this->insert('mail_template', array(
			'key'         => 'notifIpad',
			'name'        => 'Уведомление об участии в конкурсе',
			'subject'     => 'Вы участник конкурса.',
			'keywords'    => '',
			'from'        => 'noreply@myhome.ru',
			'author'      => 'MyHome',
			'create_time' => time(),
			'update_time' => time(),
			'data'        => $this->html
		));
	}

	public function down()
	{
		$this->delete('mail_template', '`key` = :k', array(':k' => 'notifIpad'));
	}
}