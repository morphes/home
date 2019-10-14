<?php

class m130710_034839_create_cat_store_gallery extends CDbMigration
{
	public function up()
	{
		$this->createTable('cat_store_gallery', array(
			'id'          => 'pk',
			'status'      => 'TINYINT(1) NOT NULL DEFAULT 0',
			'user_id'     => 'integer',
			'image_id'    => 'integer',
			'store_id'    => 'integer',
			'position'    => 'integer',
			'name'        => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'description' => 'VARCHAR(2000) NOT NULL DEFAULT ""',
			'create_time' => 'integer',
			'update_time' => 'integer'
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('index_store_id', 'cat_store_gallery', 'store_id');
	}

	public function down()
	{
		$this->dropIndex('index_store_id', 'cat_store_gallery');

		$this->dropTable('cat_store_gallery');
	}

}