<?php

class m130710_073408_alter_table_user_data extends CDbMigration
{
	// Поля для флага для рассылки по конкурсу. Если установлен в 1 то письмо не отправляем.
	public function up()
	{
		$this->addColumn('user_data', 'notif_flag', 'TINYINT default 0 after average_rating');
	}

	public function down()
	{
		$this->dropColumn('user_data','notif_flag');
	}
}