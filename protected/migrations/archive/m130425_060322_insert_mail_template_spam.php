<?php

class m130425_060322_insert_mail_template_spam extends CDbMigration
{
	public $html='
	<h3>На MyHome.ru оставлена жалоба на спам.</h3>

	От пользователя :recipientName:
	<br>
	На пользователя :authorName:
	<br>
	 <a href=:request:>Ссылка на заявку</a>
	<br>
	Дата зявки :dateCreate:
	<br>
	';

	public function up()
	{
		$this->insert('mail_template',array(
			'key'=>'spamNotifier',
			'name'=>'Уведомление о спаме',
			'subject'=>'Уведомление о спаме',
			'keywords'=>'recipientName,authorName,request,dateCreate',
			'from'=>'noreply@myhome.ru',
			'author'=>'MyHome',
			'create_time'=>time(),
			'update_time'=>time(),
			'data'=>$this->html));
	}

	public function down()
	{
		$this->delete('mail_template', '`key` = :k', array(':k' => 'spamNotifier'));


	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}