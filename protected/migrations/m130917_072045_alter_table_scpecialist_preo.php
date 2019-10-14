<?php

class m130917_072045_alter_table_scpecialist_preo extends CDbMigration
{
	public function up()
	{
		$this->addColumn('user_service_priority', 'rate_id', 'INT(11) NOT NULL DEFAULT 0 after city_id');
		$this->addColumn('user_service_priority', 'packet', 'INT(11) NOT NULL DEFAULT 0 after rate_id');
	}

	public function down()
	{
		$this->dropColumn('user_service_priority', 'rate_id');
		$this->dropColumn('user_service_priority', 'packet');
	}
}