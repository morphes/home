<?php

class m130618_043523_create_table_cat_folder extends CDbMigration
{
	public function up()
	{
		$this->createTable('cat_folders', array(
			'id'       => 'pk',
			'name'     => 'VARCHAR(255)',
			'user_id' => 'integer',
			'status'     => 'TINYINT(1) NOT NULL DEFAULT 0',
			'count' => 'integer DEFAULT 0',
			'update_time'     => 'integer',
			'create_time'     => 'integer',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('index_user_id', 'cat_folders', 'user_id');
	}

	public function down()
	{
		$this->dropIndex('index_user_id', 'cat_folders');
		$this->dropTable('cat_folders');
	}
}