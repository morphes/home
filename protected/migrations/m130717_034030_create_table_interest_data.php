<?php

class m130717_034030_create_table_interest_data extends CDbMigration
{
	public function up()
	{
		$this->createTable('interest_data', array(
			'id' => 'pk',
			'model_id'     => 'integer',
			'model'     => 'TINYINT',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('model_id', 'interest_data', 'model_id');
		$this->createIndex('model', 'interest_data', 'model');
	}

	public function down()
	{
		$this->dropIndex('model_id', 'interest_data');
		$this->dropIndex('model', 'interest_data');
		$this->dropTable('interest_data');
	}
}