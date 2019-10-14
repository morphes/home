<?php

class m130701_055823_create_table_stat_user_service extends CDbMigration
{
	public function up()
	{
		$this->createTable('stat_user_service', array(
			'id'       => 'pk',
			'user_id'=> 'integer',
			'city_id' => 'integer',
			'service_id'    => 'integer',
			'view'     => 'integer',
			'type'     => 'TINYINT(1) NOT NULL DEFAULT 0',
			'time'     => 'integer'
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('index_user_id', 'stat_user_service', 'user_id');
		$this->createIndex('index_city_id', 'stat_user_service', 'city_id');
		$this->createIndex('index_service_id', 'stat_user_service', 'service_id');

	}

	public function down()
	{
		$this->dropIndex('index_user_id', 'stat_user_service');
		$this->dropIndex('index_city_id', 'stat_user_service');
		$this->dropIndex('index_service_id', 'stat_user_service');

		$this->dropTable('stat_user_service');
	}
}