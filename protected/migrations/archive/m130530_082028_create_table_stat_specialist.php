<?php

class m130530_082028_create_table_stat_specialist extends CDbMigration
{
	public function up()
	{
		$this->createTable('stat_specialist', array(
			'id'       => 'pk',
			'specialist_id' => 'integer',
			'view'     => 'integer',
			'type'     => 'TINYINT(1) NOT NULL DEFAULT 0',
			'time'     => 'integer'
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('index_specialist_id', 'stat_specialist', 'specialist_id');
	}

	public function down()
	{
		$this->dropIndex('index_specialist_id', 'stat_specialist');
		$this->dropTable('stat_specialist');
	}
}