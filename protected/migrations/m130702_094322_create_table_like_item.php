<?php

class m130702_094322_create_table_like_item extends CDbMigration
{
	public function up()
	{
		$this->createTable('like_item', array(
			'id'             => 'pk',
			'author_id'      => 'integer',
			'model'          => 'varchar(255) default "" NOT NULL',
			'model_id'       => 'integer',
			'create_time'    => 'integer',
			'update_time'    => 'integer',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('index_model_modelid', 'like_item', 'model_id, model');
	}

	public function down()
	{
		$this->dropIndex('index_model_modelid', 'like_item');
		$this->dropTable('like_item');
	}
}