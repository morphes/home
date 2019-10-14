<?php

class m130705_053723_create_table_review_uploadedfile extends CDbMigration
{
	public function up()
	{
		$this->createTable('review_uploadedfile', array(
			'item_id' => 'integer',
			'file_id'     => 'integer',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('item_id', 'review_uploadedfile', 'item_id');
	}

	public function down()
	{
		$this->dropIndex('item_id', 'review_uploadedfile');
		$this->dropTable('review_uploadedfile');
	}
}