<?php

class m130515_063501_alter_table_comment extends CDbMigration
{
	public function up()
	{
		$this->addColumn('comment', 'guest_id', 'VARCHAR(100) NOT NULL after author_id');
		$this->addColumn('comment', 'status', 'TINYINT(1) NOT NULL after message');
		$this->update('comment', array('status'=>1));
	}

	public function down()
	{
		$this->dropColumn('comment','guest_id');
		$this->dropColumn('comment','status');
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