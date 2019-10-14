<?php

class m130926_052340_alter_table_user_service_priority extends CDbMigration
{
	public function up()
	{
		$this->addColumn('user_service_priority', 'in_main','TINYINT');
	}

	public function down()
	{
		$this->dropColumn('user_service_priority', 'in_main');
	}
}