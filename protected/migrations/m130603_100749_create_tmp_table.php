<?php

class m130603_100749_create_tmp_table extends CDbMigration
{
	public function up()
	{
		$this->createTable('tmp_arch_files', array(
			'id'=>'INT(11) NOT NULL',
			'author_id' => 'INT(11) NOT NULL',
			'path' => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'name' => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'ext' => 'VARCHAR(7) NOT NULL DEFAULT ""',
			'original_name' => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'size' => 'INT(11) NOT NULL DEFAULT 0',
			'type' => 'TINYINT(1) NOT NULL DEFAULT 0',
			'desc' => 'VARCHAR(1000) NOT NULL DEFAULT ""',
			'keywords' => 'VARCHAR(1000) NOT NULL DEFAULT ""',
			'update_time' => 'INT(11) NOT NULL',
			'create_time' => 'INT(11) NOT NULL, PRIMARY KEY (`id`)',
			'status' => 'TINYINT(1) NOT NULL DEFAULT 0',
			'item_id' => 'INT(11) NOT NULL',
			'ismain' => 'TINYINT(1) NOT NULL DEFAULT 0'
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');
	}

	public function down()
	{
		$this->dropTable('tmp_arch_files');
	}

}