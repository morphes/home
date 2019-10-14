<?php

class m130708_031840_create_subdomain_table extends CDbMigration
{
	public function up()
	{
		$this->createTable('subdomain', array(
			'id'          => 'pk',
			'domain'      => 'varchar(255) default "" NOT NULL',
			'model'       => 'varchar(255) default "" NOT NULL',
			'model_id'    => 'integer',
			'create_time' => 'integer',
			'update_time' => 'integer',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('index_subdomain', 'subdomain', 'domain', true);
	}

	public function down()
	{
		$this->dropIndex('index_subdomain', 'subdomain');

		$this->dropTable('subdomain');
	}
}