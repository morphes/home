<?php

class m130424_030517_alter_spam extends CDbMigration
{
	public function up()
	{
		$this->addColumn('spam', 'update_time', 'INT(11) NOT NULL after create_time');
	}

	public function down()
	{

		$this->dropColumn('spam', 'update_time');
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