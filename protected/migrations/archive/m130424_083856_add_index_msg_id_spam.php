<?php

class m130424_083856_add_index_msg_id_spam extends CDbMigration
{
	public function up()
	{
		$this->createIndex('msgid','spam','msg_id',true);
	}

	public function down()
	{
		$this->dropIndex('msgid', 'spam');

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