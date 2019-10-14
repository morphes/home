<?php

class m130603_080002_create_table_stat_project extends CDbMigration
{
	public function up()
	{
		$this->createTable('stat_project', array(
			'id'       => 'pk',
			'author_id'=> 'integer',
			'model_id' => 'integer',
			'model'    => 'VARCHAR(45)',
			'view'     => 'integer',
			'type'     => 'TINYINT(1) NOT NULL DEFAULT 0',
			'time'     => 'integer'
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('index_model_id', 'stat_project', 'model_id');
		$this->createIndex('index_author_id', 'stat_project', 'author_id');
		$this->createIndex('index_model', 'stat_project', 'model');

	}

	public function down()
	{
		$this->dropIndex('index_model_id', 'stat_project');
		$this->dropIndex('author_id', 'stat_project');
		$this->dropIndex('index_model', 'stat_project');
		$this->dropTable('stat_project');
	}
}