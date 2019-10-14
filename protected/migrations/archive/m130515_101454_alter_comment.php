<?php

class m130515_101454_alter_comment extends CDbMigration
{
	public function up()
	{
		$this->addColumn('comment','author_ip','BIGINT(11) NOT NULL after guest_id');
		$this->createIndex('ip','comment','author_ip',false);

	}

	public function down()
	{
		$this->dropColumn('comment','author_ip');

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