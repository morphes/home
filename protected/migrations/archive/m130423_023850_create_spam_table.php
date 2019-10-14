<?php

class m130423_023850_create_spam_table extends CDbMigration
{
	public function up()
	{
		$this->createTable('spam', array(
			'id' => 'pk',
			'msg_id' => 'INT(11) NOT NULL',
			'status' => 'TINYINT(1) NOT NULL DEFAULT 0',
			'create_time' => 'INT(11) NOT NULL',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

	}

	public function down()
	{
		$this->dropTable('spam');
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}