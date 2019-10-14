<?php

class m130515_043027_alter_comment extends CDbMigration
{
	public function up()
	{
		$this->dropForeignKey('fk_comment_user1','comment');
	}

	public function down()
	{
		echo "m130515_043027_alter_comment does not support migration down.\n";
		return false;
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