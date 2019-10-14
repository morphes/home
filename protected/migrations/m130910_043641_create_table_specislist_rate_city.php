<?php

class m130910_043641_create_table_specislist_rate_city extends CDbMigration
{
	public function up()
	{
		$this->createTable('specialist_rate_city', array(
			'id'	=> 'pk',
			'rate_id'     => 'integer',
			'city_id'     => 'integer',
			'service_id'     => 'integer',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

	}

	public function down()
	{
		$this->dropTable('specialist_rate_city');
	}
}