<?php

class m130619_075956_create_table_cat_folder_item extends CDbMigration
{
	public function up()
	{
		$this->createTable('cat_folder_item', array(
			'id'       => 'pk',
			'folder_id' => 'integer',
			'model_id' => 'integer',
			'update_time'     => 'integer',
			'create_time'     => 'integer',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('folder_id', 'cat_folder_item', 'folder_id');
	}

	public function down()
	{
		$this->dropIndex('folder_id', 'cat_folder_item');
		$this->dropTable('cat_folder_item');
	}
}