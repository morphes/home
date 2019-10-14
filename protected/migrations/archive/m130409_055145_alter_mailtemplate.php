<?php

class m130409_055145_alter_mailtemplate extends CDbMigration
{
	private $html = '
		<div style="font-weight: bold; font-size: 14px; line-height: 20px; font-family: Arial,tahoma; height: 40px;">На <a style="color: #000000;" href="http://www.myhome.ru/">MyHome.ru</a> оставлена заявка Магазина.</div>
		<div style="font-size: 12px; line-height: 20px; font-family: Arial,tahoma;">
		<div>:data_form:</div>
		<p style="font-size: 12px; line-height: 20px; font-family: Arial,tahoma;"><strong>IP адрес отправителя</strong>: :user_ip: <br /><br /> <strong>UserAgent отправителя:</strong> :user_agent:&nbsp;</p>
		</div>
		<p><strong>Email отправителя:</strong> :user_email:</p>
	';

	public function up()
	{
		$this->insert('mail_template', array(
			'key'         => 'shopAdvertising',
			'name'        => 'Магазинам, заявка',
			'subject'     => 'Магазинам, заявка',
			'keywords'    => 'data_form, user_ip, user_agent, user_email',
			'from'        => 'support@myhome.ru',
			'author'      => 'MyHome',
			'create_time' => time(),
			'update_time' => time(),
			'data'        => $this->html
		));
	}

	public function down()
	{
		$this->delete('mail_template', '`key` = :k', array(':k' => 'shopAdvertising'));
	}
}