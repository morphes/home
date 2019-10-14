<?php

class m130419_035431_create_stat_store extends CDbMigration
{
	public function up()
	{
		$this->createTable('stat_store', array(
			'id'       => 'pk',
			'store_id' => 'integer',
			'view'     => 'integer',
			'type'     => 'TINYINT(1) NOT NULL DEFAULT 0',
			'time'     => 'integer'
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('index_store_id', 'stat_store', 'store_id');
	}

	public function down()
	{
		$this->dropIndex('index_store_id', 'stat_store');
		$this->dropTable('stat_store');
	}
}