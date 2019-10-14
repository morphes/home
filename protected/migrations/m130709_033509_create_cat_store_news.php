<?php

class m130709_033509_create_cat_store_news extends CDbMigration
{
	public function up()
	{
		$this->createTable('cat_store_news', array(
			'id'          => 'pk',
			'status'      => 'TINYINT(1) NOT NULL DEFAULT 0',
			'user_id'     => 'integer',
			'image_id'    => 'integer',
			'store_id'    => 'integer',
			'rating'      => 'FLOAT(2, 1) NOT NULL DEFAULT 0.0',
			'title'       => 'VARCHAR(255) NOT NULL DEFAULT ""',
			'content'     => 'TEXT NOT NULL DEFAULT ""',
			'create_time' => 'integer',
			'update_time' => 'integer'
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('index_store_id', 'cat_store_news', 'store_id');
	}

	public function down()
	{
		$this->dropIndex('index_store_id', 'cat_store_news');
		$this->dropTable('cat_store_news');
	}
}